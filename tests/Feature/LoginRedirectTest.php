<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_is_redirected_to_site_even_when_admin_url_was_intended(): void
    {
        $user = User::factory()->create(['password' => 'password', 'status' => 'active']);

        $this->withSession(['url.intended' => route('Admin.Dashboard')])
            ->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('site.home'));
    }

    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['password' => 'password', 'status' => 'active']);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->post(route('login'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('Admin.Dashboard'));
    }

    public function test_invalid_login_uses_vietnamese_error_message(): void
    {
        $user = User::factory()->create(['password' => 'password', 'status' => 'active']);

        $this->from(route('login'))->post(route('login'), [
            'email' => $user->email, 'password' => 'wrong-password',
        ])->assertRedirect(route('login'))->assertSessionHasErrors([
            'email' => 'Email hoặc mật khẩu không chính xác.',
        ]);

        $this->get(route('login'))->assertOk()
            ->assertSee('Đăng nhập không thành công')
            ->assertSee('Email hoặc mật khẩu không chính xác.');
    }
}
