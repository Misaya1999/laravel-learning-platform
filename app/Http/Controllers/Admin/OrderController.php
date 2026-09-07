<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Order;
use App\Services\OrderPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with(['user:id,name,email', 'confirmedBy:id,name', 'items.course:id,name']);
        if ($search = trim($request->string('search')->toString())) {
            $query->where(fn ($q) => $q->where('code', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                ->orWhereHas('items', fn ($item) => $item->where('course_name', 'like', "%{$search}%")));
        }
        if (in_array($request->status, [Order::STATUS_PENDING, Order::STATUS_PAID, Order::STATUS_CANCELLED, Order::STATUS_REFUNDED], true)) $query->where('status', $request->status);
        $sort = in_array($request->sort, ['latest', 'oldest', 'total_desc', 'total_asc'], true) ? $request->sort : 'latest';
        match ($sort) { 'oldest' => $query->oldest(), 'total_desc' => $query->orderByDesc('total'), 'total_asc' => $query->orderBy('total'), default => $query->latest() };
        $orders = $query->paginate(15)->withQueryString();
        return view('Admin.Order', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order, OrderPaymentService $paymentService): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:paid,cancelled,refunded'], 'transaction_id' => ['nullable', 'string', 'max:150', 'unique:orders,transaction_id']]);

        if ($data['status'] === Order::STATUS_PAID) {
            $paymentService->confirmPaid($order, $data['transaction_id'] ?? null, $request->user()->id);
            return back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
        }

        DB::transaction(function () use ($order, $data) {
            $orderQuery = Order::whereKey($order->id);
            if (DB::getDriverName() !== 'sqlite') {
                $orderQuery->lockForUpdate();
            }
            $lockedOrder = $orderQuery->firstOrFail();
            if ($data['status'] === Order::STATUS_CANCELLED) {
                abort_unless($lockedOrder->status === Order::STATUS_PENDING, 422, 'Chỉ đơn đang chờ mới có thể hủy.');
                $lockedOrder->update(['status' => Order::STATUS_CANCELLED]);
            } else {
                abort_unless($lockedOrder->status === Order::STATUS_PAID, 422, 'Chỉ đơn đã thanh toán mới có thể hoàn tiền.');
                $lockedOrder->update(['status' => Order::STATUS_REFUNDED]);
                Enrollment::where('user_id', $lockedOrder->user_id)->whereIn('course_id', $lockedOrder->items()->pluck('course_id'))->update(['status' => Enrollment::STATUS_CANCELLED]);
            }
        });

        return back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }

    public function receipt(Order $order): StreamedResponse
    {
        abort_unless($order->receipt_path && Storage::disk('local')->exists($order->receipt_path), 404);

        return Storage::disk('local')->response($order->receipt_path);
    }
}
