<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Mentor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MentorTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_can_have_multiple_mentors(): void
    {
        $course = Course::create(['name' => 'Khóa học nhiều mentor']);
        $firstMentor = Mentor::create(['name' => 'Mentor A']);
        $secondMentor = Mentor::create(['name' => 'Mentor B']);

        $course->mentors()->sync([$firstMentor->id, $secondMentor->id]);

        $this->assertCount(2, $course->fresh()->mentors);
        $this->assertTrue($firstMentor->courses()->whereKey($course->id)->exists());
        $this->assertTrue($secondMentor->courses()->whereKey($course->id)->exists());
    }

    public function test_admin_can_upload_certificate_images_for_mentor(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('Admin.Mentor.Store'), [
            'name' => 'Mentor chứng chỉ',
            'certificates' => [UploadedFile::fake()->createWithContent(
                'certificate.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
            )],
        ])->assertRedirect();

        $mentor = Mentor::where('name', 'Mentor chứng chỉ')->with('certificates')->firstOrFail();
        $this->assertCount(1, $mentor->certificates);
        Storage::disk('public')->assertExists($mentor->certificates->first()->image);
    }

    public function test_mentor_directory_displays_all_mentors_with_main_mentor_first(): void
    {
        $otherMentor = Mentor::create(['name' => 'Nguyễn Mentor Phụ', 'specialty' => 'Lập trình PHP']);
        $mainMentor = Mentor::create(['name' => 'Trần Mentor Chính', 'specialty' => 'Kinh tế', 'is_main' => true]);

        $response = $this->get(route('site.mentor.index'));

        $response->assertOk()
            ->assertSeeInOrder([$mainMentor->name, $otherMentor->name])
            ->assertSee('Lập trình PHP')
            ->assertSee(route('site.course.index', ['mentor' => $otherMentor->id]), false);

        $this->get(route('site.mentor.index', ['search' => 'Kinh tế']))
            ->assertOk()
            ->assertSee($mainMentor->name)
            ->assertDontSee($otherMentor->name);
    }

    public function test_home_page_only_shows_main_mentor_and_links_to_mentor_directory(): void
    {
        Mentor::create(['name' => 'Mentor Chỉ Ở Trang Giảng Viên']);
        $mainMentor = Mentor::create(['name' => 'Mentor Nổi Bật Trang Chủ', 'is_main' => true]);

        $this->get(route('site.home'))
            ->assertOk()
            ->assertSee($mainMentor->name)
            ->assertDontSee('Mentor Chỉ Ở Trang Giảng Viên')
            ->assertSee(route('site.mentor.index'), false)
            ->assertDontSee('data-other-mentors', false);
    }
}
