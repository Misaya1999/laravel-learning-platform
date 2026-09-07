<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderPaymentService;
use App\Services\PayOSService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PayOSController extends Controller
{
    public function webhook(Request $request, PayOSService $payOS, OrderPaymentService $payment): JsonResponse
    {
        $payload = $request->all();
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $signature = (string) ($payload['signature'] ?? '');
        if (! $data || ! $signature || ! $payOS->verifyWebhook($data, $signature)) {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        if (! ($payload['success'] ?? false) || ($data['code'] ?? null) !== '00') {
            return response()->json(['success' => true]);
        }

        $order = Order::where('payos_order_code', $data['orderCode'] ?? null)->first();
        if (! $order) return response()->json(['success' => true]); // payOS sends sample data while registering webhook.
        if ((int) round((float) $order->total) !== (int) ($data['amount'] ?? -1)) {
            return response()->json(['success' => false, 'message' => 'Amount mismatch'], 422);
        }
        if ($order->status === Order::STATUS_PENDING) {
            $payment->confirmPaid($order, (string) ($data['reference'] ?? $data['paymentLinkId'] ?? 'PAYOS'));
        }

        return response()->json(['success' => true]);
    }

    public function result(Request $request): RedirectResponse
    {
        $order = Order::where('payos_order_code', $request->integer('orderCode'))
            ->where('user_id', $request->user()->id)->firstOrFail();

        return redirect()->route('site.orders.show', $order)->with('info', 'Giao dịch đang được đối soát tự động. Trang sẽ cập nhật ngay khi ngân hàng xác nhận.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $order = Order::where('payos_order_code', $request->integer('orderCode'))
            ->where('user_id', $request->user()->id)->firstOrFail();

        return redirect()->route('site.orders.show', $order)->with('info', 'Bạn đã đóng trang thanh toán. Đơn hàng vẫn được giữ để có thể thanh toán lại.');
    }

    public function retry(Request $request, Order $order, PayOSService $payOS): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        abort_unless($order->status === Order::STATUS_PENDING && $order->payment_method === 'payos', 422);
        if (! $order->checkout_url) {
            try { $order = $payOS->createPaymentLink($order); }
            catch (\Throwable $exception) { report($exception); return back()->withErrors('Chưa thể kết nối payOS. Vui lòng thử lại sau.'); }
        }

        return redirect()->away($order->checkout_url);
    }

    public function useBankTransfer(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        abort_unless($order->status === Order::STATUS_PENDING && $order->payment_method === 'payos', 422);
        $order->update(['payment_method' => 'bank_transfer']);

        return back()->with('info', 'Đã chuyển sang phương thức chuyển khoản thủ công.');
    }
}
