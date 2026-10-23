<?php

namespace App\Http\Controllers;

use App\Labs\Kql\KqlError;
use App\Models\Lab;
use App\Models\LabAttempt;
use App\Models\LabCompletion;
use App\Services\LabService;
use App\Services\LearningService;
use App\Support\RichMarkdown;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Lab page, server-side KQL console and lab submission (JSON endpoints used by the lab runner). */
class LabController extends Controller
{
    public function __construct(private readonly LabService $labs, private readonly LearningService $learning) {}

    /** Resolves a lab by slug inside the version the learner is enrolled in. */
    private function lab(Request $request, string $slug, string $labSlug): Lab
    {
        $enrolment = $this->learning->activeEnrolment($request->user(), $slug);
        abort_unless($enrolment, 403, 'You are not enrolled in this programme.');

        return Lab::where('course_version_id', $enrolment->course_version_id)->where('slug', $labSlug)->where('status', 'published')->firstOrFail();
    }

    public function show(Request $request, string $slug, string $labSlug): View
    {
        $lab = $this->lab($request, $slug, $labSlug);
        $attempts = LabAttempt::where('user_id', $request->user()->id)->where('lab_id', $lab->id)->latest('submitted_at')->limit(10)->get();

        return view('learner.lab', [
            'slug' => $slug,
            'spec' => $this->learning->activeEnrolment($request->user(), $slug)->specialization,
            'lab' => $lab,                        // the view only uses public fields; validator is hidden
            'attempts' => $attempts,
            'completed' => LabCompletion::where('user_id', $request->user()->id)->where('lab_id', $lab->id)->exists(),
        ]);
    }

    public function query(Request $request, string $slug, string $labSlug): JsonResponse
    {
        $data = $request->validate(['query' => ['required', 'string', 'max:4000']]);
        try {
            return response()->json(['ok' => true] + $this->labs->runQuery($request->user(), $this->lab($request, $slug, $labSlug), $data['query']));
        } catch (KqlError $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function submit(Request $request, string $slug, string $labSlug): JsonResponse
    {
        $data = $request->validate(['answers' => ['present', 'array'], 'hints_used' => ['nullable', 'integer', 'min:0', 'max:100']]);
        $lab = $this->lab($request, $slug, $labSlug);
        $result = $this->labs->submit($request->user(), $lab, $data['answers'], (int) ($data['hints_used'] ?? 0));

        // Only results and feedback go back to the browser, never the answer keys.
        // The debrief contains the worked solution, so it is only sent once the lab is passed.
        $debrief = $result['passed'] && ! empty($lab->validator['debrief']) ? RichMarkdown::render($lab->validator['debrief']) : null;   // stored with the answer key

        return response()->json([
            'score' => $result['score'], 'maxScore' => $result['maxScore'], 'passed' => $result['passed'], 'debriefHtml' => $debrief,
            'results' => array_map(fn ($r) => array_intersect_key($r, array_flip(['taskId', 'correct', 'feedback', 'detail', 'points', 'pointsAwarded'])), $result['results']),
        ]);
    }
}
