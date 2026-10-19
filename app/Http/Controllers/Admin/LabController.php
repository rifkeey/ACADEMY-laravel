<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RuleViolation;
use App\Http\Controllers\Controller;
use App\Labs\LabEvaluator;
use App\Models\Lab;
use App\Models\LabCompletion;
use App\Models\Specialization;
use App\Services\AuditLogger;
use App\Services\VersionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Lab definitions. The learner-visible `content` and the server-only `validator` are edited as
 * JSON. Anything that changes grading (tasks, workspace data, answer keys, pass mark, required)
 * is structural and needs a draft version; wording fields can be corrected on a live version.
 */
class LabController extends Controller
{
    private const TASK_TYPES = ['text', 'number', 'choice', 'multi', 'kql', 'firewall', 'grid'];

    public function __construct(private readonly VersionService $versions) {}

    public function index(Request $request): View
    {
        $this->authorize('content.edit');
        $programme = Specialization::where('slug', $request->query('programme'))->first();
        $labs = Lab::with(['courseVersion.specialization:id,title,slug', 'module:id,title'])
            ->when($programme, fn ($q) => $q->whereHas('courseVersion', fn ($v) => $v->where('specialization_id', $programme->id)))
            ->orderBy('course_version_id')->orderBy('position')
            ->paginate(30)->withQueryString();
        $completions = LabCompletion::whereIn('lab_id', $labs->pluck('id'))->groupBy('lab_id')->selectRaw('lab_id, COUNT(*) as n')->pluck('n', 'lab_id');

        return view('admin.labs.index', [
            'labs' => $labs, 'completions' => $completions, 'programme' => $programme,
            'programmes' => Specialization::orderBy('sort_order')->get(['id', 'slug', 'title']),
        ]);
    }

    public function edit(Lab $lab): View
    {
        $this->authorize('content.edit');
        $lab->load('courseVersion.specialization', 'module');
        $lab->makeVisible('validator');   // staff editing view only; never serialised for learners

        return view('admin.labs.edit', ['lab' => $lab]);
    }

    public function update(Request $request, Lab $lab): RedirectResponse
    {
        $this->authorize('content.edit');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'summary' => ['required', 'string', 'max:600'],
            'difficulty' => ['required', Rule::in(['foundation', 'intermediate', 'advanced'])],
            'estimated_minutes' => ['required', 'integer', 'min:5', 'max:600'],
            'pass_percent' => ['required', 'integer', 'min:50', 'max:100'],
            'required' => ['required', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'content' => ['required', 'json', 'max:2000000'],
            'validator' => ['required', 'json', 'max:500000'],
        ]);
        $content = json_decode($data['content'], true);
        $validator = json_decode($data['validator'], true);
        $this->checkDefinition($content, $validator);

        $old = $lab->content;
        $structural = $content['tasks'] !== $old['tasks'] || $content['workspace'] !== $old['workspace']
            || $validator !== $lab->validator || (int) $data['pass_percent'] !== $lab->pass_percent
            || (bool) $data['required'] !== $lab->required || $data['status'] !== $lab->status;
        $this->versions->assertEditable($lab->course_version_id, $structural);

        DB::transaction(function () use ($request, $lab, $data, $content, $validator, $structural) {
            $lab->forceFill([
                'title' => $data['title'], 'summary' => $data['summary'], 'difficulty' => $data['difficulty'],
                'estimated_minutes' => $data['estimated_minutes'], 'pass_percent' => $data['pass_percent'],
                'required' => (bool) $data['required'], 'status' => $data['status'],
                'content' => $content, 'validator' => $validator,
            ])->save();
            AuditLogger::record($request->user()->id, 'lab.updated', 'lab', $lab->id, ['structural' => $structural]);
        });

        return back()->with('success', 'Lab saved.');
    }

    /** Rejects definitions the lab runner or the grader could not use. */
    private function checkDefinition(mixed $content, mixed $validator): void
    {
        if (! is_array($content) || ! is_array($content['tasks'] ?? null) || ! array_is_list($content['tasks']) || ! $content['tasks']
            || ! is_array($content['workspace'] ?? null) || ! in_array($content['workspace']['kind'] ?? null, ['kql', 'evidence', 'documents'], true)) {
            throw new RuleViolation('Content needs a non-empty "tasks" list and a "workspace" of kind kql, evidence or documents.');
        }
        foreach (['scenario', 'instructions'] as $key) {
            if (! is_string($content[$key] ?? null)) {
                throw new RuleViolation("Content needs a \"{$key}\" text.");
            }
        }
        if (! is_array($validator) || ! is_array($validator['tasks'] ?? null)) {
            throw new RuleViolation('The validator needs a "tasks" object keyed by task id.');
        }
        $ids = [];
        foreach ($content['tasks'] as $i => $t) {
            $n = $i + 1;
            if (! is_array($t) || ! is_string($t['id'] ?? null) || ! preg_match('/^[a-z0-9-]{1,60}$/', $t['id']) || ! is_string($t['prompt'] ?? null)
                || ! is_int($t['points'] ?? null) || $t['points'] < 1 || ! in_array($t['input']['type'] ?? null, self::TASK_TYPES, true)) {
                throw new RuleViolation("Task {$n} needs an id (lower-case letters, digits, hyphens), a prompt, whole-number points and a supported input type.");
            }
            if (isset($ids[$t['id']])) {
                throw new RuleViolation("Task id \"{$t['id']}\" is used twice.");
            }
            $ids[$t['id']] = true;
            $v = $validator['tasks'][$t['id']] ?? null;
            if (! is_array($v) || ($v['type'] ?? null) !== $t['input']['type'] || ! is_array($v['feedback'] ?? null)) {
                throw new RuleViolation("The validator entry for task \"{$t['id']}\" is missing, has a different type, or has no feedback.");
            }
            if (str_contains(json_encode($t), '"correct"')) {
                throw new RuleViolation("Task \"{$t['id']}\" appears to contain an answer key; answers belong in the validator only.");
            }
        }
        // A dry run proves the grader can evaluate the definition without errors.
        try {
            LabEvaluator::evaluate($content, $validator, [], 100, (string) config('academy.lab_reference_now'));
        } catch (\Throwable $e) {
            throw new RuleViolation('The grader could not evaluate this definition: '.$e->getMessage());
        }
    }
}
