<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $allowedStatuses = [Order::STATUS_PENDING, Order::STATUS_PAID, Order::STATUS_CANCELLED, Order::STATUS_REFUNDED];

        $query = $request->user()->orders()
            ->with(['items.course:id,name,image'])
            ->withCount('items')
            ->latest();

        if (in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(10)->withQueryString();
        $counts = $request->user()->orders()
            ->selectRaw('status, COUNT(*) as order_count')
            ->groupBy('status')
            ->pluck('order_count', 'status')
            ->map(fn ($count) => (int) $count);

        return view('Site.Order.Index', compact('orders', 'counts', 'status'));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        $order->load('items.course');

        return view('Site.Checkout.Order', compact('order'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        DB::transaction(function () use ($order) {
            $query = Order::whereKey($order->id);
            if (DB::getDriverName() !== 'sqlite') {
                $query->lockForUpdate();
            }

            $lockedOrder = $query->firstOrFail();
            abort_unless($lockedOrder->status === Order::STATUS_PENDING, 422, 'Chỉ có thể hủy đơn hàng đang chờ thanh toán.');
            $lockedOrder->update(['status' => Order::STATUS_CANCELLED]);
        });

        return redirect()->route('site.orders.index')->with('success', 'Đơn hàng đã được hủy. Bạn có thể đăng ký lại khóa học khi cần.');
    }

    public function uploadReceipt(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        $request->validate([
            'receipt' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'receipt.required' => 'Vui lòng chọn ảnh biên lai.',
            'receipt.image' => 'Biên lai phải là tệp hình ảnh.',
            'receipt.mimes' => 'Biên lai chỉ hỗ trợ JPG, PNG hoặc WEBP.',
            'receipt.max' => 'Ảnh biên lai không được vượt quá 5MB.',
        ]);

        $newPath = $request->file('receipt')->store("order-receipts/{$order->id}", 'local');
        $oldPath = null;
        try {
            DB::transaction(function () use ($order, $newPath, &$oldPath) {
                $query = Order::whereKey($order->id);
                if (DB::getDriverName() !== 'sqlite') $query->lockForUpdate();
                $lockedOrder = $query->firstOrFail();
                abort_unless($lockedOrder->status === Order::STATUS_PENDING, 422, 'Chỉ đơn đang chờ thanh toán mới có thể gửi biên lai.');
                $oldPath = $lockedOrder->receipt_path;
                $lockedOrder->update(['receipt_path' => $newPath, 'receipt_submitted_at' => now()]);
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($newPath);
            throw $exception;
        }
        if ($oldPath && $oldPath !== $newPath) Storage::disk('local')->delete($oldPath);

        return back()->with('success', 'Biên lai đã được gửi. Admin sẽ kiểm tra và xác nhận đơn hàng.');
    }

    public function receipt(Request $request, Order $order): StreamedResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        abort_unless($order->receipt_path && Storage::disk('local')->exists($order->receipt_path), 404);

        return Storage::disk('local')->response($order->receipt_path);
    }

    public function status(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        return response()->json(['status' => $order->status, 'paid_at' => $order->paid_at?->toIso8601String()]);
    }
}
