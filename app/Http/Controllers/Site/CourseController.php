<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $ownedCourseIds = $this->ownedCourseIds();
        $category = Category::withCount(['courses' => fn ($query) => $query->where('status', Course::STATUS_PUBLISHED)])->orderBy('name')->get();
        $selectedCategory = $request->filled('category')
            ? $category->firstWhere('id', $request->integer('category'))
            : null;
        $sort = in_array($request->string('sort')->toString(), ['latest', 'rating', 'students', 'price_asc', 'price_desc'], true)
            ? $request->string('sort')->toString()
            : 'latest';

        $course = Course::with(['category', 'mentors'])
            ->withLearningStats()
            ->where('status', Course::STATUS_PUBLISHED)
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->string('search')->trim() . '%');
            })
            ->when($request->filled('mentor'), function ($query) use ($request) {
                $query->whereHas('mentors', fn ($mentor) => $mentor->whereKey($request->integer('mentor')));
            })
            ->tap(function ($query) use ($sort) {
                match ($sort) {
                    'rating' => $query->orderByDesc('reviews_avg_rating')->latest('courses.id'),
                    'students' => $query->orderByDesc('students_count')->latest('courses.id'),
                    'price_asc' => $query->orderBy('price')->latest('courses.id'),
                    'price_desc' => $query->orderByDesc('price')->latest('courses.id'),
                    default => $query->latest('courses.id'),
                };
            })
            ->paginate(9)
            ->withQueryString();

        return view('Site.Course.Index', compact('course', 'category', 'selectedCategory', 'sort', 'ownedCourseIds'));
    }

    public function show(Course $course)
    {
        $enrollment = auth()->check()
            ? Enrollment::where('user_id', auth()->id())->where('course_id', $course->id)->first()
            : null;
        $ownsCourse = $enrollment
            && in_array($enrollment->status, [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED], true);
        abort_unless($course->status === Course::STATUS_PUBLISHED || ($course->status === Course::STATUS_HIDDEN && $ownsCourse), 404);

        $course->load(['category', 'mentors.courses.category', 'sections.lessons']);
        $course->loadCount([
            'sections',
            'lessons',
            'reviews as reviews_count' => fn ($query) => $query->visible(),
            'enrollments as students_count' => fn ($query) => $query->whereIn('status', [
                Enrollment::STATUS_ACTIVE,
                Enrollment::STATUS_COMPLETED,
            ]),
        ]);
        $course->loadSum('lessons', 'duration_minutes');
        $course->loadAvg(['reviews as reviews_avg_rating' => fn ($query) => $query->visible()], 'rating');

        $relatedCourse = Course::with(['category', 'mentors'])
            ->withLearningStats()
            ->where('status', Course::STATUS_PUBLISHED)
            ->whereKeyNot($course->id)
            ->when($course->category_id, fn ($query) => $query->where('category_id', $course->category_id))
            ->latest()
            ->limit(3)
            ->get();
        $ownedCourseIds = $this->ownedCourseIds();

        $isExpired = $enrollment?->isExpired() ?? false;
        $isEnrolled = $enrollment
            && in_array($enrollment->status, [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED], true)
            && ! $isExpired;

        $canReview = $enrollment
            && in_array($enrollment->status, [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED], true);
        $currentReview = auth()->check()
            ? $course->reviews()->where('user_id', auth()->id())->first()
            : null;
        $reviews = $course->reviews()
            ->visible()
            ->with('user:id,name,avatar')
            ->latest()
            ->limit(10)
            ->get();
        $previewLesson = $course->sections
            ->flatMap->lessons
            ->first(fn ($lesson) => $lesson->is_preview && $lesson->type === 'video' && $lesson->videoEmbedUrl());

        return view('Site.Course.Show', compact(
            'course',
            'relatedCourse',
            'enrollment',
            'isEnrolled',
            'isExpired',
            'canReview',
            'currentReview',
            'reviews',
            'previewLesson',
            'ownedCourseIds',
        ));
    }

    private function ownedCourseIds()
    {
        if (! auth()->check()) {
            return collect();
        }

        return Enrollment::where('user_id', auth()->id())
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->pluck('course_id');
    }
}
