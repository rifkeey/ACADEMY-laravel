<?php

namespace App\Http\Controllers;

use App\Exceptions\RuleViolation;
use App\Models\ExamAttempt;
use App\Services\ExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Final examination: rules and eligibility, taking an attempt, results. */
class ExamController extends Controller
{
    public function __construct(private readonly ExamService $exams) {}

    public function show(Request $request, string $slug): View
    {
        return view('learner.exam', $this->exams->status($request->user(), $slug) + ['slug' => $slug]);
    }

    public function start(Request $request, string $slug): RedirectResponse
    {
        try {
            $attempt = $this->exams->start($request->user(), $slug);
        } catch (RuleViolation $e) {
            return redirect()->route('exam.show', $slug)->with('warning', $e->getMessage());
        }

        return redirect()->route('exam.take', [$slug, $attempt]);
    }

    public function take(Request $request, string $slug, ExamAttempt $attempt): View|RedirectResponse
    {
        $data = $this->exams->forTaking($request->user(), $attempt);
        if ($attempt->status !== 'in_progress') {
            return redirect()->route('exam.result', [$slug, $attempt]);
        }

        return view('learner.exam-take', $data + ['slug' => $slug]);
    }

    public function submit(Request $request, string $slug, ExamAttempt $attempt): RedirectResponse
    {
        $data = $request->validate(['responses' => ['nullable', 'array', 'max:200'], 'responses.*' => ['array', 'max:10'], 'responses.*.*' => ['integer']]);
        $this->exams->submit($request->user(), $attempt, $data['responses'] ?? []);

        return redirect()->route('exam.result', [$slug, $attempt]);
    }

    public function result(Request $request, string $slug, ExamAttempt $attempt): View
    {
        return view('learner.exam-result', $this->exams->result($request->user(), $attempt) + ['slug' => $slug]);
    }
}
