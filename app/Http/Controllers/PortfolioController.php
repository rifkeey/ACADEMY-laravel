<?php

namespace App\Http\Controllers;

use App\Models\Enrolment;
use App\Models\Lab;
use App\Models\LabAttempt;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The learner's portfolio: the written deliverables (reports, policies, plans) from their most
 * recent attempt at each lab. Only the signed-in learner's own work is ever shown.
 */
class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $enrolments = Enrolment::with('specialization:id,title,slug')->where('user_id', $user->id)->where('status', 'active')->get();

        $programmes = [];
        foreach ($enrolments as $enrolment) {
            $labs = Lab::where('course_version_id', $enrolment->course_version_id)->where('status', 'published')->orderBy('position')->get();
            $items = [];
            foreach ($labs as $lab) {
                $tasks = array_values(array_filter($lab->content['tasks'], fn ($t) => ($t['input']['type'] ?? null) === 'deliverable'));
                if (! $tasks) {
                    continue;
                }
                $attempt = LabAttempt::where('user_id', $user->id)->where('lab_id', $lab->id)->latest('submitted_at')->latest('id')->first();   // id breaks ties within the same second
                foreach ($tasks as $task) {
                    $result = collect($attempt?->task_results ?? [])->firstWhere('taskId', $task['id']);
                    $items[] = [
                        'lab' => $lab,
                        'task' => $task,
                        'text' => is_string($attempt?->answers[$task['id']] ?? null) ? trim($attempt->answers[$task['id']]) : '',
                        'complete' => (bool) ($result['correct'] ?? false),
                        'submittedAt' => $attempt?->submitted_at,
                    ];
                }
            }
            if ($items) {
                $programmes[] = ['spec' => $enrolment->specialization, 'items' => $items];
            }
        }

        return view('learner.portfolio', ['programmes' => $programmes, 'user' => $user]);
    }
}
