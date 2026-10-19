<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\RuleViolation;
use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\SourceReference;
use App\Services\AuditLogger;
use App\Services\VersionService;
use App\Support\Lines;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function __construct(private readonly VersionService $versions) {}

    public function store(Request $request, CourseModule $module): RedirectResponse
    {
        $this->authorize('content.edit');
        $this->versions->assertEditable($module->course_version_id, structural: true);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('lessons')->where('course_module_id', $module->id)],
        ]);

        $lesson = DB::transaction(function () use ($request, $module, $data) {
            $position = (int) Lesson::where('course_module_id', $module->id)->lockForUpdate()->max('position') + 1;
            $lesson = Lesson::create($data + [
                'course_module_id' => $module->id, 'position' => $position, 'objectives' => [],
                'body_md' => '', 'knowledge_checks' => [], 'required' => true, 'status' => 'draft',
            ]);
            AuditLogger::record($request->user()->id, 'lesson.created', 'lesson', $lesson->id, ['module' => $module->id, 'title' => $lesson->title]);

            return $lesson;
        });

        return redirect()->route('admin.lessons.edit', $lesson)->with('success', 'Lesson created as a draft.');
    }

    public function edit(Lesson $lesson): View
    {
        $this->authorize('content.edit');
        $lesson->load(['module.courseVersion.specialization', 'sources:id']);

        return view('admin.lessons.edit', [
            'lesson' => $lesson,
            'version' => $lesson->module->courseVersion,
            'sources' => SourceReference::orderBy('publisher')->orderBy('title')->get(['id', 'title', 'publisher']),
        ]);
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->authorize('content.edit');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'objectives' => ['nullable', 'string', 'max:6000'],
            'body_md' => ['required', 'string', 'max:200000'],
            'estimated_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'knowledge_checks' => ['nullable', 'json', 'max:50000'],
            'required' => ['required', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'sources' => ['nullable', 'array', 'max:30'],
            'sources.*' => ['integer', 'exists:source_references,id'],
        ]);
        $checks = $this->validateChecks($data['knowledge_checks'] ?? '[]');

        // Whether a lesson is required or visible changes completion rules: draft versions only.
        $structural = (bool) $data['required'] !== (bool) $lesson->required || $data['status'] !== $lesson->status;
        $this->versions->assertEditable($lesson->module->course_version_id, $structural);

        DB::transaction(function () use ($request, $lesson, $data, $checks) {
            $lesson->forceFill([
                'title' => $data['title'], 'objectives' => Lines::parse($data['objectives'] ?? ''), 'body_md' => $data['body_md'],
                'estimated_minutes' => $data['estimated_minutes'], 'knowledge_checks' => $checks,
                'required' => (bool) $data['required'], 'status' => $data['status'],
            ])->save();
            $lesson->sources()->sync($data['sources'] ?? []);
            AuditLogger::record($request->user()->id, 'lesson.updated', 'lesson', $lesson->id, ['fields' => array_keys($lesson->getChanges())]);
        });

        return back()->with('success', 'Lesson saved.');
    }

    /**
     * Knowledge checks are edited as JSON: [{"question", "options": [...], "answerIndex", "explanation"}].
     *
     * @return list<array>
     */
    private function validateChecks(string $json): array
    {
        $checks = json_decode($json, true);
        if (! is_array($checks) || ! array_is_list($checks) || count($checks) > 20) {
            throw new RuleViolation('Knowledge checks must be a JSON list of at most 20 items.');
        }
        foreach ($checks as $i => $c) {
            $n = $i + 1;
            $ok = is_array($c) && is_string($c['question'] ?? null) && trim($c['question']) !== ''
                && is_array($c['options'] ?? null) && array_is_list($c['options']) && count($c['options']) >= 2 && count($c['options']) <= 6
                && count(array_filter($c['options'], 'is_string')) === count($c['options'])
                && is_int($c['answerIndex'] ?? null) && $c['answerIndex'] >= 0 && $c['answerIndex'] < count($c['options'])
                && is_string($c['explanation'] ?? '');
            if (! $ok) {
                throw new RuleViolation("Knowledge check {$n} needs a question, 2–6 text options, a valid answerIndex and an explanation.");
            }
        }

        return array_map(fn ($c) => ['question' => $c['question'], 'options' => $c['options'], 'answerIndex' => $c['answerIndex'], 'explanation' => $c['explanation'] ?? ''], $checks);
    }
}
