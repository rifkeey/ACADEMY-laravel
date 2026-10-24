<?php

namespace App\Services;

use App\Exceptions\RuleViolation;
use App\Models\CourseVersion;
use App\Models\Exam;
use App\Models\Lab;
use App\Models\Lesson;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Course versioning. Learners stay on the version they enrolled in, so their progress, lab
 * completions and certificates always refer to the content they actually studied.
 *
 * - Published versions accept text edits only (titles, bodies, objectives, explanations).
 * - Structural changes (adding/removing lessons or labs, changing what is required) are made in
 *   a draft version cloned from the published one, then published.
 */
final class VersionService
{
    public function assertEditable(int $versionId, bool $structural): CourseVersion
    {
        $v = CourseVersion::findOrFail($versionId);
        if ($v->status === 'retired') {
            throw new RuleViolation(__('Retired versions are read-only.'));
        }
        if ($structural && $v->status === 'published') {
            throw new RuleViolation(__('Structural changes require a draft version. Create a new draft from this version first.'));
        }

        return $v;
    }

    public function createDraft(User $actor, Specialization $spec): CourseVersion
    {
        return DB::transaction(function () use ($actor, $spec) {
            $spec = Specialization::whereKey($spec->id)->lockForUpdate()->first();
            if (! $spec->current_version_id) {
                throw new RuleViolation(__('Programme has no published version to copy.'));
            }
            if ($existing = CourseVersion::where('specialization_id', $spec->id)->where('status', 'draft')->first()) {
                throw new RuleViolation(__('Draft version :version already exists. Publish or edit it instead.', ['version' => $existing->version]));
            }
            $current = CourseVersion::findOrFail($spec->current_version_id);
            $draft = CourseVersion::create([
                'specialization_id' => $spec->id,
                'version' => (int) CourseVersion::where('specialization_id', $spec->id)->max('version') + 1,
                'status' => 'draft',
            ]);

            // Copy modules and lessons (with their sources), then labs and the exam settings, each
            // with its translations: the copies start with the same English, so they stay current.
            $translations = app(TranslationService::class);
            $moduleMap = [];
            foreach ($current->modules()->get() as $m) {
                $copy = $m->replicate(['course_version_id']);
                $copy->course_version_id = $draft->id;
                $copy->save();
                $moduleMap[$m->id] = $copy->id;
                $translations->copy('module', $m->id, $copy->id);
                foreach (Lesson::where('course_module_id', $m->id)->with('sources')->get() as $l) {
                    $lc = $l->replicate(['course_module_id']);
                    $lc->course_module_id = $copy->id;
                    $lc->save();
                    $lc->sources()->sync($l->sources->pluck('id'));
                    $translations->copy('lesson', $l->id, $lc->id);
                }
            }
            foreach (Lab::where('course_version_id', $current->id)->get() as $lab) {
                $copy = $lab->replicate(['course_version_id', 'course_module_id']);
                $copy->course_version_id = $draft->id;
                $copy->course_module_id = $lab->course_module_id ? ($moduleMap[$lab->course_module_id] ?? null) : null;
                $copy->save();
                $translations->copy('lab', $lab->id, $copy->id);
            }
            if ($exam = Exam::where('course_version_id', $current->id)->first()) {
                $copy = $exam->replicate(['course_version_id']);
                $copy->course_version_id = $draft->id;
                $copy->config_version = 1;
                $copy->save();
                $translations->copy('exam', $exam->id, $copy->id);
            }
            AuditLogger::record($actor->id, 'version.draft_created', 'course_version', $draft->id, ['programme' => $spec->slug, 'version' => $draft->version]);

            return $draft;
        });
    }

    public function publish(User $actor, CourseVersion $version, string $changelog): void
    {
        DB::transaction(function () use ($actor, $version, $changelog) {
            $v = CourseVersion::whereKey($version->id)->lockForUpdate()->first();
            if ($v->status !== 'draft') {
                throw new RuleViolation(__('Only draft versions can be published.'));
            }
            CourseVersion::where('specialization_id', $v->specialization_id)->where('status', 'published')->update(['status' => 'retired']);
            $v->forceFill(['status' => 'published', 'published_at' => now(), 'changelog' => $changelog])->save();
            Specialization::whereKey($v->specialization_id)->update(['current_version_id' => $v->id]);
            AuditLogger::record($actor->id, 'version.published', 'course_version', $v->id, ['version' => $v->version, 'changelog' => $changelog]);
        });
    }
}
