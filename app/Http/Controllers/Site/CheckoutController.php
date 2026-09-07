<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderPaymentService;
use App\Notifications\OrderCreatedNotification;
use App\Services\PayOSService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $ids = collect($request->input('course_ids', []))->map(fn ($id) => (int) $id)->filter()->unique();
        if ($ids->isEmpty()) return redirect()->route('site.course.index')->with('info', 'Vui lòng chọn ít nhất một khóa học.');

        $courses = Course::whereKey($ids)->where('status', Course::STATUS_PUBLISHED)->get();
        abort_unless($courses->count() === $ids->count(), 404);
        $ownedIds = $this->validOwnedIds($request, $ids);
        $courses = $courses->reject(fn ($course) => $ownedIds->contains($course->id))->values();
        if ($courses->isEmpty()) return redirect()->route('site.my-courses.index')->with('info', 'Bạn đã sở hữu các khóa học đã chọn.');

        return view('Site.Checkout.Show', compact('courses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'course_ids' => ['required', 'array', 'min:1'],
            'course_ids.*' => ['integer', 'distinct', Rule::exists('courses', 'id')->where('status', Course::STATUS_PUBLISHED)],
            'payment_method' => ['nullable', 'in:payos,bank_transfer,free'],
            'accept_terms' => ['accepted'],
        ], [
            'accept_terms.accepted' => 'Bạn cần đồng ý với điều khoản và các chính sách trước khi tiếp tục.',
        ]);
        $ids = collect($data['course_ids'])->map(fn ($id) => (int) $id)->unique();
        $courses = Course::whereKey($ids)->where('status', Course::STATUS_PUBLISHED)->get();
        if ($courses->count() !== $ids->count() || $this->validOwnedIds($request, $ids)->isNotEmpty()) {
            return back()->withErrors('Một hoặc nhiều khóa học không còn hợp lệ để thanh toán.');
        }
        $hasPendingCourse = OrderItem::whereIn('course_id', $ids)
            ->whereHas('order', fn ($query) => $query->where('user_id', $request->user()->id)->where('status', Order::STATUS_PENDING))
            ->exists();
        if ($hasPendingCourse) return back()->withErrors('Bạn đã có đơn hàng đang chờ thanh toán cho một khóa học đã chọn.');

        [$freeOrder, $paidOrder] = DB::transaction(function () use ($request, $courses, $data) {
            $freeCourses = $courses->filter(fn ($course) => (float) $course->price <= 0);
            $paidCourses = $courses->filter(fn ($course) => (float) $course->price > 0);

            return [
                $freeCourses->isNotEmpty() ? $this->createOrder($request, $freeCourses, 'free') : null,
                $paidCourses->isNotEmpty() ? $this->createOrder($request, $paidCourses, $data['payment_method'] ?? 'payos') : null,
            ];
        });

        if ($freeOrder) {
            $request->user()->notify(new OrderCreatedNotification($freeOrder->id));
            $freeOrder = app(OrderPaymentService::class)->confirmPaid($freeOrder);
        }
        if ($paidOrder) {
            $request->user()->notify(new OrderCreatedNotification($paidOrder->id));
            if ($paidOrder->payment_method === 'payos') {
                try {
                    $paidOrder = app(PayOSService::class)->createPaymentLink($paidOrder);
                } catch (\Throwable $exception) {
                    report($exception);
                    return redirect()->route('site.orders.show', $paidOrder)->with('payment_setup_error', 'Chưa thể tạo mã VietQR. Vui lòng thử lại hoặc sử dụng chuyển khoản dự phòng.');
                }
                return redirect()->away($paidOrder->checkout_url);
            }
        }

        $order = $paidOrder ?? $freeOrder;
        return redirect()->route('site.orders.show', $order)->with('order_created', true);
    }

    private function validOwnedIds(Request $request, $ids)
    {
        return Enrollment::where('user_id', $request->user()->id)->whereIn('course_id', $ids)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->pluck('course_id');
    }

    private function createOrder(Request $request, $courses, string $paymentMethod): Order
    {
        $total = $courses->sum(fn ($course) => (float) $course->price);
        do {
            $code = 'KHP-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (Order::where('code', $code)->exists());

        $order = Order::create([
            'user_id' => $request->user()->id,
            'code' => $code,
            'status' => Order::STATUS_PENDING,
            'subtotal' => $total,
            'total' => $total,
            'payment_method' => $paymentMethod,
            'terms_version' => config('business.policy_version'),
            'terms_accepted_at' => now(),
        ]);
        foreach ($courses as $course) {
            $order->items()->create(['course_id' => $course->id, 'course_name' => $course->name, 'price' => $course->price]);
        }

        return $order;
    }
}
