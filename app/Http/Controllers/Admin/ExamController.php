<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RuleViolation;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Examination settings per course version. Each attempt stores the pass mark it started with,
 * so changing the settings never re-grades attempts already taken.
 */
class ExamController extends Controller
{
    public function index(): View
    {
        $this->authorize('exams.configure');
        $exams = Exam::with('courseVersion.specialization:id,title,slug')->orderBy('course_version_id')->get();
        $approved = Question::where('review_status', 'approved')->groupBy('question_bank_id')->selectRaw('question_bank_id, COUNT(*) n')->pluck('n', 'question_bank_id');
        $attempts = ExamAttempt::groupBy('exam_id')->selectRaw('exam_id, COUNT(*) n, SUM(passed = 1) passes')->get()->keyBy('exam_id');

        return view('admin.exams.index', ['exams' => $exams, 'approved' => $approved, 'attempts' => $attempts]);
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorize('exams.configure');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'question_count' => ['required', 'integer', 'min:5', 'max:200'],
            'pass_percent' => ['required', 'integer', 'min:50', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'cooldown_hours' => ['required', 'integer', 'min:0', 'max:720'],
            'show_explanations' => ['required', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);
        if ($exam->courseVersion->status === 'retired') {
            throw new RuleViolation('This exam belongs to a retired version and is read-only.');
        }
        $approved = Question::where('question_bank_id', $exam->question_bank_id)->where('review_status', 'approved')->count();
        if ($data['status'] === 'published' && $approved < $data['question_count']) {
            throw new RuleViolation("Only {$approved} approved questions are available; at least {$data['question_count']} are needed to publish.");
        }

        DB::transaction(function () use ($request, $exam, $data) {
            $locked = Exam::whereKey($exam->id)->lockForUpdate()->first();
            $locked->fill([])->forceFill($data);
            if ($locked->isDirty()) {
                $locked->config_version++;   // recorded on each new attempt
                $locked->save();
                AuditLogger::record($request->user()->id, 'exam.configured', 'exam', $locked->id, $locked->getChanges());
            }
        });

        return back()->with('success', 'Examination settings saved. Attempts already started keep their original settings.');
    }
}
