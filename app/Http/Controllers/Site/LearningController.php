<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\LessonView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LearningController extends Controller
{
    public function show(Request $request, Course $course, ?Lesson $lesson = null): View|RedirectResponse
    {
        $this->ensureCourseAccess($request, $course);
        $course->load('sections.lessons');
        $lessons = $course->sections->flatMap->lessons->values();

        if ($lessons->isEmpty()) {
            return redirect()->route('site.course.show', $course)->with('info', 'Khóa học chưa có bài học.');
        }

        $lesson ??= $lessons->first();
        abort_unless($lessons->contains('id', $lesson->id), 404);
        $currentIndex = $lessons->search(fn ($item) => $item->id === $lesson->id);
        $previousLesson = $currentIndex > 0 ? $lessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $lessons->count() - 1 ? $lessons[$currentIndex + 1] : null;
        $completedLessonIds = LessonProgress::where('user_id', $request->user()->id)
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->pluck('lesson_id');
        $completedCount = $completedLessonIds->count();
        $progressPercentage = $lessons->isEmpty() ? 0 : (int) round(($completedCount / $lessons->count()) * 100);
        $isLessonCompleted = $completedLessonIds->contains($lesson->id);
        $attachmentUrl = $lesson->attachment
            ? URL::temporarySignedRoute('site.learning.attachment', now()->addMinutes(5), [$course, $lesson])
            : null;
        $attachmentDownloadUrl = $lesson->attachment && $lesson->allow_attachment_download
            ? URL::temporarySignedRoute('site.learning.attachment', now()->addMinutes(5), [$course, $lesson, 'download' => 1])
            : null;

        LessonView::updateOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['last_viewed_at' => now()],
        );

        return view('Site.Learning.Show', compact('course', 'lesson', 'previousLesson', 'nextLesson', 'completedLessonIds', 'completedCount', 'progressPercentage', 'isLessonCompleted', 'attachmentUrl', 'attachmentDownloadUrl'));
    }

    public function progress(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $enrollment = $this->ensureCourseAccess($request, $course);
        abort_unless($lesson->section()->where('course_id', $course->id)->exists(), 404);
        $data = $request->validate(['completed' => ['required', 'boolean']]);

        if ($data['completed']) {
            LessonProgress::updateOrCreate(
                ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
                ['completed_at' => now()],
            );
        } else {
            LessonProgress::where('user_id', $request->user()->id)->where('lesson_id', $lesson->id)->delete();
        }

        $lessonIds = $course->lessons()->pluck('lessons.id');
        $completedCount = LessonProgress::where('user_id', $request->user()->id)->whereIn('lesson_id', $lessonIds)->count();
        $allCompleted = $lessonIds->isNotEmpty() && $completedCount === $lessonIds->count();
        $enrollment->update([
            'status' => $allCompleted ? Enrollment::STATUS_COMPLETED : Enrollment::STATUS_ACTIVE,
            'completed_at' => $allCompleted ? now() : null,
        ]);

        return back()->with('progress_success', $data['completed'] ? 'Đã đánh dấu bài học hoàn thành.' : 'Đã bỏ đánh dấu hoàn thành.');
    }

    public function attachment(Request $request, Course $course, Lesson $lesson): StreamedResponse
    {
        $this->ensureCourseAccess($request, $course);
        abort_unless($lesson->section()->where('course_id', $course->id)->exists(), 404);
        abort_unless($lesson->attachment, 404);

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($lesson->attachment)) {
                $extension = strtolower(pathinfo($lesson->attachment, PATHINFO_EXTENSION));
                $fileName = 'tai-lieu-bai-' . $lesson->id . ($extension ? '.' . $extension : '');

                if ($request->boolean('download')) {
                    abort_unless($lesson->allow_attachment_download, 403, 'Tài liệu này không được phép tải xuống.');

                    return Storage::disk($disk)->download($lesson->attachment, $fileName);
                }

                if ($extension === 'pdf') {
                    return Storage::disk($disk)->response(
                        $lesson->attachment,
                        $fileName,
                        [
                            'Content-Type' => 'application/pdf',
                            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
                            'Pragma' => 'no-cache',
                            'X-Content-Type-Options' => 'nosniff',
                        ],
                        'inline'
                    );
                }

                abort(415, 'Định dạng tài liệu này chưa hỗ trợ xem trực tiếp.');
            }
        }
        abort(404);
    }

    public function video(Request $request, Course $course, Lesson $lesson): BinaryFileResponse
    {
        $this->ensureCourseAccess($request, $course);
        abort_unless($lesson->section()->where('course_id', $course->id)->exists(), 404);
        abort_unless($lesson->type === 'video' && $lesson->video_path, 404);
        abort_unless(Storage::disk('local')->exists($lesson->video_path), 404);

        return response()->file(Storage::disk('local')->path($lesson->video_path), [
            'Content-Type' => 'video/mp4',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Content-Disposition' => 'inline; filename="lesson-' . $lesson->id . '.mp4"',
        ]);
    }

    private function ensureCourseAccess(Request $request, Course $course): Enrollment
    {
        $enrollment = Enrollment::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->first();

        abort_unless($enrollment && ! $enrollment->isExpired(), 403, 'Bạn không có quyền truy cập khóa học này hoặc khóa học đã hết hạn.');
        return $enrollment;
    }
}
