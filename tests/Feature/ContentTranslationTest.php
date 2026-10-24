<?php

namespace Tests\Feature;

use App\Exceptions\RuleViolation;
use App\Models\Article;
use App\Models\AuditEvent;
use App\Models\ContentTranslation;
use App\Models\CourseModule;
use App\Models\Enrolment;
use App\Models\Exam;
use App\Models\ExamAttemptQuestion;
use App\Models\Lab;
use App\Models\LegalPage;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Specialization;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\ExamService;
use App\Services\LabService;
use App\Services\LearningService;
use App\Services\TranslationService;
use App\Services\VersionService;
use App\Translation\Segments;
use App\Translation\TranslationTypes;
use Database\Seeders\CatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\LabSolutions;
use Tests\TestCase;

/**
 * Course content in French: drafts loaded from files, the translate → review → approve
 * workflow with a second reviewer, learners seeing French only when it is approved and current,
 * out-of-date detection when the English changes, labs marked identically in both languages,
 * and exam attempts keeping the language they started in.
 */
class ContentTranslationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = CatalogueSeeder::class;

    private const LAB = 'phishing-compromise-timeline';

    private function staff(string $role = 'instructor'): User
    {
        $u = User::factory()->create();
        $u->forceFill(['role' => $role])->save();

        return $u->fresh();
    }

    private function learner(string $locale = 'fr'): User
    {
        $u = User::factory()->create();
        $u->forceFill(['locale' => $locale])->save();
        $soc = $this->soc();
        Enrolment::create(['user_id' => $u->id, 'specialization_id' => $soc->id, 'course_version_id' => $soc->current_version_id, 'source' => 'complimentary', 'status' => 'active', 'enrolled_at' => now()]);

        return $u->fresh();
    }

    private function soc(): Specialization
    {
        return Specialization::where('slug', 'soc-analyst')->firstOrFail();
    }

    private function lesson(): Lesson
    {
        $module = CourseModule::where('course_version_id', $this->soc()->current_version_id)->where('slug', 'soc-fundamentals')->firstOrFail();

        return Lesson::where('course_module_id', $module->id)->where('slug', 'soc-role-and-workflows')->firstOrFail();
    }

    private function lab(): Lab
    {
        return Lab::where('course_version_id', $this->soc()->current_version_id)->where('slug', self::LAB)->firstOrFail();
    }

    private function row(string $type, int $id): ContentTranslation
    {
        return ContentTranslation::where(['item_type' => $type, 'item_id' => $id, 'locale' => 'fr'])->firstOrFail();
    }

    /** Translator A sends the seeded draft for review; reviewer B approves it. */
    private function approve(string $type, int $id): ContentTranslation
    {
        $svc = app(TranslationService::class);
        $translator = $this->staff();
        $item = TranslationTypes::model($type)::findOrFail($id);
        // A person checks the imported draft and saves it as their own before it can be submitted.
        $edited = array_map(fn ($s) => $s['text'], $this->row($type, $id)->segments);
        $edited[array_key_first($edited)] .= ' ';
        $t = $svc->saveDraft($translator, $item, 'fr', $edited);
        $svc->submit($translator, $t);
        $svc->approve($this->staff('admin'), $t, null);

        return $this->row($type, $id);
    }

    #[Test]
    public function segments_cover_prose_only_and_never_change_structure_or_answers(): void
    {
        $lab = $this->lab();
        $english = app(TranslationService::class)->english($lab);

        $this->assertArrayHasKey('content.tasks[order].prompt', $english);
        $this->assertArrayHasKey('content.tasks[technique].input.options[t1190].label', $english);
        $this->assertArrayHasKey('validator.tasks.order.feedback.correct', $english);
        $this->assertSame(Segments::APPEND, $english['validator.tasks.persistence.accept']['kind']);
        foreach (array_keys($english) as $path) {
            $this->assertStringNotContainsString('workspace', $path, 'Lab data is never translated');
            // "feedback.correct" is the message shown for a right answer; a task's own "correct" is its answer key.
            $this->assertDoesNotMatchRegularExpression('/(?<!feedback)\.(id|type|correct|cells|value)$/', $path, "{$path} is an answer key or id");
        }

        // Laying text over every segment keeps ids, types, points and answer keys identical.
        $texts = array_map(fn ($s) => 'FR '.$s['text'], $english);
        $content = Segments::apply('content', 'lab_content', $lab->content, $texts);
        $validator = Segments::apply('validator', 'lab_validator', $lab->validator, $texts);
        $strip = fn ($v) => json_decode(preg_replace('/"FR [^"]*"/u', '""', json_encode($v)), true);
        $this->assertSame(array_column($lab->content['tasks'], 'id'), array_column($content['tasks'], 'id'));
        $this->assertSame($lab->content['workspace'], $content['workspace']);
        $this->assertSame($lab->validator['tasks']['order']['cells'], $validator['tasks']['order']['cells']);
        $this->assertSame($lab->validator['tasks']['technique']['correct'], $validator['tasks']['technique']['correct']);
        $this->assertStringStartsWith('FR ', $content['tasks'][0]['prompt']);
        // Accepted answers: French terms are added, the English ones stay.
        $this->assertContains('AdobeSyncHelper', $validator['tasks']['persistence']['accept']);
        $this->assertContains('FR AdobeSyncHelper', $validator['tasks']['persistence']['accept']);
        $this->assertNotNull($strip($content));
    }

    #[Test]
    public function seeded_french_files_load_as_unapproved_machine_drafts(): void
    {
        $t = $this->row('lesson', $this->lesson()->id);
        $this->assertSame(['draft', 'machine', null], [$t->status, $t->origin, $t->reviewed_by]);
        $this->assertStringStartsWith('## Pourquoi un SOC existe', $t->segments['body_md']['text']);
        $this->assertSame($this->lesson()->body_md, $t->segments['body_md']['source']);
        $this->assertTrue(ContentTranslation::where('item_type', 'lab')->where('item_id', $this->lab()->id)->exists());
        $this->assertTrue(ContentTranslation::where('item_type', 'question')->exists());

        // A draft never reaches learners: the English is shown with a notice.
        $learner = $this->learner();
        $this->actingAs($learner)->get(route('learn.lesson', ['soc-analyst', $this->lesson()->id]))->assertOk()
            ->assertSee('Why a SOC exists')->assertDontSee('Pourquoi un SOC existe')
            ->assertSee(__('This lesson is not yet available in French. It is shown in English.', [], 'fr'));
    }

    #[Test]
    public function approval_needs_a_second_person_and_then_french_learners_see_french(): void
    {
        $svc = app(TranslationService::class);
        $lesson = $this->lesson();
        $translator = $this->staff();
        $texts = array_map(fn ($s) => $s['text'], $this->row('lesson', $lesson->id)->segments);

        $t = $svc->saveDraft($translator, $lesson, 'fr', $texts);
        $this->assertSame(['draft', 'human', $translator->id], [$t->status, $t->origin, $t->translated_by]);
        $svc->submit($translator, $t);
        try {
            $svc->approve($translator, $t, null);
            $this->fail('The last editor must not approve their own translation.');
        } catch (RuleViolation) {
        }
        $this->assertSame('in_review', $t->fresh()->status);
        $svc->approve($this->staff(), $t->fresh(), 'Terminologie vérifiée.');
        $this->assertSame('approved', $t->fresh()->status);
        $this->assertSame(['translation.saved', 'translation.submitted', 'translation.approved'], AuditEvent::where('entity_type', 'translation')->orderBy('id')->pluck('action')->all());

        $this->actingAs($this->learner('fr'))->get(route('learn.lesson', ['soc-analyst', $lesson->id]))->assertOk()
            ->assertSee('Pourquoi un SOC existe')->assertSee('Ce que fait un SOC et comment il fonctionne')
            ->assertDontSee(__('This lesson is not yet available in French. It is shown in English.', [], 'fr'));
        $this->actingAs($this->learner('en'))->get(route('learn.lesson', ['soc-analyst', $lesson->id]))->assertOk()
            ->assertSee('Why a SOC exists')->assertDontSee('Pourquoi un SOC existe');
    }

    #[Test]
    public function whoever_sends_an_imported_draft_for_review_cannot_approve_it(): void
    {
        $svc = app(TranslationService::class);
        $instructor = $this->staff();
        $t = $this->row('lesson', $this->lesson()->id);
        $this->assertNull($t->translated_by);
        $svc->submit($instructor, $t);   // unchanged machine draft
        $this->assertSame($instructor->id, $t->fresh()->translated_by);
        $this->expectException(RuleViolation::class);
        $svc->approve($instructor, $t->fresh(), null);
    }

    /**
     * Quality gate for the French files in database/content: every draft is complete, code and
     * lab data are untouched, legal placeholders survive, and translated labs mark the known
     * solutions exactly as the English does.
     */
    #[Test]
    public function seeded_french_content_is_complete_and_keeps_code_and_marking_intact(): void
    {
        $svc = app(TranslationService::class);
        $rows = ContentTranslation::where('locale', 'fr')->get();
        $this->assertGreaterThan(100, $rows->count());
        $fences = fn (string $md) => preg_match_all('/^```(\w*)\n(.*?)^```/ms', $md, $m) ? array_map(null, $m[1], $m[2]) : [];

        foreach ($rows as $row) {
            $item = TranslationTypes::model($row->item_type)::findOrFail($row->item_id);
            $english = $svc->english($item);
            $c = $svc->compare($row, $english);
            $label = "{$row->item_type}:{$row->item_id}";
            $this->assertSame([], $c['missing'], "{$label} is incomplete");
            $this->assertSame([], $c['stale'], "{$label} was drafted from different English");

            foreach ($row->segments as $path => $seg) {
                if (! str_contains($english[$path]['text'], '```')) {
                    continue;
                }
                $en = $fences($english[$path]['text']);
                $fr = $fences($seg['text']);
                $this->assertCount(count($en), $fr, "{$label} {$path}: code blocks added or lost");
                foreach ($en as $i => [$lang, $code]) {
                    if (in_array($lang, ['kql', 'bash', 'yaml', 'powershell', 'json'], true)) {
                        $this->assertSame($code, $fr[$i][1], "{$label} {$path}: {$lang} block changed");
                    }
                }
            }
            if ($row->item_type === 'legal') {
                $this->assertSame(substr_count($english['body_md']['text'], '['), substr_count($row->segments['body_md']['text'], '['), "{$label}: placeholders lost");
            }
        }

        $labRows = $rows->where('item_type', 'lab');
        $this->assertGreaterThanOrEqual(11, $labRows->count(), 'SOC and GRC labs are drafted');
        foreach ($labRows as $row) {
            $lab = Lab::findOrFail($row->item_id);
            $texts = array_map(fn ($s) => $s['text'], $row->segments);
            $answers = LabSolutions::ALL[$lab->slug];
            $now = config('academy.lab_reference_now');
            $content = Segments::apply('content', 'lab_content', $lab->content, $texts);
            $en = \App\Labs\LabEvaluator::evaluate($lab->content, $lab->validator, $answers, $lab->pass_percent, $now);
            $fr = \App\Labs\LabEvaluator::evaluate($content, Segments::apply('validator', 'lab_validator', $lab->validator, $texts), $answers, $lab->pass_percent, $now);
            $this->assertTrue($en['passed'], $lab->slug);
            $this->assertSame([$en['score'], $en['maxScore'], $en['passed']], [$fr['score'], $fr['maxScore'], $fr['passed']], "{$lab->slug} marks differently in French");

            // Evidence data, tables, queries and code documents are identical; only reading documents change.
            $data = fn (array $ws) => ['rest' => array_diff_key($ws, ['documents' => 1]), 'code' => array_values(array_filter($ws['documents'] ?? [], fn ($d) => ($d['format'] ?? null) !== 'markdown'))];
            $this->assertSame($data($lab->content['workspace']), $data($content['workspace']), "{$lab->slug}: lab data changed");
            $this->assertSame(count($lab->content['workspace']['documents'] ?? []), count($content['workspace']['documents'] ?? []));
        }
    }

    #[Test]
    public function legal_pages_and_articles_follow_the_same_rules(): void
    {
        $this->get('/fr/legal/privacy')->assertOk()->assertSee('Who we are')
            ->assertSee(__('This content is not yet available in French. It is shown in English.', [], 'fr'));
        $page = LegalPage::where('slug', 'privacy')->firstOrFail();
        $this->assertSame('machine', $this->row('legal', $page->id)->origin);

        $this->approve('legal', $page->id);
        $this->get('/fr/legal/privacy')->assertOk()->assertSee('Qui sommes-nous')->assertSee('[À compléter', false);
        $this->get('/legal/privacy')->assertOk()->assertSee('Who we are')->assertDontSee('Qui sommes-nous');

        // legal.json stays the source of the English: a change there marks the French out of date.
        $page->forceFill(['body_md' => $page->body_md."\n\nNew clause."])->save();
        $this->assertTrue($this->row('legal', $page->id)->outdated);
        $this->get('/fr/legal/privacy')->assertSee('Who we are');

        $article = Article::where('slug', 'how-to-read-a-security-alert')->firstOrFail();
        $this->approve('article', $article->id);
        $this->get('/fr/resources/how-to-read-a-security-alert')->assertOk()->assertSee('Lire une alerte de sécurité en cinq minutes');
        $this->get('/fr/resources')->assertOk()->assertSee('Lire une alerte de sécurité en cinq minutes');
    }

    #[Test]
    public function an_incomplete_translation_cannot_be_sent_for_review(): void
    {
        $svc = app(TranslationService::class);
        $t = $svc->saveDraft($this->staff(), $this->lesson(), 'fr', ['body_md' => '']);   // removes the body
        $this->expectException(RuleViolation::class);
        $svc->submit($this->staff(), $t);
    }

    #[Test]
    public function an_english_change_makes_the_french_out_of_date_until_reviewed(): void
    {
        $svc = app(TranslationService::class);
        $lesson = $this->lesson();
        $this->approve('lesson', $lesson->id);
        $reviewer = $this->staff('admin');

        // A small English correction: the French is hidden and marked out of date…
        $lesson->forceFill(['body_md' => str_replace('Why a SOC exists', 'Why a SOC exists at all', $lesson->body_md)])->save();
        $t = $this->row('lesson', $lesson->id);
        $this->assertTrue($t->outdated);
        $this->assertSame('outdated', $svc->state($t, $svc->english($lesson->fresh())));
        $this->actingAs($this->learner())->get(route('learn.lesson', ['soc-analyst', $lesson->id]))->assertSee('Why a SOC exists at all');

        // …until a reviewer confirms the French still matches.
        $svc->confirmCurrent($reviewer, $t);
        $this->assertFalse($this->row('lesson', $lesson->id)->outdated);
        $this->actingAs($this->learner())->get(route('learn.lesson', ['soc-analyst', $lesson->id]))->assertSee('Pourquoi un SOC existe');

        // New English text cannot be "confirmed": it has to be translated and reviewed.
        $lesson = $lesson->fresh();
        $lesson->forceFill(['objectives' => [...$lesson->objectives, 'Name the documents to find in your first week']])->save();
        $this->assertTrue($this->row('lesson', $lesson->id)->outdated);
        $this->expectException(RuleViolation::class);
        $svc->confirmCurrent($reviewer, $this->row('lesson', $lesson->id));
    }

    #[Test]
    public function a_translated_lab_is_marked_exactly_like_the_english(): void
    {
        $lab = $this->lab();
        $this->approve('lab', $lab->id);
        $fr = $this->learner('fr');
        $en = $this->learner('en');

        $this->actingAs($fr)->get(route('learn.lab', ['soc-analyst', self::LAB]))->assertOk()
            ->assertSee('les cinq étapes de la compromission')
            ->assertSee('Investiguer une compromission')
            ->assertSee('sync-telemetry.example');   // evidence data is the same in both languages

        $answers = LabSolutions::ALL[self::LAB];
        $frResult = $this->actingAs($fr)->postJson(route('learn.lab.submit', ['soc-analyst', self::LAB]), ['answers' => $answers])->assertOk()->json();
        $enResult = $this->actingAs($en)->postJson(route('learn.lab.submit', ['soc-analyst', self::LAB]), ['answers' => $answers])->assertOk()->json();
        $this->assertTrue($frResult['passed']);
        $this->assertSame([$enResult['score'], $enResult['maxScore']], [$frResult['score'], $frResult['maxScore']]);
        $this->assertSame('Séquence correcte.', collect($frResult['results'])->firstWhere('taskId', 'order')['feedback']);
        $this->assertSame('Correct sequence.', collect($enResult['results'])->firstWhere('taskId', 'order')['feedback']);

        // The translated copy can never be saved over the English.
        $copy = $this->lab();
        app(TranslationService::class)->localise($copy, 'fr');
        $this->expectException(LogicException::class);
        $copy->save();
    }

    #[Test]
    public function exam_attempts_keep_the_language_they_started_in(): void
    {
        $svc = app(TranslationService::class);
        $exam = Exam::where('course_version_id', $this->soc()->current_version_id)->firstOrFail();
        $translator = $this->staff();
        $reviewer = $this->staff('admin');
        foreach (Question::where('question_bank_id', $exam->question_bank_id)->where('review_status', 'approved')->get() as $q) {
            $t = $svc->saveDraft($translator, $q, 'fr', array_map(fn ($s) => 'FR: '.$s['text'], $svc->english($q)));
            $svc->submit($translator, $t);
            $svc->approve($reviewer, $t, null);
        }

        $learner = User::factory()->create();
        $learner->forceFill(['locale' => 'fr'])->save();
        $this->enrolAndComplete($learner);
        app()->setLocale('fr');
        $attempt = app(ExamService::class)->start($learner->fresh(), 'soc-analyst');
        $this->assertSame('fr', $attempt->locale);
        $rows = ExamAttemptQuestion::where('exam_attempt_id', $attempt->id)->get();
        $this->assertTrue($rows->every(fn ($r) => str_starts_with($r->snapshot['prompt'], 'FR: ') && str_starts_with($r->snapshot['options'][0]['text'], 'FR: ')));

        // Later changes to the translation do not alter an attempt already started.
        $q = Question::find($rows->first()->question_id);
        $svc->saveDraft($translator, $q, 'fr', ['prompt' => 'Nouveau texte']);
        $this->assertStringStartsWith('FR: ', ExamAttemptQuestion::where('exam_attempt_id', $attempt->id)->where('question_id', $q->id)->first()->snapshot['prompt']);
    }

    private function enrolAndComplete(User $user): void
    {
        $spec = $this->soc();
        app(CheckoutService::class)->grantComplimentary($this->staff('admin'), $user->email, $spec, 'Test enrolment');
        $course = app(LearningService::class)->learnerCourse($user, 'soc-analyst');
        foreach ($course['orderedLessons'] as $l) {
            app(LearningService::class)->markLessonComplete($user, Lesson::find($l->id));
        }
        foreach ($course['labs'] as $l) {
            $this->assertTrue(app(LabService::class)->submit($user, Lab::find($l->id), LabSolutions::ALL[$l->slug], 0)['passed']);
        }
    }

    #[Test]
    public function a_new_course_version_keeps_its_translations(): void
    {
        $lesson = $this->lesson();
        $this->approve('lesson', $lesson->id);
        $draft = app(VersionService::class)->createDraft($this->staff('admin'), $this->soc());

        $copyModule = CourseModule::where('course_version_id', $draft->id)->where('slug', 'soc-fundamentals')->firstOrFail();
        $copy = Lesson::where('course_module_id', $copyModule->id)->where('slug', 'soc-role-and-workflows')->firstOrFail();
        $t = $this->row('lesson', $copy->id);
        $this->assertSame('approved', $t->status);
        $this->assertTrue(app(TranslationService::class)->usable($t, app(TranslationService::class)->english($copy)));
        $this->assertTrue(ContentTranslation::where(['item_type' => 'lab', 'item_id' => Lab::where('course_version_id', $draft->id)->where('slug', self::LAB)->value('id')])->exists());
    }

    #[Test]
    public function the_admin_editor_saves_segments_and_reviews_through_http(): void
    {
        $lesson = $this->lesson();
        $translator = $this->staff();
        $enc = fn (string $p) => strtr(base64_encode($p), '+/', '-_');

        $this->actingAs($translator)->get(route('admin.translations.index', ['programme' => 'soc-analyst']))->assertOk()
            ->assertSee('What a SOC does and how it works')->assertSee(__('Draft'));
        $this->get(route('admin.translations.edit', ['type' => 'lab', 'id' => $this->lab()->id]))->assertOk()
            ->assertSee('content.tasks[order].prompt')->assertSee(__('Machine-drafted'));
        $this->get(route('admin.translations.edit', ['type' => 'lesson', 'id' => $lesson->id]))->assertOk();

        $texts = array_map(fn ($s) => $s['text'], $this->row('lesson', $lesson->id)->segments);
        $payload = ['segments' => collect($texts)->mapWithKeys(fn ($t, $p) => [$enc($p) => $t])->all() + [$enc('not.a.real.path') => 'ignored'], 'then' => 'submit'];
        $this->put(route('admin.translations.update', ['type' => 'lesson', 'id' => $lesson->id]), $payload)->assertSessionHas('success');
        $t = $this->row('lesson', $lesson->id);
        $this->assertSame('in_review', $t->status);
        $this->assertArrayNotHasKey('not.a.real.path', $t->segments);

        $this->post(route('admin.translations.review', ['type' => 'lesson', 'id' => $lesson->id]), ['decision' => 'approve'])->assertSessionHas('error');
        $this->actingAs($this->staff('admin'))->post(route('admin.translations.review', ['type' => 'lesson', 'id' => $lesson->id]), ['decision' => 'return'])->assertSessionHasErrors('note');
        $this->post(route('admin.translations.review', ['type' => 'lesson', 'id' => $lesson->id]), ['decision' => 'return', 'note' => 'Revoir le tableau.'])->assertSessionHas('success');
        $this->assertSame(['draft', 'Revoir le tableau.'], [$this->row('lesson', $lesson->id)->status, $this->row('lesson', $lesson->id)->review_note]);

        // Learners cannot reach the editor.
        $this->actingAs($this->learner())->get(route('admin.translations.index'))->assertNotFound();
    }
}
