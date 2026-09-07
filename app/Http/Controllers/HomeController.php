<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Mentor;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $ownedCourseIds = $this->ownedCourseIds();
        $course = Course::with(['category', 'mentors'])
            ->withLearningStats()
            ->where('status', Course::STATUS_PUBLISHED)
            ->latest()
            ->limit(3)
            ->get();

        $category = Category::withCount(['courses' => fn ($query) => $query->where('status', Course::STATUS_PUBLISHED)])
            ->orderBy('name')
            ->get();

        $mentorQuery = Mentor::with('certificates')->withCount(['courses' => fn ($query) => $query->where('status', Course::STATUS_PUBLISHED)]);
        $mainMentor = (clone $mentorQuery)->where('is_main', true)->first()
            ?? (clone $mentorQuery)->orderByDesc('courses_count')->oldest()->first();
        $myCourse = collect();

        if (auth()->check()) {
            $myCourse = auth()->user()
                ->courses()
                ->with('category')
                ->withCount(['sections', 'lessons'])
                ->wherePivotIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
                ->orderByPivot('enrolled_at', 'desc')
                ->limit(3)
                ->get();
        }

        return view('Site.Home', compact('course', 'category', 'myCourse', 'ownedCourseIds', 'mainMentor'));
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
