<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\CatalogueService;
use App\Services\LearningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Course overview and the lesson player. Access always comes from the learner's own enrolment. */
class LearnController extends Controller
{
    public function __construct(private readonly LearningService $learning) {}

    public function course(Request $request, string $slug, CatalogueService $catalogue): View|RedirectResponse
    {
        $course = $this->learning->learnerCourse($request->user(), $slug);
        if (! $course) {
            abort_unless($catalogue->getProgramme($slug), 404);

            return redirect(lroute('programmes.show', $slug))->with('warning', __('Enrol in this programme to access its lessons.'));
        }

        return view('learner.course', $course + ['slug' => $slug]);
    }

    public function lesson(Request $request, string $slug, int $lesson): View
    {
        return view('learner.lesson', $this->learning->lessonForLearner($request->user(), $slug, $lesson) + ['slug' => $slug]);
    }

    public function complete(Request $request, string $slug, Lesson $lesson): RedirectResponse
    {
        $this->learning->markLessonComplete($request->user(), $lesson);
        $next = $request->integer('next');

        return $next
            ? redirect()->route('learn.lesson', [$slug, $next])
            : redirect()->route('learn.lesson', [$slug, $lesson->id])->with('success', __('Lesson marked as complete.'));
    }
}
