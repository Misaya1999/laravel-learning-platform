<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_published_announcement(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)->post(route('Admin.Announcement.Store'), [
            'title' => 'Khóa học mới',
            'content' => 'Khóa học mới đã sẵn sàng.',
            'type' => 'info',
            'is_published' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('announcements', ['title' => 'Khóa học mới', 'is_published' => true]);
    }

    public function test_user_sees_unread_announcement_and_can_mark_it_read(): void
    {
        $user = User::factory()->create();
        $announcement = Announcement::create([
            'title' => 'Thông báo dành cho bạn',
            'content' => 'Nội dung thông báo.',
            'type' => 'success',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($user)->get(route('site.home'))
            ->assertOk()
            ->assertSee('Thông báo dành cho bạn');

        $this->actingAs($user)->patch(route('site.announcements.read', $announcement))
            ->assertRedirect();

        $this->assertDatabaseHas('announcement_reads', [
            'announcement_id' => $announcement->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_draft_announcement_is_not_visible_or_readable(): void
    {
        $user = User::factory()->create();
        $announcement = Announcement::create([
            'title' => 'Bản nháp bí mật',
            'content' => 'Không được hiển thị.',
            'type' => 'warning',
            'is_published' => false,
        ]);

        $this->actingAs($user)->get(route('site.home'))->assertDontSee('Bản nháp bí mật');
        $this->actingAs($user)->patch(route('site.announcements.read', $announcement))->assertNotFound();
    }
}
