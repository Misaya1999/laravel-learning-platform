<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\LessonView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        $enrollments = $user->enrollments()
            ->with('course.category')
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->latest('enrolled_at')
            ->get();
        $courseIds = $enrollments->pluck('course_id');
        $totalLessonCount = Course::whereIn('id', $courseIds)
            ->withCount('lessons')
            ->get()
            ->sum('lessons_count');
        $completedLessonCount = LessonProgress::where('user_id', $user->id)
            ->whereHas('lesson.section', fn ($query) => $query->whereIn('course_id', $courseIds))
            ->count();
        $overallProgress = $totalLessonCount > 0
            ? (int) round($completedLessonCount / $totalLessonCount * 100)
            : 0;
        $recentLessonView = LessonView::with('lesson.section.course.category')
            ->where('user_id', $user->id)
            ->whereHas('lesson.section', fn ($query) => $query->whereIn('course_id', $courseIds))
            ->latest('last_viewed_at')
            ->first();
        $recentCourseId = $recentLessonView?->lesson?->section?->course_id;
        $suggestedEnrollment = $enrollments
            ->first(fn ($enrollment) => $enrollment->course_id === $recentCourseId && ! $enrollment->isExpired())
            ?? $enrollments->first(fn ($enrollment) => ! $enrollment->isExpired());

        return view('Site.Profile.Edit', [
            'user' => $user,
            'totalLessonCount' => $totalLessonCount,
            'completedLessonCount' => $completedLessonCount,
            'overallProgress' => $overallProgress,
            'recentLessonView' => $recentLessonView,
            'suggestedEnrollment' => $suggestedEnrollment,
        ]);
    }

    public function update(UserRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')
                ->store('uploads/user/avatar', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Thông tin tài khoản đã được cập nhật.');
    }
}
