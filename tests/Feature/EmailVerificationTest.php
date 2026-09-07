<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_sends_verification_email(): void
    {
        Notification::fake();

        $this->post(route('register'), [
            'name' => 'Người dùng mới',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'accept_terms' => '1',
        ])->assertRedirect('/home');

        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertNotNull($user->terms_accepted_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_requires_terms_acceptance(): void
    {
        $this->post(route('register'), [
            'name' => 'Người chưa đồng ý', 'email' => 'no-terms@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('accept_terms');

        $this->assertDatabaseMissing('users', ['email' => 'no-terms@example.com']);
    }

    public function test_google_can_create_verified_user_after_terms_acceptance(): void
    {
        Socialite::fake('google', (new SocialiteUser)->map([
            'id' => 'google-123', 'name' => 'Google User', 'email' => 'google@example.com',
            'user' => ['email_verified' => true],
        ]));

        $this->withSession(['google_terms_accepted' => true])->get(route('auth.google.callback'))
            ->assertRedirect(route('site.home'));

        $user = User::where('email', 'google@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-123', $user->google_id);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertNotNull($user->terms_accepted_at);
    }

    public function test_unverified_user_can_browse_but_cannot_checkout_review_or_learn(): void
    {
        $user = User::factory()->unverified()->create();
        $course = Course::create(['name' => 'Khóa học xác minh', 'price' => 100000, 'status' => Course::STATUS_PUBLISHED]);
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);

        $this->actingAs($user)->get(route('site.home'))->assertOk();
        $this->actingAs($user)->post(route('site.checkout.store'), ['course_ids' => [$course->id], 'accept_terms' => 1])->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->post(route('site.course.review', $course), ['rating' => 5])->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get(route('site.learning.show', $course))->assertRedirect(route('verification.notice'));
    }

    public function test_user_can_verify_email_with_valid_signed_link(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect('/home');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_admin_does_not_require_email_verification_and_is_listed_first(): void
    {
        $admin = User::factory()->unverified()->create(['name' => 'Quản trị viên đầu']);
        $admin->forceFill(['role' => 'admin'])->save();
        $customer = User::factory()->create(['name' => 'Người dùng sau']);
        Order::create([
            'user_id' => $customer->id,
            'code' => 'KHP-PENDING-1',
            'status' => Order::STATUS_PENDING,
            'subtotal' => 100000,
            'total' => 100000,
            'payment_method' => 'bank_transfer',
        ]);
        DB::table('sessions')->insert([
            'id' => 'admin-online-session',
            'user_id' => $admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        $this->assertTrue($admin->fresh()->hasVerifiedEmail());
        $this->actingAs($admin)->get(route('Admin.User', ['sort' => 'name_desc']))
            ->assertOk()
            ->assertSee('Không yêu cầu')
            ->assertSee('Online')
            ->assertSee('Offline')
            ->assertSeeInOrder(['Quản trị viên đầu', 'Người dùng sau']);
        $this->actingAs($admin)->getJson(route('Admin.LiveStatus'))
            ->assertOk()
            ->assertJsonPath('online', 1)
            ->assertJsonPath('offline', 1)
            ->assertJsonPath('pending_orders', 1)
            ->assertJsonPath('online_user_ids.0', $admin->id);
    }

}
