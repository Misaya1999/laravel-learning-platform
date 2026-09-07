<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseReviewController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $hasCourse = Enrollment::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->exists();

        abort_unless($hasCourse, 403, 'Bạn cần đăng ký khóa học trước khi đánh giá.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao.',
            'rating.between' => 'Đánh giá phải từ 1 đến 5 sao.',
            'comment.max' => 'Nhận xét không được vượt quá 1000 ký tự.',
        ]);

        CourseReview::updateOrCreate(
            ['user_id' => $request->user()->id, 'course_id' => $course->id],
            $data,
        );

        return back()->with('review_success', 'Đánh giá của bạn đã được lưu.');
    }
}
