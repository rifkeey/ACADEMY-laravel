<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\AuditEvent;
use App\Models\BadgeDefinition;
use App\Models\Certificate;
use App\Models\CourseVersion;
use App\Models\Enquiry;
use App\Models\Enrolment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Lab;
use App\Models\Lesson;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Question;
use App\Models\SourceReference;
use App\Models\Specialization;
use App\Models\User;
use App\Services\CheckoutService;
use App\Support\CredentialIds;
use Database\Seeders\CatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** Admin area: every page renders, permissions are enforced per action, and business rules hold. */
class AdminAreaTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = CatalogueSeeder::class;

    private function user(string $role): User
    {
        $u = User::factory()->create();
        $u->forceFill(['role' => $role])->save();

        return $u->fresh();
    }

    private function soc(): Specialization
    {
        return Specialization::where('slug', 'soc-analyst')->firstOrFail();
    }

    #[Test]
    public function every_admin_page_renders_for_an_administrator(): void
    {
        $this->actingAs($this->user('admin'));
        $soc = $this->soc();
        $urls = [
            route('admin.dashboard'), route('admin.users.index'), route('admin.programmes.index'),
            route('admin.programmes.edit', $soc), route('admin.labs.index'), route('admin.labs.index', ['programme' => 'governance-risk-compliance']),
            route('admin.questions.index'), route('admin.questions.create'), route('admin.exams.index'),
            route('admin.badges.index'), route('admin.certificates.index'), route('admin.pricing.index'),
            route('admin.orders.index'), route('admin.enquiries.index'), route('admin.articles.index'),
            route('admin.articles.create'), route('admin.sources.index'), route('admin.settings.edit'), route('admin.audit.index'),
            route('admin.lessons.edit', Lesson::firstOrFail()), route('admin.labs.edit', Lab::firstOrFail()),
            route('admin.questions.edit', Question::firstOrFail()), route('admin.articles.edit', Article::firstOrFail()),
        ];
        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    #[Test]
    public function instructors_get_content_tools_only_and_learners_see_nothing(): void
    {
        $this->actingAs($this->user('instructor'));
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.programmes.index'))->assertOk();
        $this->get(route('admin.questions.index'))->assertOk();
        foreach (['admin.users.index', 'admin.pricing.index', 'admin.orders.index', 'admin.certificates.index', 'admin.settings.edit', 'admin.audit.index', 'admin.exams.index'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        // Publishing needs content.publish, which instructors do not have.
        $this->put(route('admin.programmes.publication', $this->soc()), ['status' => 'draft', 'enrolment_open' => 0, 'content_status' => 'complete'])->assertForbidden();
        $this->assertSame('published', $this->soc()->status);

        $this->actingAs($this->user('learner'));
        $this->get(route('admin.dashboard'))->assertNotFound();
        $this->put(route('admin.users.role', User::first()), ['role' => 'admin'])->assertNotFound();
    }

    #[Test]
    public function role_changes_cannot_target_yourself_or_remove_the_last_admin(): void
    {
        $admin = $this->user('admin');
        $learner = $this->user('learner');
        $this->actingAs($admin);

        $this->put(route('admin.users.role', $admin), ['role' => 'learner'])->assertSessionHas('error');
        $this->assertSame('admin', $admin->fresh()->role);

        $this->put(route('admin.users.role', $learner), ['role' => 'superuser'])->assertSessionHasErrors('role');
        $this->put(route('admin.users.role', $learner), ['role' => 'instructor'])->assertSessionHas('success');
        $this->assertSame('instructor', $learner->fresh()->role);
        $this->assertTrue(AuditEvent::where('action', 'user.role_changed')->where('entity_id', (string) $learner->id)->exists());

        // A second admin may demote the first, but not when that would leave no active admin.
        $other = $this->user('admin');
        $this->put(route('admin.users.status', $other), ['status' => 'suspended', 'reason' => 'Left the company'])->assertSessionHas('success');
        $this->actingAs($other->fresh());
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));   // suspended accounts are signed out
        $this->actingAs($admin);
        $this->put(route('admin.users.status', $other), ['status' => 'active'])->assertSessionHas('success');
        $this->actingAs($other->fresh())->put(route('admin.users.role', $admin), ['role' => 'learner'])->assertSessionHas('success');
        $this->actingAs($admin->fresh())->get(route('admin.dashboard'))->assertNotFound();
        $this->actingAs($other->fresh())->put(route('admin.users.status', $other), ['status' => 'suspended'])->assertSessionHas('error');
    }

    #[Test]
    public function prices_are_stored_exactly_and_old_offers_are_closed(): void
    {
        $this->actingAs($this->user('admin'));
        $soc = $this->soc();
        $this->post(route('admin.pricing.price', $soc), ['amount' => '199999.99'])->assertSessionHas('success');
        $this->post(route('admin.pricing.price', $soc), ['amount' => '12.3.4'])->assertSessionHasErrors('amount');

        $active = Offer::where('specialization_id', $soc->id)->where('active', true)->get();
        $this->assertCount(1, $active);
        $this->assertSame(19_999_999, $active->first()->amount_minor);
        $this->assertSame(1, Offer::where('specialization_id', $soc->id)->where('active', false)->whereNotNull('valid_until')->count());
        $this->get(route('checkout.show', $soc))->assertSee('₦199,999.99');

        $this->post(route('admin.discounts.store'), ['code' => 'launch-10', 'type' => 'percent', 'percent_off' => 10])->assertSessionHas('success');
        $this->assertDatabaseHas('discounts', ['code' => 'LAUNCH-10', 'percent_off' => 10]);
        $this->post(route('admin.discounts.store'), ['code' => 'LAUNCH-10', 'type' => 'percent', 'percent_off' => 5])->assertSessionHasErrors('code');
    }

    #[Test]
    public function structure_of_a_live_version_is_protected_until_a_draft_exists(): void
    {
        $this->actingAs($this->user('admin'));
        $soc = $this->soc();
        $live = CourseVersion::findOrFail($soc->current_version_id);
        $lesson = Lesson::whereHas('module', fn ($q) => $q->where('course_version_id', $live->id))->firstOrFail();
        $payload = fn (array $over = []) => $over + [
            'title' => 'Corrected title', 'objectives' => "One\nTwo", 'body_md' => $lesson->body_md, 'estimated_minutes' => 25,
            'knowledge_checks' => json_encode($lesson->knowledge_checks), 'required' => 1, 'status' => 'published',
        ];

        // Text corrections on the live version are allowed...
        $this->put(route('admin.lessons.update', $lesson), $payload())->assertSessionHas('success');
        $this->assertSame('Corrected title', $lesson->fresh()->title);
        $this->assertSame(['One', 'Two'], $lesson->fresh()->objectives);
        // ...but making it optional is structural.
        $this->put(route('admin.lessons.update', $lesson), $payload(['required' => 0]))->assertSessionHas('error');
        $this->assertTrue($lesson->fresh()->required);
        // Invalid knowledge checks are rejected with a helpful message.
        $this->put(route('admin.lessons.update', $lesson), $payload(['knowledge_checks' => '[{"question":"Q","options":["a"],"answerIndex":3}]']))->assertSessionHas('error');

        // Create a draft, change it structurally, publish it: existing learners stay on the old version.
        $learner = $this->user('learner');
        app(CheckoutService::class)->grantComplimentary($this->user('admin'), $learner->email, $soc, 'Pilot learner');
        $this->post(route('admin.programmes.versions.store', $soc))->assertRedirect();
        $draft = CourseVersion::where('specialization_id', $soc->id)->where('status', 'draft')->firstOrFail();
        $this->post(route('admin.programmes.versions.store', $soc))->assertSessionHas('error');   // only one draft at a time
        $draftLesson = Lesson::whereHas('module', fn ($q) => $q->where('course_version_id', $draft->id))->firstOrFail();
        $this->put(route('admin.lessons.update', $draftLesson), $payload(['required' => 0]))->assertSessionHas('success');
        $this->post(route('admin.versions.publish', $draft), ['changelog' => 'Made the intro lesson optional.'])->assertRedirect();

        $this->assertSame($draft->id, $soc->fresh()->current_version_id);
        $this->assertSame('retired', $live->fresh()->status);
        $this->assertSame($live->id, Enrolment::where('user_id', $learner->id)->value('course_version_id'));
    }

    #[Test]
    public function lab_definitions_are_validated_and_answer_keys_stay_server_side(): void
    {
        $this->actingAs($this->user('admin'));
        $lab = Lab::where('slug', 'kql-suspicious-signins')->firstOrFail();
        $base = [
            'title' => $lab->title, 'summary' => $lab->summary, 'difficulty' => $lab->difficulty, 'estimated_minutes' => $lab->estimated_minutes,
            'pass_percent' => $lab->pass_percent, 'required' => 1, 'status' => 'published',
            'content' => json_encode($lab->content), 'validator' => json_encode($lab->validator),
        ];

        $this->put(route('admin.labs.update', $lab), ['title' => 'Sign-in investigation'] + $base)->assertSessionHas('success');
        $this->assertSame('Sign-in investigation', $lab->fresh()->title);

        // Changing the answer key of a live lab is structural.
        $validator = $lab->validator;
        $validator['tasks']['classify']['correct'] = 'spray';
        $this->put(route('admin.labs.update', $lab), ['validator' => json_encode($validator)] + $base)->assertSessionHas('error');
        $this->assertSame('brute', $lab->fresh()->validator['tasks']['classify']['correct']);

        // Malformed definitions are rejected before they could break the lab runner.
        $content = $lab->content;
        $content['tasks'][0]['id'] = 'Bad Id';
        $this->put(route('admin.labs.update', $lab), ['content' => json_encode($content)] + $base)->assertSessionHas('error');
        $this->put(route('admin.labs.update', $lab), ['content' => '{not json'] + $base)->assertSessionHasErrors('content');

        // The staff edit page shows the key, the learner API never does (covered in HttpPagesTest).
        $this->get(route('admin.labs.edit', $lab))->assertOk()->assertSee('203.0.113.45')->assertSee('unique');
    }

    #[Test]
    public function questions_need_valid_options_and_exams_need_enough_approved_questions(): void
    {
        $this->actingAs($this->user('admin'));
        $bank = $this->soc()->questionBank;
        $q = ['question_bank_id' => $bank->id, 'kind' => 'single', 'category' => 'triage', 'difficulty' => 'foundation', 'prompt' => 'Which alert should be triaged first?', 'review_status' => 'draft'];

        $this->post(route('admin.questions.store'), $q + ['options' => [['text' => 'A', 'correct' => 1], ['text' => 'B', 'correct' => 1]]])->assertSessionHas('error');
        $this->post(route('admin.questions.store'), $q + ['options' => [['text' => 'Only one', 'correct' => 1], ['text' => '', 'correct' => 0]]])->assertSessionHas('error');
        $this->post(route('admin.questions.store'), $q + ['options' => [['text' => 'Critical asset', 'correct' => 1], ['text' => 'Oldest', 'correct' => 0], ['text' => '', 'correct' => 0]]])->assertRedirect();
        $created = Question::where('prompt', $q['prompt'])->firstOrFail();
        $this->assertSame(2, $created->options()->count());

        $exam = Exam::where('course_version_id', $this->soc()->current_version_id)->firstOrFail();
        $settings = ['title' => $exam->title, 'question_count' => 500, 'pass_percent' => 75, 'duration_minutes' => 60, 'max_attempts' => 3, 'cooldown_hours' => 24, 'show_explanations' => 1, 'status' => 'published'];
        $this->put(route('admin.exams.update', $exam), $settings)->assertSessionHasErrors('question_count');
        $this->put(route('admin.exams.update', $exam), ['question_count' => 150] + $settings)->assertSessionHas('error');
        $this->put(route('admin.exams.update', $exam), ['question_count' => 40, 'pass_percent' => 80] + $settings)->assertSessionHas('success');
        $this->assertSame(80, $exam->fresh()->pass_percent);
        $this->assertSame($exam->config_version + 1, $exam->fresh()->config_version);
        $this->put(route('admin.exams.update', $exam), ['question_count' => 40, 'pass_percent' => 49] + $settings)->assertSessionHasErrors('pass_percent');
    }

    #[Test]
    public function certificates_can_be_revoked_and_reinstated_with_a_reason(): void
    {
        $admin = $this->user('admin');
        $learner = $this->user('learner');
        $enrolment = app(CheckoutService::class)->grantComplimentary($admin, $learner->email, $this->soc(), 'Scholarship');
        $this->travelTo(now());
        // Issue a certificate directly (the full learner path is covered in LearnerJourneyTest).
        $cert = Certificate::create([
            'user_id' => $learner->id, 'enrolment_id' => $enrolment->id, 'specialization_id' => $enrolment->specialization_id,
            'course_version_id' => $enrolment->course_version_id, 'exam_attempt_id' => $this->fakeAttempt($learner, $enrolment),
            'certificate_number' => 'AMQ-SOC-2026-TEST01', 'credential_id' => CredentialIds::credentialId(), 'holder_name' => 'Ada Test',
            'programme_title' => 'SOC Analyst', 'course_version' => 1, 'issued_at' => now(),
        ]);

        $this->actingAs($admin);
        $this->post(route('admin.certificates.revoke', $cert), ['reason' => 'no'])->assertSessionHasErrors('reason');
        $this->post(route('admin.certificates.revoke', $cert), ['reason' => 'Issued in error'])->assertSessionHas('success');
        $this->get(route('verify.show', $cert->credential_id))->assertSee('Revoked certificate');
        $this->post(route('admin.certificates.revoke', $cert), ['reason' => 'Issued in error'])->assertSessionHas('error');
        $this->post(route('admin.certificates.reinstate', $cert), ['reason' => 'Error corrected'])->assertSessionHas('success');
        $this->get(route('verify.show', $cert->credential_id))->assertSee('Valid certificate');
        $this->assertSame(['revoked', 'reinstated'], $cert->events()->orderBy('id')->pluck('action')->all());
        $this->get(route('certificates.pdf', $cert))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    #[Test]
    public function complimentary_enrolments_refunds_and_revocations_are_recorded(): void
    {
        $admin = $this->user('admin');
        $learner = $this->user('learner');
        $this->actingAs($admin);
        $soc = $this->soc();

        $this->post(route('admin.enrolments.grant'), ['email' => 'nobody@example.com', 'specialization_id' => $soc->id, 'note' => 'Pilot cohort'])->assertSessionHas('error');
        $this->post(route('admin.enrolments.grant'), ['email' => strtoupper($learner->email), 'specialization_id' => $soc->id, 'note' => 'Pilot cohort'])->assertSessionHas('success');
        $enrolment = Enrolment::where('user_id', $learner->id)->firstOrFail();
        $this->assertSame('complimentary', $enrolment->source);
        $this->post(route('admin.enrolments.revoke', $enrolment), ['reason' => 'Pilot ended'])->assertSessionHas('success');
        $this->assertSame('revoked', $enrolment->fresh()->status);
        $this->actingAs($learner)->get(route('learn.course', 'soc-analyst'))->assertRedirect();

        // Refund: only paid orders, and it removes access.
        $buyer = $this->user('learner');
        $this->actingAs($buyer)->post(route('checkout.store', 'governance-risk-compliance'))->assertRedirect()->assertSessionHasNoErrors();
        $order = Order::where('user_id', $buyer->id)->firstOrFail();
        $this->post(route('checkout.sandbox.pay', $order->reference), ['outcome' => 'success']);
        $this->actingAs($admin)->post(route('admin.orders.refund', $order), ['reason' => 'Refund within policy'])->assertSessionHas('success');
        $this->assertSame('refunded', $order->fresh()->status);
        $this->assertSame('revoked', Enrolment::where('order_id', $order->id)->value('status'));
        $this->post(route('admin.orders.refund', $order), ['reason' => 'Refund within policy'])->assertSessionHas('error');
        $this->get(route('admin.orders.index', ['q' => $order->reference]))->assertOk()->assertSee($order->reference);
    }

    #[Test]
    public function content_settings_and_enquiries_can_be_managed(): void
    {
        $admin = $this->user('admin');
        $this->actingAs($admin);

        $this->post(route('admin.articles.store'), ['slug' => 'triage-basics', 'title' => 'Triage basics', 'summary' => 'How to triage alerts.', 'body_md' => "# Triage\n\nStart with impact.", 'tags' => 'soc, triage', 'author_name' => 'AMANIQ Academy', 'status' => 'published'])->assertRedirect();
        $this->get('/resources/triage-basics')->assertOk()->assertSee('Start with impact.');
        $this->assertSame(['soc', 'triage'], Article::where('slug', 'triage-basics')->value('tags'));

        $this->post(route('admin.sources.store'), ['key' => 'bad-link', 'title' => 'X', 'publisher' => 'Y', 'url' => 'javascript:alert(1)', 'topic' => 'Z', 'source_type' => 'open_resource', 'verification_status' => 'verified'])->assertSessionHasErrors('url');
        $this->post(route('admin.sources.store'), ['key' => 'admin-test-source', 'title' => 'Known Exploited Vulnerabilities Catalog', 'publisher' => 'CISA', 'url' => 'https://www.cisa.gov/known-exploited-vulnerabilities-catalog', 'topic' => 'Vulnerability management', 'source_type' => 'official_standard', 'verification_status' => 'pending_review'])->assertSessionHas('success');
        $this->assertTrue(SourceReference::where('key', 'admin-test-source')->exists());

        $this->put(route('admin.settings.update'), ['contact_email' => 'academy@example.com', 'support_hours' => 'Weekdays', 'verification_name_display' => 'initials'])->assertSessionHas('success');
        $this->get('/contact')->assertSee('academy@example.com');

        $this->post('/contact', ['kind' => 'corporate', 'name' => 'Bola', 'email' => 'bola@example.com', 'message' => 'We would like training for twelve staff.']);
        $enquiry = Enquiry::where('email', 'bola@example.com')->firstOrFail();
        $this->put(route('admin.enquiries.update', $enquiry), ['status' => 'in_progress', 'notes' => 'Called back'])->assertSessionHas('success');
        $this->assertSame('in_progress', $enquiry->fresh()->status);

        $badge = BadgeDefinition::firstOrFail();
        $this->put(route('admin.badges.update', $badge), ['name' => $badge->name, 'description' => $badge->description, 'criteria' => $badge->criteria, 'hue' => 400, 'icon' => 'radar', 'status' => 'active'])->assertSessionHasErrors('hue');

        $this->get(route('admin.audit.index', ['action' => 'settings.']))->assertOk()->assertSee('settings.updated');
    }

    /** A submitted, passed attempt row so a certificate can reference it. */
    private function fakeAttempt(User $learner, Enrolment $enrolment): int
    {
        $exam = Exam::where('course_version_id', $enrolment->course_version_id)->firstOrFail();

        return ExamAttempt::create([
            'public_id' => (string) Str::ulid(), 'user_id' => $learner->id, 'exam_id' => $exam->id, 'enrolment_id' => $enrolment->id,
            'attempt_number' => 1, 'exam_config_version' => 1, 'pass_percent' => 75, 'status' => 'submitted',
            'started_at' => now()->subHour(), 'expires_at' => now(), 'submitted_at' => now(), 'correct_count' => 30, 'total_count' => 40, 'passed' => true,
        ])->id;
    }
}
