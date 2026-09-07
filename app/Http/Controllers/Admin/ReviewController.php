<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = CourseReview::with(['user:id,name,email,avatar', 'course:id,name']);

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($review) use ($search) {
                $review->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('course', fn ($course) => $course->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('rating') && in_array($request->integer('rating'), range(1, 5), true)) {
            $query->where('rating', $request->integer('rating'));
        }
        if ($request->visibility === 'visible') $query->where('is_visible', true);
        if ($request->visibility === 'hidden') $query->where('is_visible', false);

        match ($request->sort) {
            'oldest' => $query->oldest(),
            'rating_desc' => $query->orderByDesc('rating')->latest(),
            'rating_asc' => $query->orderBy('rating')->latest(),
            default => $query->latest(),
        };

        $reviews = $query->paginate(15)->withQueryString();
        $summary = [
            'all' => CourseReview::count(),
            'visible' => CourseReview::visible()->count(),
            'hidden' => CourseReview::where('is_visible', false)->count(),
        ];

        return view('Admin.Review', compact('reviews', 'summary'));
    }

    public function toggleVisibility(CourseReview $review): RedirectResponse
    {
        $review->update(['is_visible' => ! $review->is_visible]);

        return back()->with('success', $review->is_visible ? 'Đánh giá đã được hiển thị lại.' : 'Đánh giá đã được ẩn khỏi website.');
    }

    public function destroy(CourseReview $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'Đánh giá đã được xóa vĩnh viễn.');
    }
}
