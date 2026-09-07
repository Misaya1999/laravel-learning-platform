<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LearningAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_enrolled_user_with_valid_access_can_open_lesson(): void
    {
        [$course, $lesson] = $this->courseWithLesson();
        $user = User::factory()->create();

        $this->get(route('site.learning.show', [$course, $lesson]))->assertRedirect(route('login'));
        $this->actingAs($user)->get(route('site.learning.show', [$course, $lesson]))->assertForbidden();

        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'expires_at' => now()->addDays(10)]);

        $this->actingAs($user)->get(route('site.learning.show', [$course, $lesson]))
            ->assertOk()->assertSee($lesson->title);
    }

    public function test_expired_enrollment_cannot_open_lesson(): void
    {
        [$course, $lesson] = $this->courseWithLesson();
        $user = User::factory()->create();
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'expires_at' => now()->subDay()]);

        $this->actingAs($user)->get(route('site.learning.show', [$course, $lesson]))->assertForbidden();
    }

    public function test_lesson_from_another_course_returns_not_found(): void
    {
        [$course] = $this->courseWithLesson();
        [, $otherLesson] = $this->courseWithLesson('Khóa khác');
        $user = User::factory()->create();
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);

        $this->actingAs($user)->get(route('site.learning.show', [$course, $otherLesson]))->assertNotFound();
    }

    public function test_authorized_user_can_view_private_attachment_with_a_signed_url(): void
    {
        Storage::fake('local');
        [$course, $lesson] = $this->courseWithLesson();
        $lesson->update([
            'attachment' => 'uploads/lesson/attachments/test.pdf',
            'allow_attachment_download' => true,
        ]);
        Storage::disk('local')->put($lesson->attachment, 'document');
        $user = User::factory()->create();
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);

        $this->actingAs($user)
            ->get(route('site.learning.show', [$course, $lesson]))
            ->assertOk()
            ->assertSee('data-pdf-viewer', false)
            ->assertSee('signature=', false)
            ->assertSee($user->email);

        $signedAttachmentUrl = URL::temporarySignedRoute(
            'site.learning.attachment',
            now()->addMinutes(5),
            [$course, $lesson]
        );

        $this->actingAs($user)
            ->get($signedAttachmentUrl)
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=tai-lieu-bai-' . $lesson->id . '.pdf');

        $this->actingAs($user)
            ->get(route('site.learning.attachment', [$course, $lesson]))
            ->assertForbidden();

        $signedDownloadUrl = URL::temporarySignedRoute(
            'site.learning.attachment',
            now()->addMinutes(5),
            [$course, $lesson, 'download' => 1]
        );
        $this->actingAs($user)->get($signedDownloadUrl)
            ->assertOk()
            ->assertDownload('tai-lieu-bai-' . $lesson->id . '.pdf');

        $lesson->update(['allow_attachment_download' => false]);
        $this->actingAs($user)->get($signedDownloadUrl)->assertForbidden();

    }

    public function test_user_can_complete_and_uncomplete_a_lesson(): void
    {
        [$course, $lesson] = $this->courseWithLesson();
        $user = User::factory()->create();
        $enrollment = Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);

        $this->actingAs($user)->post(route('site.learning.progress', [$course, $lesson]), ['completed' => 1])
            ->assertSessionHas('progress_success');

        $this->assertDatabaseHas('lesson_progress', ['user_id' => $user->id, 'lesson_id' => $lesson->id]);
        $this->assertSame(Enrollment::STATUS_COMPLETED, $enrollment->fresh()->status);

        $this->actingAs($user)->post(route('site.learning.progress', [$course, $lesson]), ['completed' => 0]);

        $this->assertDatabaseMissing('lesson_progress', ['user_id' => $user->id, 'lesson_id' => $lesson->id]);
        $this->assertSame(Enrollment::STATUS_ACTIVE, $enrollment->fresh()->status);
    }

    public function test_admin_can_upload_private_mp4_and_only_enrolled_user_can_stream_it(): void
    {
        Storage::fake('local');
        $course = Course::create(['name' => 'Khóa video']);
        $section = CourseSection::create(['course_id' => $course->id, 'title' => 'Chương video', 'position' => 1]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('Admin.Course.Lesson.Store', $section), [
            'title' => 'Video tải lên',
            'type' => 'video',
            'video_file' => UploadedFile::fake()->create('lesson.mp4', 100, 'video/mp4'),
            'duration_minutes' => 2,
        ])->assertRedirect();

        $lesson = Lesson::where('title', 'Video tải lên')->firstOrFail();
        $this->assertNotNull($lesson->video_path);
        Storage::disk('local')->assertExists($lesson->video_path);

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('site.learning.video', [$course, $lesson]))->assertForbidden();
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);
        $this->actingAs($user)->get(route('site.learning.video', [$course, $lesson]))->assertOk();
    }

    public function test_admin_can_upload_mp4_in_chunks_and_attach_it_to_lesson(): void
    {
        Storage::fake('local');
        $course = Course::create(['name' => 'Khóa chunk video']);
        $section = CourseSection::create(['course_id' => $course->id, 'title' => 'Chương 1', 'position' => 1]);
        $admin = User::factory()->create(['role' => 'admin']);
        $uploadId = 'video-upload-test-1234567890';

        foreach (['abc', 'def'] as $index => $content) {
            $this->actingAs($admin)->post(route('Admin.Course.Lesson.Video.Chunk'), [
                'upload_id' => $uploadId,
                'chunk_index' => $index,
                'total_chunks' => 2,
                'video_chunk' => UploadedFile::fake()->createWithContent('video.part', $content),
            ])->assertOk();
        }

        $this->actingAs($admin)->postJson(route('Admin.Course.Lesson.Video.Complete'), [
            'upload_id' => $uploadId,
            'total_chunks' => 2,
            // FormData của trình duyệt luôn gửi giá trị này dưới dạng chuỗi.
            'expected_size' => '6',
        ])->assertOk()->assertJsonPath('upload_token', $uploadId);

        $this->actingAs($admin)->post(route('Admin.Course.Lesson.Store', $section), [
            'title' => 'Video ghép từ chunk',
            'type' => 'video',
            'video_upload_token' => $uploadId,
            'duration_minutes' => 1,
        ])->assertRedirect();

        $lesson = Lesson::where('title', 'Video ghép từ chunk')->firstOrFail();
        $this->assertSame('abcdef', Storage::disk('local')->get($lesson->video_path));
    }

    public function test_admin_course_detail_contains_initialized_chunk_upload_requirements(): void
    {
        $course = Course::create(['name' => 'Khóa kiểm tra giao diện upload']);
        CourseSection::create(['course_id' => $course->id, 'title' => 'Chương 1', 'position' => 1]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('Admin.Course.Detail', $course))
            ->assertOk()
            ->assertSee('name="csrf-token"', false)
            ->assertSee('lesson-video-upload-config', false)
            ->assertSee('initLessonVideoUpload();', false);
    }

    public function test_chunk_endpoint_accepts_chunk_count_required_by_a_600mb_video(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('Admin.Course.Lesson.Video.Chunk'), [
            'upload_id' => 'large-video-upload-test-1234567890',
            'chunk_index' => 11,
            'total_chunks' => 12,
            'video_chunk' => UploadedFile::fake()->createWithContent('video.part', 'chunk'),
        ])->assertOk()->assertJsonPath('received', 11);
    }

    private function courseWithLesson(string $name = 'Khóa học thử'): array
    {
        $course = Course::create(['name' => $name]);
        $section = CourseSection::create(['course_id' => $course->id, 'title' => 'Chương 1', 'position' => 1]);
        $lesson = Lesson::create(['course_section_id' => $section->id, 'title' => 'Bài học đầu tiên', 'type' => 'article', 'content' => 'Nội dung']);

        return [$course, $lesson];
    }
}
