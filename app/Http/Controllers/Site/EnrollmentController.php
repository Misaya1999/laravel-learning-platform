<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\LessonView;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $enrollmentQuery = $request->user()
            ->enrollments()
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED]);

        $activeCourseCount = (clone $enrollmentQuery)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->count();

        $expiredCourseCount = (clone $enrollmentQuery)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->count();

        $allCourseIds = (clone $enrollmentQuery)->pluck('course_id');

        $course = $request->user()
            ->courses()
            ->with('category')
            ->withCount(['sections', 'lessons'])
            ->withSum('lessons', 'duration_minutes')
            ->wherePivotIn('status', [
                Enrollment::STATUS_ACTIVE,
                Enrollment::STATUS_COMPLETED,
            ])
            ->orderByPivot('enrolled_at', 'desc')
            ->paginate(9);

        $courseIds = $course->getCollection()->pluck('id');
        $completedByCourse = LessonProgress::query()
            ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
            ->join('course_sections', 'lessons.course_section_id', '=', 'course_sections.id')
            ->where('lesson_progress.user_id', $request->user()->id)
            ->whereIn('course_sections.course_id', $courseIds)
            ->selectRaw('course_sections.course_id, COUNT(*) as completed_count')
            ->groupBy('course_sections.course_id')
            ->pluck('completed_count', 'course_sections.course_id');
        $allRecentViews = LessonView::with('lesson.section.course')
            ->where('user_id', $request->user()->id)
            ->whereHas('lesson.section', fn ($query) => $query->whereIn('course_id', $allCourseIds))
            ->latest('last_viewed_at')
            ->get();
        $recentViewsByCourse = $allRecentViews
            ->groupBy(fn ($view) => $view->lesson->section->course_id)
            ->map->first();

        $course->getCollection()->each(function ($courseItem) use ($completedByCourse, $recentViewsByCourse) {
            $courseItem->completed_lessons_count = (int) ($completedByCourse[$courseItem->id] ?? 0);
            $courseItem->progress_percentage = $courseItem->lessons_count > 0
                ? (int) round($courseItem->completed_lessons_count / $courseItem->lessons_count * 100)
                : 0;
            $courseItem->recent_lesson_view = $recentViewsByCourse->get($courseItem->id);
        });

        $totalLessonCount = \App\Models\Course::whereIn('id', $allCourseIds)
            ->withCount('lessons')
            ->get()
            ->sum('lessons_count');
        $completedLessonCount = LessonProgress::where('user_id', $request->user()->id)
            ->whereHas('lesson.section', fn ($query) => $query->whereIn('course_id', $allCourseIds))
            ->count();
        $overallProgress = $totalLessonCount > 0
            ? (int) round($completedLessonCount / $totalLessonCount * 100)
            : 0;
        $recentLessonView = $allRecentViews->first();

        return view('Site.MyCourse.Index', compact(
            'course',
            'activeCourseCount',
            'expiredCourseCount',
            'totalLessonCount',
            'completedLessonCount',
            'overallProgress',
            'recentLessonView',
        ));
    }

}
