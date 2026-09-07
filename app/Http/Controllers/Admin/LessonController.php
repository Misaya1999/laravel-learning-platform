<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseSection;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LessonController extends Controller
{
    public function store(Request $request, CourseSection $section) 
    {
        $data = $this->validateLesson($request);

        $position = $section->lessons()
            ->max('position');

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request
                ->file('attachment')
                ->store(
                    'uploads/lesson/attachments',
                    'local'
                );
        }

        if ($videoPath = $this->storeUploadedVideo($request)) {
            $data['video_path'] = $videoPath;
            $data['video_url'] = null;
        }
        unset($data['video_file'], $data['video_upload_token']);

        $data = $this->normalizeLessonData($request, $data);
        $data['position'] = ($position ?? 0) + 1;

        $section->lessons()->create($data);

        return back()->with(
            'success',
            'Thêm bài học thành công.'
        );
    }

    public function update(Request $request, Lesson $lesson)
    {
        $data = $this->validateLesson($request, $lesson);

        if ($request->hasFile('attachment')) {
            if ($lesson->attachment) {
                Storage::disk('local')->delete($lesson->attachment);
                Storage::disk('public')->delete($lesson->attachment);
            }

            $data['attachment'] = $request
                ->file('attachment')
                ->store('uploads/lesson/attachments', 'local');
        }

        if ($newVideoPath = $this->storeUploadedVideo($request)) {
            if ($lesson->video_path) Storage::disk('local')->delete($lesson->video_path);
            $data['video_path'] = $newVideoPath;
            $data['video_url'] = null;
        } elseif ($request->filled('video_url') && $lesson->video_path) {
            Storage::disk('local')->delete($lesson->video_path);
            $data['video_path'] = null;
        } elseif ($data['type'] !== 'video' && $lesson->video_path) {
            Storage::disk('local')->delete($lesson->video_path);
            $data['video_path'] = null;
        }
        unset($data['video_file'], $data['video_upload_token']);

        $data = $this->normalizeLessonData($request, $data);
        $lesson->update($data);

        return back()->with('success', 'Cập nhật bài học thành công.');
    }

    public function destroy(Lesson $lesson)
    {
        if ($lesson->attachment) {
            Storage::disk('local')->delete($lesson->attachment);
            Storage::disk('public')->delete($lesson->attachment);
        }

        if ($lesson->video_path) Storage::disk('local')->delete($lesson->video_path);

        $lesson->delete();

        return back()->with('success', 'Xóa bài học thành công.');
    }

    public function attachment(Lesson $lesson): StreamedResponse
    {
        abort_unless($lesson->attachment, 404);

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($lesson->attachment)) {
                return Storage::disk($disk)->download($lesson->attachment);
            }
        }

        abort(404);
    }

    private function validateLesson(Request $request, ?Lesson $lesson = null): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,article,assignment',
            'video_url' => 'nullable|url',
            'video_file' => 'nullable|file|mimes:mp4|max:614400',
            'video_upload_token' => ['nullable', 'regex:/^[a-zA-Z0-9-]{20,80}$/'],
            'content' => 'nullable|string|required_if:type,article,assignment',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
            'allow_attachment_download' => ['nullable', 'boolean'],
            'duration_minutes' => 'nullable|integer|min:1|max:10000',
            'position' => 'nullable|integer|min:1',
        ]);

        if ($data['type'] === 'video'
            && ! $request->filled('video_url')
            && ! $request->hasFile('video_file')
            && ! $request->filled('video_upload_token')
            && ! $lesson?->video_path) {
            throw ValidationException::withMessages([
                'video_file' => 'Vui lòng tải lên file MP4 hoặc nhập URL video.',
            ]);
        }

        if ($request->filled('video_upload_token')) {
            $temporaryPath = $this->temporaryVideoPath($request, $request->string('video_upload_token'));
            if (! Storage::disk('local')->exists($temporaryPath)) {
                throw ValidationException::withMessages(['video_file' => 'Video tải lên chưa hoàn tất hoặc đã hết hạn.']);
            }
        }

        return $data;
    }

    private function storeUploadedVideo(Request $request): ?string
    {
        if ($request->hasFile('video_file')) {
            return $request->file('video_file')->store('uploads/lesson/videos', 'local');
        }
        if (! $request->filled('video_upload_token')) return null;

        $token = (string) $request->string('video_upload_token');
        $temporaryPath = $this->temporaryVideoPath($request, $token);
        $finalPath = 'uploads/lesson/videos/' . Str::uuid() . '.mp4';
        abort_unless(Storage::disk('local')->move($temporaryPath, $finalPath), 500, 'Không thể lưu video đã tải lên.');
        Storage::disk('local')->deleteDirectory('tmp/video-uploads/' . $request->user()->id . '/' . $token);

        return $finalPath;
    }

    private function temporaryVideoPath(Request $request, string $token): string
    {
        return 'tmp/video-uploads/' . $request->user()->id . '/' . $token . '/complete.mp4';
    }

    private function normalizeLessonData(Request $request, array $data): array
    {
        $data['is_preview'] = $request->boolean('is_preview');
        $data['allow_attachment_download'] = $request->boolean('allow_attachment_download');

        if ($data['type'] === 'video') {
            $data['content'] = null;
        } else {
            $data['video_url'] = null;
            $data['video_path'] = null;
            $data['duration_minutes'] = null;
        }

        return $data;
    }
}
