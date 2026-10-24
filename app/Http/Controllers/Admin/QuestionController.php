<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RuleViolation;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Question bank. Only "approved" questions are drawn into exams. Editing a question never
 * changes past attempts, because every attempt stores a snapshot of the questions it used.
 */
class QuestionController extends Controller
{
    private const STATUSES = ['draft', 'approved', 'retired'];

    public function index(Request $request): View
    {
        $this->authorize('questions.edit');
        $bank = QuestionBank::find($request->integer('bank')) ?? QuestionBank::orderBy('id')->first();
        $status = in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : null;
        $category = $request->query('category');
        $q = trim((string) $request->query('q'));

        $questions = Question::query()
            ->when($bank, fn ($w) => $w->where('question_bank_id', $bank->id))
            ->when($status, fn ($w) => $w->where('review_status', $status))
            ->when(is_string($category) && $category !== '', fn ($w) => $w->where('category', $category))
            ->when($q !== '', fn ($w) => $w->where('prompt', 'like', '%'.addcslashes($q, '%_').'%'))
            ->withCount('options')->orderBy('category')->orderBy('id')
            ->paginate(30)->withQueryString();

        return view('admin.questions.index', [
            'questions' => $questions, 'bank' => $bank, 'status' => $status, 'category' => $category, 'q' => $q,
            'banks' => QuestionBank::with('specialization:id,title')->get(),
            'categories' => $bank ? Question::where('question_bank_id', $bank->id)->distinct()->orderBy('category')->pluck('category') : collect(),
            'counts' => $bank ? Question::where('question_bank_id', $bank->id)->groupBy('review_status')->selectRaw('review_status, COUNT(*) n')->pluck('n', 'review_status') : collect(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('questions.edit');

        return view('admin.questions.form', [
            'question' => new Question(['kind' => 'single', 'review_status' => 'draft', 'difficulty' => 'foundation', 'question_bank_id' => $request->integer('bank') ?: null]),
            'options' => collect(),
            'banks' => QuestionBank::with('specialization:id,title')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('questions.edit');
        $data = $this->validated($request, creating: true);

        $question = DB::transaction(function () use ($request, $data) {
            $question = Question::create($this->fields($data) + [
                'question_bank_id' => $data['question_bank_id'],
                'external_key' => 'adm-'.Str::lower(Str::random(10)),
                'tags' => [],
            ]);
            $this->syncOptions($question, $data['options']);
            AuditLogger::record($request->user()->id, 'question.created', 'question', $question->id, ['bank' => $data['question_bank_id']]);

            return $question;
        });

        return redirect()->route('admin.questions.edit', $question)->with('success', __('Question created.'));
    }

    public function edit(Question $question): View
    {
        $this->authorize('questions.edit');
        $question->load('bank.specialization');

        return view('admin.questions.form', [
            'question' => $question,
            'options' => QuestionOption::where('question_id', $question->id)->orderBy('position')->get()->each->makeVisible('is_correct'),
            'banks' => collect([$question->bank]),
        ]);
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $this->authorize('questions.edit');
        $data = $this->validated($request, creating: false);

        DB::transaction(function () use ($request, $question, $data) {
            $question->forceFill($this->fields($data))->save();
            $this->syncOptions($question, $data['options']);
            AuditLogger::record($request->user()->id, 'question.updated', 'question', $question->id, ['status' => $question->review_status]);
        });

        return back()->with('success', __('Question saved. Past attempts keep the version they were given.'));
    }

    /** Review several questions at once, for example approving a reviewed category. Each change is audited. */
    public function bulkStatus(Request $request): RedirectResponse
    {
        $this->authorize('questions.edit');
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'distinct'],
            'review_status' => ['required', Rule::in(self::STATUSES)],
        ]);

        $changed = DB::transaction(function () use ($request, $data) {
            $questions = Question::whereIn('id', $data['ids'])->where('review_status', '!=', $data['review_status'])->lockForUpdate()->get();
            foreach ($questions as $question) {
                $from = $question->review_status;
                $question->forceFill(['review_status' => $data['review_status']])->save();
                AuditLogger::record($request->user()->id, 'question.reviewed', 'question', $question->id, ['from' => $from, 'to' => $data['review_status']]);
            }

            return $questions->count();
        });

        return back()->with('success', __(':changed question(s) marked :review_status.', ['changed' => $changed, 'review_status' => __($data['review_status'])]));
    }

    private function validated(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'question_bank_id' => [$creating ? 'required' : 'prohibited', 'integer', 'exists:question_banks,id'],
            'kind' => ['required', Rule::in(['single', 'multiple'])],
            'category' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9-]+$/'],
            'difficulty' => ['required', Rule::in(['foundation', 'intermediate', 'advanced'])],
            'objective' => ['nullable', 'string', 'max:300'],
            'prompt' => ['required', 'string', 'max:4000'],
            'explanation' => ['nullable', 'string', 'max:4000'],
            'review_status' => ['required', Rule::in(self::STATUSES)],
            'options' => ['required', 'array', 'min:2', 'max:8'],
            'options.*.text' => ['nullable', 'string', 'max:1000'],
            'options.*.correct' => ['nullable', 'boolean'],
        ]);
        // Blank option rows in the form are ignored.
        $data['options'] = array_values(array_filter($data['options'], fn ($o) => trim((string) ($o['text'] ?? '')) !== ''));
        $correct = count(array_filter($data['options'], fn ($o) => ! empty($o['correct'])));
        if (count($data['options']) < 2) {
            throw new RuleViolation(__('A question needs at least two options.'));
        }
        if ($data['kind'] === 'single' ? $correct !== 1 : $correct < 1) {
            throw new RuleViolation($data['kind'] === 'single' ? 'Single-answer questions need exactly one correct option.' : 'Mark at least one correct option.');
        }

        return $data;
    }

    private function fields(array $data): array
    {
        return array_intersect_key($data, array_flip(['kind', 'category', 'difficulty', 'objective', 'prompt', 'explanation', 'review_status']));
    }

    /** Options are replaced as a set; attempts are unaffected because they use snapshots. */
    private function syncOptions(Question $question, array $options): void
    {
        QuestionOption::where('question_id', $question->id)->delete();
        foreach ($options as $i => $o) {
            QuestionOption::create(['question_id' => $question->id, 'position' => $i + 1, 'text' => trim($o['text']), 'is_correct' => ! empty($o['correct'])]);
        }
    }
}
