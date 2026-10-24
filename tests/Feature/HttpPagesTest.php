<?php

namespace Tests\Feature;

use App\Http\Responses\PasswordResetLinkResponse;
use App\Models\Article;
use App\Models\Enrolment;
use App\Models\ExamAttempt;
use App\Models\Lab;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Specialization;
use App\Models\User;
use App\Payments\PaymentProviderFactory;
use App\Payments\SandboxProvider;
use App\Services\CheckoutService;
use Database\Seeders\CatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\LabSolutions;
use Tests\TestCase;

/**
 * Renders every page through the full HTTP stack (routing, middleware, Blade) and checks the
 * security behaviour that only shows up at that level: headers, 404s for other people's data,
 * answer keys never reaching the browser, and the webhook endpoint.
 */
class HttpPagesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = CatalogueSeeder::class;

    private function user(string $role = 'learner'): User
    {
        $u = User::factory()->create();
        $u->forceFill(['role' => $role])->save();

        return $u->fresh();   // as loaded by the auth guard on a real request
    }

    /** Enrols through the real HTTP checkout: order → sandbox page → simulated provider webhook. */
    private function enrolViaHttp(User $user, string $slug = 'soc-analyst'): Order
    {
        $this->actingAs($user)->post(route('checkout.store', $slug))->assertRedirect();
        $order = Order::where('user_id', $user->id)->latest('id')->firstOrFail();
        $this->get(route('checkout.sandbox', $order->reference))->assertOk()->assertSee($order->reference);
        $this->post(route('checkout.sandbox.pay', $order->reference), ['outcome' => 'success'])
            ->assertRedirect(route('checkout.complete', ['reference' => $order->reference]));
        $this->get(route('checkout.complete', ['reference' => $order->reference]))->assertOk()->assertSee('Payment confirmed');

        return $order;
    }

    #[Test]
    public function public_pages_render_with_security_headers(): void
    {
        $urls = ['/', '/programmes', '/about', '/corporate', '/contact', '/resources', '/verify', '/login', '/register', '/forgot-password', '/sitemap.xml', '/robots.txt'];
        foreach (['privacy', 'terms', 'acceptable-use', 'refunds'] as $doc) {
            $urls[] = "/legal/{$doc}";
        }
        foreach (Specialization::published()->pluck('slug') as $slug) {
            $urls[] = "/programmes/{$slug}";
        }
        foreach (Article::where('status', 'published')->pluck('slug') as $slug) {
            $urls[] = "/resources/{$slug}";
        }

        foreach ($urls as $url) {
            $res = $this->get($url);
            $res->assertOk();
            $this->assertStringContainsString("frame-ancestors 'none'", (string) $res->headers->get('Content-Security-Policy'), $url);
            $this->assertSame('nosniff', $res->headers->get('X-Content-Type-Options'), $url);
        }
        $this->get('/programmes/does-not-exist')->assertNotFound();
        $this->get('/legal/unknown')->assertNotFound();
    }

    #[Test]
    public function signed_in_pages_require_login_and_a_verified_email(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/learn/soc-analyst')->assertRedirect('/login');

        $unverified = User::factory()->unverified()->create();
        $this->actingAs($unverified)->get('/dashboard')->assertRedirect(route('verification.notice'));
    }

    #[Test]
    public function learner_can_enrol_and_open_every_page_of_the_programme(): void
    {
        $learner = $this->user();
        $this->actingAs($learner)->get('/dashboard')->assertOk();
        $this->get(route('dashboard.payments'))->assertOk();
        $this->get(route('account'))->assertOk();
        $this->get(route('checkout.show', 'soc-analyst'))->assertOk()->assertSee('₦180,000');

        // Not enrolled yet: course content is refused.
        $this->get(route('learn.course', 'soc-analyst'))->assertRedirect(route('programmes.show', 'soc-analyst'));

        $this->enrolViaHttp($learner);
        $this->get(route('learn.course', 'soc-analyst'))->assertOk();
        $this->get(route('checkout.show', 'soc-analyst'))->assertRedirect(route('learn.course', 'soc-analyst'));

        $enrolment = Enrolment::where('user_id', $learner->id)->firstOrFail();
        foreach (Lesson::whereHas('module', fn ($q) => $q->where('course_version_id', $enrolment->course_version_id))->get() as $lesson) {
            $this->get(route('learn.lesson', ['soc-analyst', $lesson->id]))->assertOk();
        }
        foreach (Lab::where('course_version_id', $enrolment->course_version_id)->get() as $lab) {
            $page = $this->get(route('learn.lab', ['soc-analyst', $lab->slug]))->assertOk();
            // The validator (answer keys) must never be in the page.
            foreach (['"accept"', '"correct":', 'minCorrectRatio', 'expectedRows'] as $secret) {
                $page->assertDontSee($secret, false);
            }
        }
        $this->get(route('exam.show', 'soc-analyst'))->assertOk()->assertSee('Complete all required lessons');
    }

    #[Test]
    public function lab_endpoints_run_kql_on_the_server_and_grade_without_leaking_keys(): void
    {
        $learner = $this->user();
        $this->enrolViaHttp($learner);
        $lab = Lab::where('slug', 'kql-suspicious-signins')->firstOrFail();

        $this->postJson(route('learn.lab.query', ['soc-analyst', $lab->slug]), ['query' => 'SigninLogs | take 3'])
            ->assertOk()->assertJsonPath('ok', true)->assertJsonCount(3, 'rows');
        $this->postJson(route('learn.lab.query', ['soc-analyst', $lab->slug]), ['query' => 'SigninLogs | wherever'])
            ->assertStatus(422)->assertJsonPath('ok', false);

        $wrong = $this->postJson(route('learn.lab.submit', ['soc-analyst', $lab->slug]), ['answers' => []])->assertOk();
        $this->assertFalse($wrong->json('passed'));
        $this->assertStringNotContainsString('validator', $wrong->getContent());

        $right = $this->postJson(route('learn.lab.submit', ['soc-analyst', $lab->slug]), ['answers' => LabSolutions::ALL[$lab->slug]])->assertOk();
        $this->assertTrue($right->json('passed'));
        foreach ($right->json('results') as $r) {
            $this->assertEqualsCanonicalizing(['taskId', 'correct', 'feedback', 'detail', 'points', 'pointsAwarded'], array_keys($r));
        }

        // Another learner who is not enrolled cannot use the endpoints.
        $this->actingAs($this->user())->postJson(route('learn.lab.query', ['soc-analyst', $lab->slug]), ['query' => 'SigninLogs'])->assertForbidden();
    }

    #[Test]
    public function exam_pages_never_expose_answers_and_attempts_are_private(): void
    {
        $learner = $this->user();
        $this->enrolViaHttp($learner);
        $enrolment = Enrolment::where('user_id', $learner->id)->firstOrFail();
        foreach (Lesson::whereHas('module', fn ($q) => $q->where('course_version_id', $enrolment->course_version_id))->get() as $l) {
            $this->post(route('learn.lesson.complete', ['soc-analyst', $l->id]))->assertRedirect();
        }
        foreach (Lab::where('course_version_id', $enrolment->course_version_id)->get() as $lab) {
            $this->postJson(route('learn.lab.submit', ['soc-analyst', $lab->slug]), ['answers' => LabSolutions::ALL[$lab->slug]])->assertOk();
        }

        $this->get(route('exam.show', 'soc-analyst'))->assertOk()->assertSee('Start attempt 1');
        $this->post(route('exam.start', 'soc-analyst'))->assertRedirect();
        $attempt = ExamAttempt::where('user_id', $learner->id)->firstOrFail();

        $take = $this->get(route('exam.take', ['soc-analyst', $attempt]))->assertOk();
        $take->assertDontSee('isCorrect', false)->assertDontSee('is_correct', false)->assertDontSee('explanation', false);
        $this->assertSame(40, substr_count($take->getContent(), '<fieldset'));

        // Other learners get 404 for this attempt, on every exam route.
        $other = $this->user();
        $this->enrolViaHttp($other);
        $this->get(route('exam.take', ['soc-analyst', $attempt]))->assertNotFound();
        $this->post(route('exam.submit', ['soc-analyst', $attempt]), ['responses' => []])->assertNotFound();
        $this->get(route('exam.result', ['soc-analyst', $attempt]))->assertNotFound();

        // The owner submits an empty attempt: it is graded on the server and the result renders.
        $this->actingAs($learner)->post(route('exam.submit', ['soc-analyst', $attempt]), ['responses' => []])
            ->assertRedirect(route('exam.result', ['soc-analyst', $attempt]));
        $this->get(route('exam.result', ['soc-analyst', $attempt]))->assertOk()->assertSee('Not passed');
        $this->get(route('exam.take', ['soc-analyst', $attempt]))->assertRedirect(route('exam.result', ['soc-analyst', $attempt]));
    }

    #[Test]
    public function webhook_endpoint_checks_signatures_and_ignores_duplicates(): void
    {
        $learner = $this->user();
        $this->actingAs($learner)->post(route('checkout.store', 'soc-analyst'));
        $order = Order::where('user_id', $learner->id)->firstOrFail();
        app(PaymentProviderFactory::class)->get('sandbox')->setOutcome($order->reference, 'success');
        $body = json_encode(['id' => "evt_{$order->reference}", 'event' => 'payment.success', 'reference' => $order->reference]);

        $send = fn (string $sig) => $this->call('POST', '/webhooks/payments/sandbox', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SANDBOX_SIGNATURE' => $sig], $body);

        $send('forged')->assertStatus(401);
        $this->assertSame('pending', $order->fresh()->status);

        $send(SandboxProvider::sign($body))->assertOk()->assertJsonPath('result', 'paid');
        $send(SandboxProvider::sign($body))->assertOk()->assertJsonPath('result', 'duplicate');
        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
        $this->assertSame(1, Enrolment::where('user_id', $learner->id)->count());
    }

    #[Test]
    public function verification_page_handles_unknown_ids(): void
    {
        $this->get('/verify/AMQ-NOTREAL')->assertOk()->assertSee('Certificate not found');
        $this->post('/verify', ['id' => ' amq-<script> '])->assertRedirect('/verify/AMQSCRIPT');
        $this->post('/verify', ['id' => ''])->assertRedirect('/verify/UNKNOWN');
    }

    #[Test]
    public function enquiry_form_validates_and_stores(): void
    {
        $this->post('/contact', [])->assertSessionHasErrors(['kind', 'name', 'email', 'message']);
        $this->post('/contact', ['name' => 'Ngozi', 'email' => 'ngozi@example.com', 'kind' => 'general', 'message' => 'Please tell me about the SOC programme start dates.'])
            ->assertRedirect();
        $this->assertDatabaseHas('enquiries', ['email' => 'ngozi@example.com']);
    }

    #[Test]
    public function completed_modules_render_and_release_the_debrief_only_after_a_pass(): void
    {
        $learner = $this->user();
        $admin = $this->user('admin');
        foreach (['network-security', 'cloud-security'] as $slug) {
            app(CheckoutService::class)->grantComplimentary($admin, $learner->email, Specialization::where('slug', $slug)->firstOrFail(), 'Pilot review');
        }
        $this->actingAs($learner);

        // Every module marked "complete" in the authored content: lessons render, labs keep their keys secret.
        foreach (['network-security', 'cloud-security'] as $slug) {
            $def = json_decode(file_get_contents(database_path("content/programmes/{$slug}/programme.json")), true);
            $complete = collect($def['modules'])->where('contentStatus', 'complete')->pluck('slug');
            $this->assertGreaterThanOrEqual(2, $complete->count(), $slug);
            $enrolment = Enrolment::where('user_id', $learner->id)->whereHas('specialization', fn ($q) => $q->where('slug', $slug))->firstOrFail();
            foreach ($complete as $module) {
                $lessons = Lesson::whereHas('module', fn ($q) => $q->where('course_version_id', $enrolment->course_version_id)->where('slug', $module))->get();
                $this->assertGreaterThanOrEqual(4, $lessons->count(), $module);
                foreach ($lessons as $lesson) {
                    $page = $this->get(route('learn.lesson', [$slug, $lesson->id]))->assertOk();
                    // Every teaching block was rendered (ARNs such as arn:aws:s3::: legitimately contain colons).
                    $this->assertDoesNotMatchRegularExpression('/:::(note|tip|warning|scenario|takeaway|reveal)/', $page->getContent());
                    $this->assertStringStartsWith('<!doctype html>', ltrim($page->getContent()));   // no byte-order mark before the doctype
                }
                foreach (collect($def['labs'])->where('moduleSlug', $module)->pluck('slug') as $labSlug) {
                    $page = $this->get(route('learn.lab', [$slug, $labSlug]))->assertOk();
                    foreach (['Worked solution', '"requests"', '"flows"', 'mustMention', '"cells"'] as $secret) {
                        $page->assertDontSee($secret, false);
                    }
                    $fail = $this->postJson(route('learn.lab.submit', [$slug, $labSlug]), ['answers' => []])->assertOk();
                    $this->assertNull($fail->json('debriefHtml'), $labSlug);
                    $pass = $this->postJson(route('learn.lab.submit', [$slug, $labSlug]), ['answers' => LabSolutions::ALL[$labSlug]])->assertOk();
                    $this->assertTrue($pass->json('passed'), $labSlug);
                    $this->assertStringContainsString('Worked solution', $pass->json('debriefHtml'), $labSlug);
                }
            }
        }

        // The portfolio shows this learner's deliverables, and nobody else's.
        $this->get(route('portfolio'))->assertOk()->assertSee('Firewall change request')->assertSee('Access-review finding')->assertSee('retire rules 2, 4 and 8');
        $this->actingAs($this->user())->get(route('portfolio'))->assertOk()->assertDontSee('retire rules 2, 4 and 8')->assertSee('Your portfolio is empty');
    }

    #[Test]
    public function password_reset_requests_are_rate_limited_per_ip(): void
    {
        // Known and unknown emails get the same answer, so the form does not reveal who has an account.
        $known = User::factory()->create();
        $this->post('/forgot-password', ['email' => $known->email])->assertSessionHasNoErrors()->assertSessionHas('status', PasswordResetLinkResponse::MESSAGE);
        for ($i = 0; $i < 4; $i++) {
            $this->post('/forgot-password', ['email' => "someone{$i}@example.com"])->assertSessionHasNoErrors()->assertSessionHas('status', PasswordResetLinkResponse::MESSAGE);
        }
        $this->post('/forgot-password', ['email' => 'another@example.com'])->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many attempts', session('errors')->first('email'));
    }

    #[Test]
    public function registration_never_accepts_a_role(): void
    {
        $this->post('/register', ['name' => 'Eve', 'email' => 'eve@example.com', 'password' => 'a-long-unique-passphrase-91', 'password_confirmation' => 'a-long-unique-passphrase-91', 'terms' => '1', 'role' => 'admin'])
            ->assertRedirect();
        $this->assertSame('learner', User::where('email', 'eve@example.com')->value('role'));
    }

    #[Test]
    public function the_admin_area_is_hidden_from_learners(): void
    {
        $this->actingAs($this->user())->get('/admin')->assertNotFound();
        $this->get('/admin/users')->assertNotFound();
    }
}
