<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_uses_vietnamese_book_interface(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Quên mật khẩu?')
            ->assertSee('Gửi liên kết đặt lại')
            ->assertSee('auth-recovery-book', false);
    }

    public function test_reset_email_uses_generic_response_and_sends_vietnamese_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status', 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu.');
        Notification::assertSentTo($user, ResetPasswordNotification::class);

        $this->post(route('password.email'), ['email' => 'khong-ton-tai@example.com'])
            ->assertSessionHas('status', 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu.');
    }

    public function test_user_can_reset_password_and_existing_sessions_are_revoked(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => 'old-password']);
        $token = Password::broker()->createToken($user);
        DB::table('sessions')->insert([
            'id' => Str::random(40),
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => base64_encode('test'),
            'last_activity' => now()->timestamp,
        ]);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập bằng mật khẩu mới.');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
        Notification::assertSentTo($user, PasswordChangedNotification::class);
        $this->assertGuest();
    }

    public function test_admin_can_send_password_reset_link_to_user(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('Admin.User.PasswordReset', $user))
            ->assertSessionHas('success', 'Đã gửi liên kết đặt lại mật khẩu tới ' . $user->email . '.');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_invalid_reset_token_uses_vietnamese_error(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasErrors([
            'email' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.',
        ]);
    }
}
