<?php

namespace Tests\Feature;

use App\Labs\LabEvaluator;
use App\Models\Lab;
use App\Models\Lesson;
use App\Models\Specialization;
use Database\Seeders\CatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\LabSolutions;
use Tests\TestCase;

/** The seeded catalogue is complete, consistent and every lab can actually be passed. */
class ContentIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = CatalogueSeeder::class;

    #[Test]
    public function catalogue_matches_the_authored_content(): void
    {
        $this->assertSame(5, Specialization::count());
        // Every lesson and lab in the authored files reaches the database.
        $authored = collect(glob(database_path('content/programmes/*/programme.json')))->map(fn ($f) => json_decode(file_get_contents($f), true));
        $this->assertSame($authored->sum(fn ($p) => collect($p['modules'])->sum(fn ($m) => count($m['lessons']))), Lesson::count());
        $this->assertSame($authored->sum(fn ($p) => count($p['labs'])), Lab::count());
        $this->assertGreaterThanOrEqual(73, Lesson::count());
        $this->assertEqualsCanonicalizing(
            ['soc-analyst' => 18000000, 'penetration-testing' => 22000000, 'governance-risk-compliance' => 15000000, 'network-security' => 18000000, 'cloud-security' => 22000000],
            Specialization::with('offers')->get()->mapWithKeys(fn ($s) => [$s->slug => $s->offers->first()->amount_minor])->all(),
        );
        $this->assertTrue(Lesson::where('body_md', '')->doesntExist(), 'Every lesson has a body');
    }

    #[Test]
    public function every_lab_is_solvable_and_an_empty_submission_fails(): void
    {
        foreach (Lab::all() as $lab) {
            $this->assertArrayHasKey($lab->slug, LabSolutions::ALL, "Reference answers for {$lab->slug}");
            $ok = LabEvaluator::evaluate($lab->content, $lab->validator, LabSolutions::ALL[$lab->slug], 100, config('academy.lab_reference_now'));
            $this->assertSame([], array_values(array_filter($ok['results'], fn ($r) => ! $r['correct'])), $lab->slug);
            $empty = LabEvaluator::evaluate($lab->content, $lab->validator, [], $lab->pass_percent, config('academy.lab_reference_now'));
            $this->assertFalse($empty['passed'], "{$lab->slug} must not pass empty");
            // Answer keys never appear in learner-visible content.
            $this->assertStringNotContainsString('"correct"', json_encode($lab->content));
            $this->assertArrayNotHasKey('validator', $lab->toArray());

            // The console's pre-filled starter query must not answer any KQL task by itself.
            if ($starter = $lab->content['workspace']['sampleQuery'] ?? null) {
                $kqlTasks = array_filter($lab->content['tasks'], fn ($t) => $t['input']['type'] === 'kql');
                $answers = array_fill_keys(array_column($kqlTasks, 'id'), $starter);
                $res = LabEvaluator::evaluate($lab->content, $lab->validator, $answers, 100, config('academy.lab_reference_now'));
                foreach ($res['results'] as $r) {
                    $this->assertFalse($r['correct'], "{$lab->slug}: starter query must not solve {$r['taskId']}");
                }
            }
        }
    }

    #[Test]
    public function open_programmes_have_a_published_exam_with_enough_approved_questions(): void
    {
        foreach (Specialization::where('enrolment_open', true)->get() as $spec) {
            $exam = $spec->currentVersion()->first()->exam()->first();
            $this->assertSame('complete', $spec->content_status);
            $this->assertSame('published', $exam->status);
            $this->assertSame(75, $exam->pass_percent);
            $this->assertGreaterThanOrEqual($exam->question_count, $exam->bank()->first()->questions()->where('review_status', 'approved')->count());
        }
        $this->assertEqualsCanonicalizing(['soc-analyst', 'governance-risk-compliance'], Specialization::where('enrolment_open', true)->pluck('slug')->all());
    }
}
