<?php

namespace App\Services;

use App\Assessment\Grading;
use App\Exceptions\RuleViolation;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptQuestion;
use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Random\Randomizer;

/**
 * Final examinations: eligibility, randomised attempts, server-side grading.
 *
 * Correct answers never leave the server before submission: the taking view receives prompts and
 * option texts only, and each attempt keeps its own snapshot so later edits to the question bank
 * or pass mark cannot change a recorded result.
 */
final class ExamService
{
    public function __construct(
        private readonly LearningService $learning,
        private readonly CredentialService $credentials,
    ) {}

    private function graceSeconds(): int
    {
        return (int) config('academy.exam_submit_grace_seconds', 30);
    }

    private function context(User $user, string $slug): array
    {
        $enrolment = $this->learning->activeEnrolment($user, $slug);
        if (! $enrolment) {
            throw new AuthorizationException('You are not enrolled in this programme.');
        }

        return [$enrolment, Exam::where('course_version_id', $enrolment->course_version_id)->first()];
    }

    /** Eligibility, attempts, cooldown and any open attempt, with a learner-facing reason. */
    public function status(User $user, string $slug): array
    {
        [$enrolment, $exam] = $this->context($user, $slug);
        $requirements = $this->learning->computeRequirements($enrolment);
        if (! $exam) {
            return ['spec' => $enrolment->specialization, 'exam' => null, 'requirements' => $requirements, 'attempts' => collect(), 'attemptsUsed' => 0, 'canStart' => false, 'reason' => 'No examination is configured for this programme yet.', 'openAttempt' => null];
        }

        $attempts = ExamAttempt::where('enrolment_id', $enrolment->id)->where('exam_id', $exam->id)->latest('started_at')->get();
        $graceCutoff = now()->subSeconds($this->graceSeconds());
        $open = $attempts->first(fn ($a) => $a->status === 'in_progress' && $a->expires_at->gt($graceCutoff));
        $used = $attempts->filter(fn ($a) => $a->status === 'submitted' || $a->expires_at->lte($graceCutoff));

        $reason = null;
        if ($exam->status !== 'published') {
            $reason = 'The examination for this programme has not been published yet.';
        } elseif (! $requirements['examEligible']) {
            $reason = 'Complete all required lessons and pass all required labs to unlock the examination.';
        } elseif ($requirements['examPassed']) {
            $reason = 'You have already passed this examination.';
        } elseif ($used->count() >= $exam->max_attempts) {
            $reason = "You have used all {$exam->max_attempts} attempts. Contact support if you need help.";
        } elseif ($last = $used->first()) {
            $retakeAt = $last->started_at->copy()->addHours($exam->cooldown_hours);
            if ($retakeAt->isFuture() && ! $open) {
                $reason = 'You can retake the examination after '.$retakeAt->format('j M Y, H:i').' UTC.';
            }
        }

        return [
            'spec' => $enrolment->specialization, 'exam' => $exam, 'requirements' => $requirements,
            'attempts' => $attempts, 'attemptsUsed' => $used->count(),
            'canStart' => ! $reason || (bool) $open, 'reason' => $open ? null : $reason, 'openAttempt' => $open,
        ];
    }

    /** Starts an attempt (or returns the open one). Eligibility is enforced here on the server. */
    public function start(User $user, string $slug): ExamAttempt
    {
        $status = $this->status($user, $slug);
        if ($status['openAttempt']) {
            return $status['openAttempt'];
        }
        if (! $status['exam'] || ! $status['canStart']) {
            throw new RuleViolation($status['reason'] ?? 'The examination is not available.');
        }
        /** @var Exam $exam */
        $exam = $status['exam'];
        [$enrolment] = $this->context($user, $slug);

        $pool = Question::where('question_bank_id', $exam->question_bank_id)->where('review_status', 'approved')
            ->with(['options' => fn ($q) => $q->orderBy('position')])->get()->all();
        if (count($pool) < $exam->question_count) {
            throw new RuleViolation('The question bank does not yet contain enough approved questions.');
        }
        $random = new Randomizer;   // secure randomness for selection and option order
        $chosen = Grading::selectBalanced($pool, $exam->question_count, $random);

        return DB::transaction(function () use ($user, $exam, $enrolment, $chosen, $random) {
            // Close any expired, never-submitted attempt (it scores zero) so a new one can open.
            ExamAttempt::where('user_id', $user->id)->where('exam_id', $exam->id)->where('status', 'in_progress')
                ->update(['status' => 'submitted', 'submitted_at' => DB::raw('expires_at'), 'correct_count' => 0, 'passed' => false]);

            $number = ExamAttempt::where('user_id', $user->id)->where('exam_id', $exam->id)->lockForUpdate()->count() + 1;
            $attempt = ExamAttempt::create([
                'public_id' => (string) Str::ulid(), 'user_id' => $user->id, 'exam_id' => $exam->id, 'enrolment_id' => $enrolment->id,
                'attempt_number' => $number, 'exam_config_version' => $exam->config_version, 'pass_percent' => $exam->pass_percent,
                'started_at' => now(), 'expires_at' => now()->addMinutes($exam->duration_minutes), 'total_count' => count($chosen),
            ]);

            $rows = [];
            foreach ($chosen as $i => $q) {
                $options = $random->shuffleArray($q->options->map(fn ($o) => ['id' => $o->id, 'text' => $o->text, 'isCorrect' => (bool) $o->is_correct])->all());
                $rows[] = [
                    'exam_attempt_id' => $attempt->id, 'position' => $i + 1, 'question_id' => $q->id,
                    'snapshot' => json_encode(['prompt' => $q->prompt, 'kind' => $q->kind, 'category' => $q->category, 'explanation' => $q->explanation, 'options' => $options]),
                ];
            }
            ExamAttemptQuestion::insert($rows);

            return $attempt;
        });
    }

    /** Questions for an in-progress attempt WITHOUT correctness or explanations. */
    public function forTaking(User $user, ExamAttempt $attempt): array
    {
        $this->assertOwner($user, $attempt);
        $questions = ExamAttemptQuestion::where('exam_attempt_id', $attempt->id)->orderBy('position')->get()
            ->map(fn ($r) => [
                'position' => $r->position,
                'prompt' => $r->snapshot['prompt'],
                'kind' => $r->snapshot['kind'],
                'options' => array_map(fn ($o) => ['id' => $o['id'], 'text' => $o['text']], $r->snapshot['options']),
            ])->all();

        return ['attempt' => $attempt, 'questions' => $questions];
    }

    /**
     * Grades and finalises an attempt exactly once. The row is locked (SELECT ... FOR UPDATE) and
     * re-checked inside the transaction, so double clicks and concurrent submits are harmless.
     *
     * @param  array<string, list<int|string>>  $responses  position => selected option ids
     */
    public function submit(User $user, ExamAttempt $attempt, array $responses): array
    {
        $this->assertOwner($user, $attempt);

        $result = DB::transaction(function () use ($attempt, $responses) {
            $locked = ExamAttempt::whereKey($attempt->id)->lockForUpdate()->first();
            if ($locked->status !== 'in_progress') {
                return ['attempt' => $locked, 'alreadySubmitted' => true];
            }
            $late = now()->gt($locked->expires_at->copy()->addSeconds($this->graceSeconds()));
            $correct = 0;

            foreach (ExamAttemptQuestion::where('exam_attempt_id', $locked->id)->get() as $row) {
                $validIds = array_column($row->snapshot['options'], 'id');
                $raw = $late ? [] : (array) ($responses[(string) $row->position] ?? []);
                // Only accept option ids that belong to this question; single-answer takes the first.
                $selected = array_values(array_intersect($validIds, array_map('intval', $raw)));
                if ($row->snapshot['kind'] === 'single') {
                    $selected = array_slice($selected, 0, 1);
                }
                $correctIds = array_column(array_filter($row->snapshot['options'], fn ($o) => $o['isCorrect']), 'id');
                $ok = Grading::isAnswerCorrect($correctIds, $selected);
                $correct += $ok ? 1 : 0;

                ExamAttemptQuestion::where('exam_attempt_id', $locked->id)->where('position', $row->position)
                    ->update(['selected_option_ids' => json_encode($selected), 'is_correct' => $ok]);
            }

            $locked->forceFill([
                'status' => 'submitted', 'submitted_at' => now(), 'correct_count' => $correct,
                'passed' => Grading::isPass($correct, $locked->total_count, $locked->pass_percent),
            ])->save();

            return ['attempt' => $locked, 'alreadySubmitted' => false];
        });

        if ($result['attempt']->passed) {
            $this->credentials->issueIfEligible($user, $result['attempt']->enrolment()->first());
        }

        return $result;
    }

    /** Result view after submission, with explanations when the exam settings allow. */
    public function result(User $user, ExamAttempt $attempt): array
    {
        $this->assertOwner($user, $attempt);
        $exam = $attempt->exam()->first();
        $rows = ExamAttemptQuestion::where('exam_attempt_id', $attempt->id)->orderBy('position')->get();
        $categories = [];
        foreach ($rows as $r) {
            $c = $r->snapshot['category'];
            $categories[$c] ??= ['correct' => 0, 'total' => 0];
            $categories[$c]['total']++;
            $categories[$c]['correct'] += $r->is_correct ? 1 : 0;
        }
        $items = $attempt->status === 'submitted' && $exam->show_explanations
            ? $rows->map(fn ($r) => ['position' => $r->position, 'isCorrect' => (bool) $r->is_correct, 'selected' => $r->selected_option_ids ?? []] + $r->snapshot)->all()
            : [];

        return [
            'attempt' => $attempt, 'exam' => $exam, 'items' => $items, 'categories' => $categories,
            'percent' => Grading::percent((int) $attempt->correct_count, $attempt->total_count),
        ];
    }

    private function assertOwner(User $user, ExamAttempt $attempt): void
    {
        // 404 rather than 403, so attempt ids of other learners cannot be confirmed.
        abort_unless($attempt->user_id === $user->id, 404);
    }
}
