<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use App\Notifications\CourseExpiringNotification;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderPaidNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_pending_order_without_enrollment(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $course = $this->publishedCourse();

        $response = $this->actingAs($user)->post(route('site.checkout.store'), ['course_ids' => [$course->id], 'payment_method' => 'bank_transfer', 'accept_terms' => 1]);
        $order = Order::firstOrFail();

        $response->assertRedirect(route('site.orders.show', $order));
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertSame(500000.0, (float) $order->total);
        $this->assertSame(config('business.policy_version'), $order->terms_version);
        $this->assertNotNull($order->terms_accepted_at);
        $this->assertDatabaseMissing('enrollments', ['user_id' => $user->id, 'course_id' => $course->id]);
        Notification::assertSentTo($user, OrderCreatedNotification::class);
    }

    public function test_checkout_requires_policy_acceptance(): void
    {
        $user = User::factory()->create();
        $course = $this->publishedCourse();

        $this->actingAs($user)->post(route('site.checkout.store'), [
            'course_ids' => [$course->id],
            'payment_method' => 'bank_transfer',
        ])->assertSessionHasErrors('accept_terms');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_payos_checkout_creates_payment_link_and_redirects_to_vietqr(): void
    {
        Notification::fake();
        $this->configurePayOS();
        Http::fake([
            'https://api-merchant.payos.vn/v2/payment-requests' => Http::response([
                'code' => '00',
                'desc' => 'success',
                'data' => ['paymentLinkId' => 'pay-link-1', 'checkoutUrl' => 'https://pay.payos.vn/web/payment/pay-link-1', 'qrCode' => '000201010212'],
            ]),
        ]);
        $user = User::factory()->create();
        $course = $this->publishedCourse();

        $response = $this->actingAs($user)->post(route('site.checkout.store'), [
            'course_ids' => [$course->id], 'payment_method' => 'payos', 'accept_terms' => 1,
        ]);
        $order = Order::firstOrFail();

        $response->assertRedirect('https://pay.payos.vn/web/payment/pay-link-1');
        $this->assertSame('payos', $order->payment_method);
        $this->assertSame($order->id, (int) $order->payos_order_code);
        $this->assertSame('pay-link-1', $order->payment_link_id);
        Http::assertSent(fn ($request) => $request->hasHeader('x-client-id', 'client-id')
            && $request['amount'] === 500000
            && $request['orderCode'] === $order->id
            && filled($request['signature']));
    }

    public function test_valid_payos_webhook_pays_order_and_is_idempotent(): void
    {
        Notification::fake();
        $this->configurePayOS();
        [$user, $course, $order] = $this->pendingOrder();
        $order->update(['payment_method' => 'payos', 'payos_order_code' => $order->id, 'payment_link_id' => 'pay-link-2']);
        $data = ['orderCode' => $order->id, 'amount' => 500000, 'code' => '00', 'reference' => 'FT260819001', 'paymentLinkId' => 'pay-link-2'];
        $payload = ['success' => true, 'data' => $data, 'signature' => $this->payOSSignature($data)];

        $this->postJson(route('site.payos.webhook'), $payload)->assertOk()->assertJson(['success' => true]);
        $this->postJson(route('site.payos.webhook'), $payload)->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => Order::STATUS_PAID, 'transaction_id' => 'FT260819001']);
        $this->assertDatabaseHas('enrollments', ['user_id' => $user->id, 'course_id' => $course->id, 'status' => Enrollment::STATUS_ACTIVE]);
        Notification::assertSentToTimes($user, OrderPaidNotification::class, 1);
    }

    public function test_payos_webhook_rejects_invalid_signature_and_wrong_amount(): void
    {
        $this->configurePayOS();
        [$user, $course, $order] = $this->pendingOrder();
        $order->update(['payment_method' => 'payos', 'payos_order_code' => $order->id]);

        $this->postJson(route('site.payos.webhook'), ['success' => true, 'data' => ['orderCode' => $order->id, 'amount' => 500000, 'code' => '00'], 'signature' => 'invalid'])
            ->assertBadRequest();
        $wrongAmount = ['orderCode' => $order->id, 'amount' => 1, 'code' => '00'];
        $this->postJson(route('site.payos.webhook'), ['success' => true, 'data' => $wrongAmount, 'signature' => $this->payOSSignature($wrongAmount)])
            ->assertStatus(422);

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertDatabaseMissing('enrollments', ['user_id' => $user->id, 'course_id' => $course->id]);
    }

    public function test_admin_confirming_paid_order_creates_enrollment(): void
    {
        Notification::fake();
        [$user, $course, $order] = $this->pendingOrder();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)->patch(route('Admin.Order.Status', $order), ['status' => 'paid', 'transaction_id' => 'BANK-001'])->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid', 'transaction_id' => 'BANK-001']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'confirmed_by' => $admin->id]);
        $this->assertDatabaseHas('enrollments', ['user_id' => $user->id, 'course_id' => $course->id, 'status' => Enrollment::STATUS_ACTIVE]);
        Notification::assertSentTo($user, OrderPaidNotification::class);
    }

    public function test_free_course_skips_payment_and_is_enrolled_immediately(): void
    {
        $user = User::factory()->create();
        $course = Course::create(['name' => 'Khóa học miễn phí', 'price' => 0, 'status' => Course::STATUS_PUBLISHED]);

        $response = $this->actingAs($user)->post(route('site.checkout.store'), ['course_ids' => [$course->id], 'accept_terms' => 1]);
        $order = Order::firstOrFail();

        $response->assertRedirect(route('site.orders.show', $order));
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => Order::STATUS_PAID, 'payment_method' => 'free', 'total' => 0]);
        $this->assertDatabaseHas('enrollments', ['user_id' => $user->id, 'course_id' => $course->id, 'status' => Enrollment::STATUS_ACTIVE]);
    }

    public function test_mixed_checkout_activates_free_course_and_only_charges_paid_course(): void
    {
        $user = User::factory()->create();
        $freeCourse = Course::create(['name' => 'Miễn phí', 'price' => 0, 'status' => Course::STATUS_PUBLISHED]);
        $paidCourse = $this->publishedCourse();

        $this->actingAs($user)->post(route('site.checkout.store'), [
            'course_ids' => [$freeCourse->id, $paidCourse->id],
            'payment_method' => 'bank_transfer',
            'accept_terms' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('enrollments', ['user_id' => $user->id, 'course_id' => $freeCourse->id, 'status' => Enrollment::STATUS_ACTIVE]);
        $this->assertDatabaseMissing('enrollments', ['user_id' => $user->id, 'course_id' => $paidCourse->id]);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'status' => Order::STATUS_PAID, 'payment_method' => 'free', 'total' => 0]);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'status' => Order::STATUS_PENDING, 'payment_method' => 'bank_transfer', 'total' => 500000]);
    }

    public function test_duplicate_pending_order_for_same_course_is_rejected(): void
    {
        [$user, $course] = $this->pendingOrder();

        $this->actingAs($user)->from(route('site.checkout.show', ['course_ids' => [$course->id]]))
            ->post(route('site.checkout.store'), ['course_ids' => [$course->id], 'payment_method' => 'bank_transfer', 'accept_terms' => 1])
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_refund_removes_course_access_and_revenue_status(): void
    {
        [$user, $course, $order] = $this->pendingOrder();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin)->patch(route('Admin.Order.Status', $order), ['status' => 'paid'])->assertSessionHas('success');
        $this->actingAs($admin)->patch(route('Admin.Order.Status', $order), ['status' => 'refunded'])->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'refunded']);
        $this->assertDatabaseHas('enrollments', ['user_id' => $user->id, 'course_id' => $course->id, 'status' => Enrollment::STATUS_CANCELLED]);
    }

    public function test_guest_cannot_checkout_or_view_my_courses(): void
    {
        $course = $this->publishedCourse();
        $this->post(route('site.checkout.store'), ['course_ids' => [$course->id], 'payment_method' => 'bank_transfer'])->assertRedirect(route('login'));
        $this->get(route('site.my-courses.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_only_see_their_own_order_history_and_details(): void
    {
        [$user, $course, $order] = $this->pendingOrder();
        $otherUser = User::factory()->create();

        $this->actingAs($user)->get(route('site.orders.index'))
            ->assertOk()
            ->assertSee($order->code)
            ->assertSee($course->name);

        $this->actingAs($otherUser)->get(route('site.orders.index'))->assertOk()->assertDontSee($order->code);
        $this->actingAs($otherUser)->get(route('site.orders.show', $order))->assertNotFound();
    }

    public function test_user_can_cancel_their_pending_order_and_checkout_again(): void
    {
        [$user, $course, $order] = $this->pendingOrder();

        $this->actingAs($user)->patch(route('site.orders.cancel', $order))
            ->assertRedirect(route('site.orders.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => Order::STATUS_CANCELLED]);
        $this->actingAs($user)->post(route('site.checkout.store'), [
            'course_ids' => [$course->id],
            'payment_method' => 'bank_transfer',
            'accept_terms' => 1,
        ])->assertRedirect();
        $this->assertDatabaseCount('orders', 2);
    }

    public function test_user_cannot_cancel_another_users_order_or_a_paid_order(): void
    {
        [$user, $course, $order] = $this->pendingOrder();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)->patch(route('site.orders.cancel', $order))->assertNotFound();
        $order->update(['status' => Order::STATUS_PAID, 'paid_at' => now()]);
        $this->actingAs($user)->patch(route('site.orders.cancel', $order))->assertStatus(422);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => Order::STATUS_PAID]);
    }

    public function test_expiry_reminder_is_sent_only_once_for_an_expiring_course(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $course = $this->publishedCourse();
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => Enrollment::STATUS_ACTIVE,
            'enrolled_at' => now()->subMonth(),
            'expires_at' => now()->addDays(5),
        ]);

        $this->artisan('courses:send-expiry-reminders')->assertSuccessful();
        $this->artisan('courses:send-expiry-reminders')->assertSuccessful();

        Notification::assertSentToTimes($user, CourseExpiringNotification::class, 1);
        $this->assertNotNull($enrollment->fresh()->expiry_reminder_sent_at);
    }

    public function test_user_can_upload_private_receipt_and_admin_is_notified(): void
    {
        Storage::fake('local');
        [$user, $course, $order] = $this->pendingOrder();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($user)->post(route('site.orders.receipt.store', $order), [
            'receipt' => UploadedFile::fake()->createWithContent('receipt.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')),
        ])->assertSessionHas('success');

        $order->refresh();
        $this->assertNotNull($order->receipt_submitted_at);
        Storage::disk('local')->assertExists($order->receipt_path);
        $this->actingAs(User::factory()->create())->get(route('site.orders.receipt.show', $order))->assertNotFound();
        $this->actingAs($admin)->get(route('Admin.Order.Receipt', $order))->assertOk();
        $this->actingAs($admin)->getJson(route('Admin.LiveStatus'))->assertJsonPath('submitted_receipts', 1);
    }

    public function test_expired_pending_orders_are_cancelled_except_orders_with_receipts(): void
    {
        [$user, $course, $expiredOrder] = $this->pendingOrder();
        $expiredOrder->forceFill(['created_at' => now()->subHours(49)])->save();
        $protectedOrder = Order::create([
            'user_id' => $user->id,
            'code' => 'KHP-RECEIPT-PROTECTED',
            'status' => Order::STATUS_PENDING,
            'subtotal' => 100000,
            'total' => 100000,
            'payment_method' => 'bank_transfer',
            'receipt_path' => 'order-receipts/protected.jpg',
            'receipt_submitted_at' => now()->subHours(48),
        ]);
        $protectedOrder->forceFill(['created_at' => now()->subHours(72)])->save();

        $this->artisan('orders:cancel-expired-pending', ['--hours' => 48])->assertSuccessful();

        $this->assertSame(Order::STATUS_CANCELLED, $expiredOrder->fresh()->status);
        $this->assertSame(Order::STATUS_PENDING, $protectedOrder->fresh()->status);
    }

    private function pendingOrder(): array
    {
        $user = User::factory()->create();
        $course = $this->publishedCourse();
        $this->actingAs($user)->post(route('site.checkout.store'), ['course_ids' => [$course->id], 'payment_method' => 'bank_transfer', 'accept_terms' => 1]);
        return [$user, $course, Order::firstOrFail()];
    }

    private function publishedCourse(): Course
    {
        return Course::create(['name' => 'Khóa học thanh toán', 'price' => 500000, 'status' => Course::STATUS_PUBLISHED]);
    }

    private function configurePayOS(): void
    {
        Config::set('payment.payos', [
            'client_id' => 'client-id', 'api_key' => 'api-key', 'checksum_key' => 'checksum-secret',
            'api_url' => 'https://api-merchant.payos.vn',
        ]);
    }

    private function payOSSignature(array $data): string
    {
        ksort($data);
        return hash_hmac('sha256', collect($data)->map(fn ($value, $key) => $key.'='.$value)->implode('&'), 'checksum-secret');
    }
}
