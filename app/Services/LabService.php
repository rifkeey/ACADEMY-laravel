<?php

namespace App\Services;

use App\Labs\Kql\Kql;
use App\Labs\LabEvaluator;
use App\Models\Enrolment;
use App\Models\Lab;
use App\Models\LabAttempt;
use App\Models\LabCompletion;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

/** Lab access, the server-side KQL console and lab submission. */
final class LabService
{
    private const MAX_ANSWER_BYTES = 64_000;

    public function __construct(private readonly CredentialService $credentials) {}

    /** The learner's enrolment that contains this lab, or an authorisation error. */
    public function enrolmentFor(User $user, Lab $lab): Enrolment
    {
        $enrolment = Enrolment::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('course_version_id', $lab->course_version_id)
            ->first();
        if (! $enrolment || $lab->status !== 'published') {
            throw new AuthorizationException('You are not enrolled in the course version that contains this lab.');
        }

        return $enrolment;
    }

    /**
     * Runs a learner's query in the KQL console. Only this lab's own synthetic tables are
     * visible, and the interpreter has no access to the database, files or network.
     */
    public function runQuery(User $user, Lab $lab, string $query): array
    {
        $this->enrolmentFor($user, $lab);
        $workspace = $lab->content['workspace'] ?? [];
        abort_unless(($workspace['kind'] ?? null) === 'kql', 404);

        return Kql::run($query, $workspace['tables'], config('academy.lab_reference_now'));
    }

    public function submit(User $user, Lab $lab, array $answers, int $hintsUsed): array
    {
        abort_if(strlen(json_encode($answers)) > self::MAX_ANSWER_BYTES, 422, 'Submission is too large.');
        $enrolment = $this->enrolmentFor($user, $lab);

        // Keep only answers for tasks that exist in this lab.
        $taskIds = array_column($lab->content['tasks'], 'id');
        $clean = array_intersect_key($answers, array_flip($taskIds));

        $result = LabEvaluator::evaluate($lab->content, $lab->validator, $clean, $lab->pass_percent, config('academy.lab_reference_now'));

        $attempt = DB::transaction(function () use ($user, $lab, $enrolment, $clean, $result, $hintsUsed) {
            $attempt = LabAttempt::create([
                'user_id' => $user->id, 'lab_id' => $lab->id, 'enrolment_id' => $enrolment->id,
                'answers' => $clean, 'task_results' => $result['results'], 'score' => $result['score'],
                'max_score' => $result['maxScore'], 'passed' => $result['passed'],
                'hints_used' => max(0, min(100, $hintsUsed)),
            ]);
            if ($result['passed']) {
                // First pass becomes the completion record; later passes change nothing.
                LabCompletion::insertOrIgnore(['user_id' => $user->id, 'lab_id' => $lab->id, 'lab_attempt_id' => $attempt->id, 'completed_at' => now()]);
            }

            return $attempt;
        });

        if ($result['passed']) {
            $this->credentials->issueIfEligible($user, $enrolment);
        }

        return ['attemptId' => $attempt->id] + $result;
    }
}
