<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use App\Models\CourseVersion;
use App\Services\AuditLogger;
use App\Services\VersionService;
use App\Support\Lines;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Modules of a course version. Adding, reordering and changing status are structural (draft
 * versions only); wording can be corrected on a live version.
 */
class ModuleController extends Controller
{
    public function __construct(private readonly VersionService $versions) {}

    public function store(Request $request, CourseVersion $version): RedirectResponse
    {
        $this->authorize('content.edit');
        $this->versions->assertEditable($version->id, structural: true);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('course_modules')->where('course_version_id', $version->id)],
        ]);

        DB::transaction(function () use ($request, $version, $data) {
            $position = (int) CourseModule::where('course_version_id', $version->id)->lockForUpdate()->max('position') + 1;
            $m = CourseModule::create($data + [
                'course_version_id' => $version->id, 'position' => $position,
                'objectives' => [], 'key_concepts' => [], 'assessment_criteria' => [],
                'content_status' => 'outline', 'status' => 'draft',
            ]);
            AuditLogger::record($request->user()->id, 'module.created', 'course_module', $m->id, ['version' => $version->id, 'title' => $m->title]);
        });

        return back()->with('success', 'Module added as a draft.');
    }

    public function update(Request $request, CourseModule $module): RedirectResponse
    {
        $this->authorize('content.edit');
        $this->versions->assertEditable($module->course_version_id, structural: false);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'purpose' => ['nullable', 'string', 'max:4000'],
            'prerequisite_knowledge' => ['nullable', 'string', 'max:4000'],
            'objectives' => ['nullable', 'string', 'max:6000'],
            'key_concepts' => ['nullable', 'string', 'max:6000'],
            'assessment_criteria' => ['nullable', 'string', 'max:6000'],
            'instructor_notes' => ['nullable', 'string', 'max:6000'],
            'content_status' => ['required', Rule::in(['complete', 'starter', 'outline'])],
            'review_date' => ['nullable', 'date'],
        ]);
        foreach (['objectives', 'key_concepts', 'assessment_criteria'] as $list) {
            $data[$list] = Lines::parse($data[$list] ?? '');
        }

        DB::transaction(function () use ($request, $module, $data) {
            $module->forceFill($data)->save();
            AuditLogger::record($request->user()->id, 'module.updated', 'course_module', $module->id, ['fields' => array_keys($module->getChanges())]);
        });

        return back()->with('success', 'Module saved.');
    }

    /** Swaps a module with its neighbour. */
    public function move(Request $request, CourseModule $module): RedirectResponse
    {
        $this->authorize('content.edit');
        $this->versions->assertEditable($module->course_version_id, structural: true);
        $dir = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        DB::transaction(function () use ($request, $module, $dir) {
            $siblings = CourseModule::where('course_version_id', $module->course_version_id)->orderBy('position')->lockForUpdate()->get();
            $i = $siblings->search(fn ($m) => $m->id === $module->id);
            $j = $dir === 'up' ? $i - 1 : $i + 1;
            if ($i === false || ! isset($siblings[$j])) {
                return;
            }
            [$a, $b] = [$siblings[$i], $siblings[$j]];
            [$pa, $pb] = [$a->position, $b->position];
            $a->forceFill(['position' => $pb])->save();
            $b->forceFill(['position' => $pa])->save();
            AuditLogger::record($request->user()->id, 'module.moved', 'course_module', $module->id, ['direction' => $dir]);
        });

        return back();
    }

    public function status(Request $request, CourseModule $module): RedirectResponse
    {
        $this->authorize('content.edit');
        $this->versions->assertEditable($module->course_version_id, structural: true);
        $data = $request->validate(['status' => ['required', Rule::in(['draft', 'published', 'archived'])]]);

        DB::transaction(function () use ($request, $module, $data) {
            $module->forceFill($data)->save();
            AuditLogger::record($request->user()->id, 'module.status', 'course_module', $module->id, $data);
        });

        return back()->with('success', "Module is now {$data['status']}.");
    }
}
