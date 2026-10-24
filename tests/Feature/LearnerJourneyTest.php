<?php

namespace Tests\Feature;

use App\Assessment\Grading;
use App\Exceptions\RuleViolation;
use App\Models\BadgeAward;
use App\Models\Certificate;
use App\Models\CertificateEvent;
use App\Models\Enrolment;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptQuestion;
use App\Models\Lab;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Specialization;
use App\Models\User;
use App\Payments\PaymentProviderFactory;
use App\Payments\SandboxProvider;
use App\Services\CertificatePdfService;
use App\Services\CheckoutService;
use App\Services\CredentialService;
use App\Services\ExamService;
use App\Services\LabService;
use App\Services\LearningService;
use chillerlan\QRCode\QRCode;
use Database\Seeders\CatalogueSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\LabSolutions;
use Tests\TestCase;

/**
 * The whole learner journey on real MySQL: checkout → webhook → lessons → labs → exam
 * (29/40 fails, 30/40 passes) → badge and certificate → PDF/QR → verification and revocation.
 */
class LearnerJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = CatalogueSeeder::class;

    private const SLUG = 'soc-analyst';

    private function learner(string $name = 'Test Learner', string $role = 'learner'): User
    {
        $u = User::factory()->create(['name' => $name]);
        $u->forceFill(['role' => $role])->save();

        return $u;
    }

    private function soc(): Specialization
    {
        return Specialization::where('slug', self::SLUG)->firstOrFail();
    }

    /** Checkout through the sandbox and return a signed webhook body + headers for it. */
    private function sandboxCheckout(User $user): array
    {
        app(CheckoutService::class)->createCheckout($user, $this->soc(), null);
        $order = Order::where('user_id', $user->id)->latest('id')->firstOrFail();
        app(PaymentProviderFactory::class)->get('sandbox')->setOutcome($order->reference, 'success');
        $body = json_encode(['id' => "evt_{$order->reference}", 'event' => 'payment.success', 'reference' => $order->reference]);

        return [$order, $body, ['x-sandbox-signature' => SandboxProvider::sign($body)]];
    }

    private function enrol(User $user): void
    {
        [, $body, $headers] = $this->sandboxCheckout($user);
        app(CheckoutService::class)->handleWebhook('sandbox', $body, $headers);
    }

    private function completeLessonsAndLabs(User $user): void
    {
        $course = app(LearningService::class)->learnerCourse($user, self::SLUG);
        foreach ($course['orderedLessons'] as $l) {
            app(LearningService::class)->markLessonComplete($user, Lesson::find($l->id));
        }
        foreach ($course['labs'] as $l) {
            $r = app(LabService::class)->submit($user, Lab::find($l->id), LabSolutions::ALL[$l->slug], 0);
            $this->assertTrue($r['passed'], $l->slug);
        }
    }

    /** Builds responses with exactly $correctTarget right answers, using the server snapshot. */
    private function answers(ExamAttempt $attempt, int $correctTarget): array
    {
        $responses = [];
        foreach (ExamAttemptQuestion::where('exam_attempt_id', $attempt->id)->orderBy('position')->get() as $i => $row) {
            $options = $row->snapshot['options'];
            $right = array_column(array_filter($options, fn ($o) => $o['isCorrect']), 'id');
            $wrong = array_values(array_filter($options, fn ($o) => ! $o['isCorrect']))[0]['id'];
            $responses[(string) $row->position] = $i < $correctTarget ? $right : [$wrong];
        }

        return $responses;
    }

    #[Test]
    public function closed_programmes_cannot_be_bought(): void
    {
        $this->expectException(RuleViolation::class);
        app(CheckoutService::class)->createCheckout($this->learner(), Specialization::where('slug', 'cloud-security')->first(), null);
    }

    #[Test]
    public function a_redirect_alone_grants_nothing_and_duplicate_webhooks_enrol_once(): void
    {
        $user = $this->learner();
        [$order, $body, $headers] = $this->sandboxCheckout($user);
        $this->assertNull(app(LearningService::class)->activeEnrolment($user, self::SLUG));

        $checkout = app(CheckoutService::class);
        $this->assertSame(401, $checkout->handleWebhook('sandbox', $body, ['x-sandbox-signature' => str_repeat('0', 64)])['status']);
        $this->assertNull(app(LearningService::class)->activeEnrolment($user, self::SLUG));

        $this->assertSame('paid', $checkout->handleWebhook('sandbox', $body, $headers)['result']);
        $this->assertSame('duplicate', $checkout->handleWebhook('sandbox', $body, $headers)['result']);
        $this->assertSame(1, Enrolment::where('user_id', $user->id)->count());
        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
    }

    #[Test]
    public function a_provider_amount_that_differs_from_the_server_price_is_rejected(): void
    {
        $user = $this->learner();
        app(CheckoutService::class)->createCheckout($user, $this->soc(), null);
        $order = Order::where('user_id', $user->id)->firstOrFail();
        app(PaymentProviderFactory::class)->get('sandbox')->setOutcome($order->reference, 'success', amountOverride: 100);   // tampered amount
        $this->assertSame('pending', app(CheckoutService::class)->confirm($order->reference));
        $this->assertNull(app(LearningService::class)->activeEnrolment($user, self::SLUG));
    }

    #[Test]
    public function full_journey_from_enrolment_to_verified_certificate(): void
    {
        $user = $this->learner('Amina Ọlá Bello');
        $this->enrol($user);
        $learning = app(LearningService::class);
        $exams = app(ExamService::class);

        // Exam locked until requirements are met.
        $this->assertFalse($exams->status($user, self::SLUG)['canStart']);
        try {
            $exams->start($user, self::SLUG);
            $this->fail('Exam should be locked');
        } catch (RuleViolation) {
        }

        // Lesson completion persists and is idempotent.
        $first = Lesson::find($learning->learnerCourse($user, self::SLUG)['orderedLessons'][0]->id);
        $learning->markLessonComplete($user, $first);
        $learning->markLessonComplete($user, $first);
        $this->assertSame(1, $learning->learnerCourse($user, self::SLUG)['requirements']['lessonsDone']);

        // A wrong lab submission is recorded but does not complete the lab.
        $lab = Lab::where('slug', 'authentication-log-investigation')->first();
        $this->assertFalse(app(LabService::class)->submit($user, $lab, ['spray-ip' => '10.0.0.1'], 2)['passed']);

        // No certificate while requirements are incomplete.
        $enrolment = Enrolment::where('user_id', $user->id)->first();
        $this->assertFalse(app(CredentialService::class)->issueIfEligible($user, $enrolment)['issued']);

        $this->completeLessonsAndLabs($user);
        $this->assertTrue($exams->status($user, self::SLUG)['canStart']);

        // Attempt 1: 29/40 (72.5%) fails. Answers are not sent to the browser.
        $a1 = $exams->start($user, self::SLUG);
        $taking = $exams->forTaking($user, $a1);
        $this->assertCount(40, $taking['questions']);
        $this->assertStringNotContainsString('isCorrect', json_encode($taking['questions']));
        $this->assertStringNotContainsString('explanation', json_encode($taking['questions']));
        $r1 = $exams->submit($user, $a1, $this->answers($a1, 29));
        $this->assertFalse($r1['attempt']->passed);
        $this->assertSame(29, $r1['attempt']->correct_count);

        // Submitting again changes nothing.
        $dup = $exams->submit($user, $a1, $this->answers($a1, 40));
        $this->assertTrue($dup['alreadySubmitted']);
        $this->assertSame(29, $dup['attempt']->correct_count);

        // Cooldown applies; simulate it elapsing.
        $this->assertFalse($exams->status($user, self::SLUG)['canStart']);
        ExamAttempt::whereKey($a1->id)->update(['started_at' => now()->subDays(2)]);

        // Attempt 2: 30/40 (75%) passes and credentials are issued automatically, once.
        $a2 = $exams->start($user, self::SLUG);
        $this->assertTrue($exams->submit($user, $a2, $this->answers($a2, 30))['attempt']->passed);
        $this->assertSame(1, Certificate::where('user_id', $user->id)->count());
        $this->assertSame(1, BadgeAward::where('user_id', $user->id)->count());
        $this->assertFalse(app(CredentialService::class)->issueIfEligible($user, $enrolment)['issued']);
        $this->assertSame(1, Certificate::where('user_id', $user->id)->count());

        $cert = Certificate::where('user_id', $user->id)->first();
        $this->assertMatchesRegularExpression('/^AMQ-SOC-\d{4}-[0-9A-HJKMNP-TV-Z]{8}$/', $cert->certificate_number);
        $this->assertSame('Amina Ọlá Bello', $cert->holder_name);

        // The PDF renders, re-rendering creates nothing, and the QR decodes to the production verify URL.
        $pdf = app(CertificatePdfService::class)->render($cert);
        $this->assertStringStartsWith('%PDF-', $pdf);
        app(CertificatePdfService::class)->render($cert);
        $this->assertSame(1, Certificate::count());
        $url = CredentialService::verificationUrl($cert->credential_id);
        $this->assertSame("https://academy.amaniqgroup.com/verify/{$cert->credential_id}", $url);
        $png = base64_decode(explode(',', CertificatePdfService::qrDataUri($url), 2)[1]);
        $this->assertSame($url, (string) (new QRCode)->readFromBlob($png));

        // Verification: valid → revoked → still identifiable; unknown → not found; no email exposed.
        $credentials = app(CredentialService::class);
        $v = $credentials->verify($cert->credential_id);
        $this->assertSame('valid', $v['status']);
        $this->assertStringNotContainsString($user->email, json_encode($v));
        $credentials->revoke($this->learner('Admin', 'admin'), $cert, 'Academic misconduct (test)');
        $this->assertSame('revoked', $credentials->verify($cert->credential_id)['status']);
        $this->assertEqualsCanonicalizing(['issued', 'revoked'], CertificateEvent::where('certificate_id', $cert->id)->pluck('action')->all());
        $this->assertSame('not_found', $credentials->verify('0000000000000000000000000Z')['status']);
        $this->assertSame('not_found', $credentials->verify('not-a-real-id')['status']);
    }

    #[Test]
    public function a_learner_cannot_reach_another_learners_course_labs_or_attempts(): void
    {
        $owner = $this->learner();
        $this->enrol($owner);
        $intruder = $this->learner();
        $lesson = Lesson::find(app(LearningService::class)->learnerCourse($owner, self::SLUG)['orderedLessons'][0]->id);

        $this->assertThrows(fn () => app(LearningService::class)->markLessonComplete($intruder, $lesson), AuthorizationException::class);
        $this->assertThrows(fn () => app(LabService::class)->submit($intruder, Lab::first(), [], 0), AuthorizationException::class);
        $this->assertNull(app(LearningService::class)->learnerCourse($intruder, self::SLUG));

        // Make the owner eligible for the exam, start an attempt, and try to read it as the intruder.
        $this->completeLessonsAndLabs($owner);
        $attempt = app(ExamService::class)->start($owner, self::SLUG);
        $this->assertThrows(fn () => app(ExamService::class)->forTaking($intruder, $attempt), HttpException::class);
        $this->assertThrows(fn () => app(ExamService::class)->submit($intruder, $attempt, []), HttpException::class);
    }

    #[Test]
    public function the_database_enforces_one_open_attempt_and_integrity_checks(): void
    {
        $user = $this->learner();
        $this->enrol($user);
        $this->completeLessonsAndLabs($user);
        $attempt = app(ExamService::class)->start($user, self::SLUG);

        // A second in-progress attempt for the same learner and exam violates the generated-column unique index.
        $this->assertThrows(fn () => ExamAttempt::create([
            'public_id' => (string) str()->ulid(), 'user_id' => $user->id, 'exam_id' => $attempt->exam_id, 'enrolment_id' => $attempt->enrolment_id,
            'attempt_number' => 2, 'exam_config_version' => 1, 'pass_percent' => 75, 'expires_at' => now()->addHour(), 'total_count' => 40,
        ]), UniqueConstraintViolationException::class);

        // CHECK constraints: no invented roles, no inconsistent order totals.
        $this->assertThrows(fn () => User::whereKey($user->id)->update(['role' => 'superuser']), QueryException::class);
        $this->assertThrows(fn () => Order::query()->update(['total_minor' => 1]), QueryException::class);
    }

    #[Test]
    public function pass_mark_arithmetic(): void
    {
        $this->assertFalse(Grading::isPass(37, 50, 75));   // 74%
        $this->assertTrue(Grading::isPass(30, 40, 75));    // 75%
    }
}
