<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_profile(): void
    {
        $this->get(route('site.profile.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('site.profile.edit'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($user->email);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('site.profile.update'), [
                'name' => 'Nguyen Van A',
                'phone' => '0901234567',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nguyen Van A',
            'email' => $user->email,
            'phone' => '0901234567',
        ]);
    }

    public function test_user_cannot_change_account_email_from_profile_request(): void
    {
        $user = User::factory()->create(['email' => 'fixed@example.com']);

        $this->actingAs($user)->put(route('site.profile.update'), [
            'name' => $user->name,
            'email' => 'attacker-change@example.com',
        ])->assertSessionHasErrors('email');

        $this->assertSame('fixed@example.com', $user->fresh()->email);
    }
}
