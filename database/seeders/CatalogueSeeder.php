<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\BadgeDefinition;
use App\Models\CourseModule;
use App\Models\CourseVersion;
use App\Models\Exam;
use App\Models\Lab;
use App\Models\Lesson;
use App\Models\Offer;
use App\Models\PlatformSetting;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\SourceReference;
use App\Models\Specialization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Loads the authored catalogue from database/content into MySQL as course version 1.
 *
 * Idempotent and non-destructive: rows that already exist are left alone (administrators may
 * have edited them), missing rows are created. This is real catalogue content, not demo data,
 * so it is safe to run in every environment: `php artisan db:seed --class=CatalogueSeeder`.
 */
class CatalogueSeeder extends Seeder
{
    private const REVIEW_DATE = '2026-10-17';

    public function run(): void
    {
        $root = database_path('content');

        DB::transaction(function () use ($root) {
            $sourceIds = $this->seedSources($root);

            foreach (glob("{$root}/programmes/*/programme.json") as $file) {
                $def = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
                $this->seedProgramme($def, dirname($file), $sourceIds);
            }

            $this->seedArticles($root);
            $this->seedSettings();
        });
    }

    /** @return array<string, int> source key => id */
    private function seedSources(string $root): array
    {
        $data = json_decode(file_get_contents("{$root}/sources.json"), true, flags: JSON_THROW_ON_ERROR);
        $unavailable = $data['checks']['unavailable'] ?? [];
        foreach ($data['sources'] as $s) {
            SourceReference::firstOrCreate(['key' => $s['key']], [
                'title' => $s['title'], 'publisher' => $s['publisher'], 'url' => $s['url'], 'topic' => $s['topic'],
                'source_type' => $s['sourceType'], 'version_note' => $s['versionNote'], 'supports' => $s['supports'],
                'date_checked' => $s['dateChecked'] ?? $data['checks']['checkedOn'],   // per-source date when it was checked separately
                'verification_status' => in_array($s['key'], $unavailable, true) ? 'unavailable' : 'verified',
            ]);
        }

        return SourceReference::pluck('id', 'key')->all();
    }

    private function seedProgramme(array $def, string $dir, array $sourceIds): void
    {
        $spec = Specialization::firstOrCreate(['slug' => $def['slug']], [
            'code' => $def['code'], 'title' => $def['title'], 'category' => $def['category'], 'level' => $def['level'],
            'summary' => $def['summary'], 'description' => $def['description'], 'audience' => $def['audience'],
            'prerequisites' => $def['prerequisites'], 'outcomes' => $def['outcomes'], 'inclusions' => $def['inclusions'],
            'faqs' => $def['faqs'], 'estimated_hours' => $def['estimatedHours'], 'status' => 'published',
            'enrolment_open' => $def['enrolmentOpen'], 'content_status' => $def['contentStatus'], 'sort_order' => $def['sortOrder'],
        ]);

        $version = CourseVersion::firstOrCreate(
            ['specialization_id' => $spec->id, 'version' => 1],
            ['status' => 'published', 'changelog' => 'Initial release.', 'published_at' => now()],
        );
        if (! $spec->current_version_id) {
            $spec->forceFill(['current_version_id' => $version->id])->save();
        }

        $moduleIds = [];
        foreach ($def['modules'] as $mi => $m) {
            $module = CourseModule::firstOrCreate(['course_version_id' => $version->id, 'slug' => $m['slug']], [
                'position' => $mi + 1, 'title' => $m['title'], 'purpose' => $m['purpose'],
                'prerequisite_knowledge' => $m['prerequisiteKnowledge'], 'objectives' => $m['objectives'],
                'key_concepts' => $m['keyConcepts'], 'assessment_criteria' => $m['assessmentCriteria'],
                'instructor_notes' => $m['instructorNotes'] ?? null, 'review_date' => self::REVIEW_DATE,
                'content_status' => $m['contentStatus'],
            ]);
            $moduleIds[$m['slug']] = $module->id;

            // New lessons are appended after existing ones so positions never collide.
            $nextPosition = (int) Lesson::where('course_module_id', $module->id)->max('position');
            foreach ($m['lessons'] as $l) {
                if (Lesson::where('course_module_id', $module->id)->where('slug', $l['slug'])->exists()) {
                    continue;
                }
                $lesson = Lesson::create([
                    'course_module_id' => $module->id, 'slug' => $l['slug'], 'position' => ++$nextPosition,
                    'title' => $l['title'], 'objectives' => $l['objectives'],
                    'body_md' => file_get_contents("{$dir}/lessons/{$m['slug']}/{$l['slug']}.md"),
                    'estimated_minutes' => $l['minutes'], 'knowledge_checks' => $l['checks'] ?? [],
                ]);
                $lesson->sources()->sync(array_values(array_filter(array_map(fn ($k) => $sourceIds[$k] ?? null, $l['sources'] ?? []))));
            }
        }

        $nextLab = (int) Lab::where('course_version_id', $version->id)->max('position');
        foreach ($def['labs'] as $lab) {
            Lab::firstOrCreate(['course_version_id' => $version->id, 'slug' => $lab['slug']], [
                'course_module_id' => $moduleIds[$lab['moduleSlug']] ?? null, 'position' => ++$nextLab,
                'title' => $lab['title'], 'difficulty' => $lab['difficulty'], 'lab_type' => $lab['labType'],
                'summary' => $lab['summary'], 'estimated_minutes' => $lab['minutes'], 'required' => $lab['required'] ?? true,
                'pass_percent' => $lab['passPercent'], 'content' => $lab['content'], 'validator' => $lab['validator'],
            ]);
        }

        $bank = QuestionBank::firstOrCreate(['specialization_id' => $spec->id], ['title' => "{$def['title']} question bank"]);
        foreach ($def['questions'] as $q) {
            if (Question::where('question_bank_id', $bank->id)->where('external_key', $q['key'])->exists()) {
                continue;
            }
            $question = Question::create([
                'question_bank_id' => $bank->id, 'external_key' => $q['key'], 'kind' => $q['kind'], 'category' => $q['category'],
                'difficulty' => $q['difficulty'], 'objective' => $q['objective'], 'tags' => $q['tags'] ?? [],
                'prompt' => $q['prompt'], 'explanation' => $q['explanation'], 'review_status' => 'approved',
            ]);
            foreach ($q['options'] as $pos => $o) {
                $question->options()->create(['position' => $pos + 1, 'text' => $o['text'], 'is_correct' => ! empty($o['correct'])]);
            }
        }

        Exam::firstOrCreate(['course_version_id' => $version->id], [
            'question_bank_id' => $bank->id, 'title' => $def['exam']['title'], 'question_count' => $def['exam']['questionCount'],
            'pass_percent' => $def['exam']['passPercent'], 'duration_minutes' => $def['exam']['durationMinutes'],
            'max_attempts' => $def['exam']['maxAttempts'], 'cooldown_hours' => $def['exam']['cooldownHours'],
            'status' => $def['exam']['status'],
        ]);

        BadgeDefinition::firstOrCreate(['specialization_id' => $spec->id], $def['badge']);

        if (! Offer::where('specialization_id', $spec->id)->where('currency', $def['price']['currency'])->exists()) {
            Offer::create(['specialization_id' => $spec->id, 'currency' => $def['price']['currency'], 'amount_minor' => $def['price']['amountMinor']]);
        }
    }

    private function seedArticles(string $root): void
    {
        foreach (json_decode(file_get_contents("{$root}/articles.json"), true) as $a) {
            Article::firstOrCreate(['slug' => $a['slug']], [
                'title' => $a['title'], 'summary' => $a['summary'], 'body_md' => $a['body'], 'tags' => $a['tags'],
                'status' => 'published', 'published_at' => $a['publishedAt'],
            ]);
        }
    }

    private function seedSettings(): void
    {
        foreach ([
            'verification_name_display' => 'full',
            'contact_email' => '',              // set the real address in Admin → Settings
            'support_hours' => 'Monday–Friday, 09:00–17:00 WAT',
            'refund_policy_days' => '',
            'certificate_signatory' => '',
        ] as $key => $value) {
            PlatformSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
