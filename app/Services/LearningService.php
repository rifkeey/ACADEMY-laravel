<?php

namespace App\Services;

use App\Models\BadgeAward;
use App\Models\Enrolment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Lab;
use App\Models\LabCompletion;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Enrolment access, course state and progress.
 *
 * computeRequirements() is the single source of truth for "is this learner done?", used by
 * exam eligibility and certificate issuance alike. Every method takes the user explicitly and
 * scopes all queries to that user, so one learner can never read another learner's records.
 */
final class LearningService
{
    public function activeEnrolment(User $user, string $slug): ?Enrolment
    {
        return Enrolment::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('specialization', fn ($q) => $q->where('slug', $slug))
            ->with('specialization')
            ->first();
    }

    /**
     * @return array{lessonsTotal: int, lessonsDone: int, labsTotal: int, labsPassed: int,
     *               examPassed: bool, examPublished: bool, examEligible: bool, complete: bool}
     */
    public function computeRequirements(Enrolment $enrolment): array
    {
        $requiredLessons = Lesson::where('required', true)->where('status', 'published')
            ->whereHas('module', fn ($q) => $q->where('course_version_id', $enrolment->course_version_id)->where('status', 'published'))
            ->pluck('id');
        $lessonsDone = $requiredLessons->isEmpty() ? 0 : LessonProgress::where('user_id', $enrolment->user_id)->whereIn('lesson_id', $requiredLessons)->count();

        $requiredLabs = Lab::where('course_version_id', $enrolment->course_version_id)->where('required', true)->where('status', 'published')->pluck('id');
        $labsPassed = $requiredLabs->isEmpty() ? 0 : LabCompletion::where('user_id', $enrolment->user_id)->whereIn('lab_id', $requiredLabs)->count();

        $exam = Exam::where('course_version_id', $enrolment->course_version_id)->first();
        $examPassed = $exam !== null && ExamAttempt::where('enrolment_id', $enrolment->id)->where('exam_id', $exam->id)->where('passed', true)->exists();

        $lessonsOk = $requiredLessons->isNotEmpty() && $lessonsDone === $requiredLessons->count();
        $labsOk = $labsPassed === $requiredLabs->count();
        $examPublished = $exam?->status === 'published';

        return [
            'lessonsTotal' => $requiredLessons->count(),
            'lessonsDone' => $lessonsDone,
            'labsTotal' => $requiredLabs->count(),
            'labsPassed' => $labsPassed,
            'examPassed' => $examPassed,
            'examPublished' => $examPublished,
            'examEligible' => $examPublished && $lessonsOk && $labsOk,
            'complete' => $examPublished && $lessonsOk && $labsOk && $examPassed,
        ];
    }

    /** Everything the course overview needs, for the version the learner enrolled in. */
    public function learnerCourse(User $user, string $slug): ?array
    {
        $enrolment = $this->activeEnrolment($user, $slug);
        if (! $enrolment) {
            return null;
        }
        $version = $enrolment->courseVersion()->first();
        $modules = $version->modules()->where('status', 'published')->orderBy('position')
            ->with([
                'lessons' => fn ($q) => $q->where('status', 'published')->orderBy('position')->select('id', 'course_module_id', 'slug', 'title', 'estimated_minutes', 'position', 'required'),
                'labs' => fn ($q) => $q->where('status', 'published')->orderBy('position')->select('id', 'course_module_id', 'slug', 'title', 'summary', 'difficulty', 'estimated_minutes', 'required'),
            ])->get();

        // Titles and summaries in the learner's language where an approved translation exists.
        $translations = app(TranslationService::class);
        $translations->localise($enrolment->specialization);
        $translations->localiseLoaded($modules);
        $translations->localiseLoaded($modules->flatMap->lessons);
        $translations->localiseLoaded($modules->flatMap->labs);

        $done = LessonProgress::where('user_id', $user->id)->pluck('lesson_id')->flip();
        $passed = LabCompletion::where('user_id', $user->id)->pluck('lab_id')->flip();
        foreach ($modules as $m) {
            $m->lessons->each(fn ($l) => $l->setAttribute('done', $done->has($l->id)));
            $m->labs->each(fn ($l) => $l->setAttribute('passed', $passed->has($l->id)));
        }
        $ordered = $modules->flatMap->lessons->values();

        return [
            'spec' => $enrolment->specialization,
            'enrolment' => $enrolment,
            'version' => $version,
            'modules' => $modules,
            'labs' => $modules->flatMap->labs->values(),
            'requirements' => $this->computeRequirements($enrolment),
            'certificate' => $enrolment->certificate()->first(),
            'orderedLessons' => $ordered,
            'nextLesson' => $ordered->first(fn ($l) => ! $l->done),
        ];
    }

    /** A lesson the learner is entitled to, with previous/next navigation. */
    public function lessonForLearner(User $user, string $slug, int $lessonId): array
    {
        $course = $this->learnerCourse($user, $slug);
        if (! $course) {
            throw new AuthorizationException('You are not enrolled in this programme.');
        }
        $index = $course['orderedLessons']->search(fn ($l) => $l->id === $lessonId);
        if ($index === false) {
            throw (new ModelNotFoundException)->setModel(Lesson::class);
        }
        $summary = $course['orderedLessons'][$index];
        $lesson = Lesson::with('sources')->findOrFail($summary->id);
        app(TranslationService::class)->localise($lesson);   // full check: approved, complete and current

        return [
            'course' => $course,
            'module' => $course['modules']->firstWhere('id', $lesson->course_module_id),
            'lesson' => $lesson,
            'done' => $summary->done,
            'prev' => $course['orderedLessons'][$index - 1] ?? null,
            'next' => $course['orderedLessons'][$index + 1] ?? null,
        ];
    }

    /** Idempotent: completing a lesson twice keeps the original completion time. */
    public function markLessonComplete(User $user, Lesson $lesson): void
    {
        $enrolment = Enrolment::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('course_version_id', $lesson->module()->value('course_version_id'))
            ->first();
        if (! $enrolment || $lesson->status !== 'published') {
            throw new AuthorizationException('You are not enrolled in the course version that contains this lesson.');
        }
        LessonProgress::insertOrIgnore([
            'user_id' => $user->id, 'lesson_id' => $lesson->id, 'enrolment_id' => $enrolment->id, 'completed_at' => now(),
        ]);
    }

    /** Dashboard summary across all of a learner's enrolments. */
    public function dashboard(User $user): array
    {
        $programmes = Enrolment::where('user_id', $user->id)->where('status', 'active')->with('specialization')->latest('enrolled_at')->get()
            ->map(function (Enrolment $e) use ($user) {
                $course = $this->learnerCourse($user, $e->specialization->slug);

                // The course's copy of the programme carries the learner's language.
                return ['spec' => $course['spec'], 'enrolment' => $e, 'requirements' => $course['requirements'], 'nextLesson' => $course['nextLesson'], 'certificate' => $course['certificate']];
            });

        return [
            'programmes' => $programmes,
            'badges' => $user->hasMany(BadgeAward::class)->with('badge')->get(),
            'certificates' => $user->certificates()->latest('issued_at')->get(),
            'attempts' => ExamAttempt::where('user_id', $user->id)->with('exam:id,title')->latest('started_at')->limit(10)->get(),
        ];
    }
}
