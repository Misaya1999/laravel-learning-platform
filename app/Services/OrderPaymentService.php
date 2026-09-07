<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Order;
use App\Notifications\OrderPaidNotification;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    public function confirmPaid(Order $order, ?string $transactionId = null, ?int $confirmedBy = null): Order
    {
        return DB::transaction(function () use ($order, $transactionId, $confirmedBy) {
            $query = Order::whereKey($order->id);
            if (DB::getDriverName() !== 'sqlite') $query->lockForUpdate();
            $lockedOrder = $query->firstOrFail();
            abort_unless($lockedOrder->status === Order::STATUS_PENDING, 422, 'Chỉ đơn đang chờ mới có thể xác nhận thanh toán.');

            $lockedOrder->update(['status' => Order::STATUS_PAID, 'paid_at' => now(), 'transaction_id' => $transactionId, 'confirmed_by' => $confirmedBy]);
            $lockedOrder->load('items.course');
            foreach ($lockedOrder->items as $item) {
                if (! $item->course) continue;
                $enrollment = Enrollment::firstOrNew(['user_id' => $lockedOrder->user_id, 'course_id' => $item->course_id]);
                $now = now();
                $enrollment->fill(['status' => Enrollment::STATUS_ACTIVE, 'enrolled_at' => $now, 'expires_at' => $item->course->calculateExpiresAt($now), 'expiry_reminder_sent_at' => null, 'completed_at' => null])->save();
            }

            $lockedOrder->user?->notify(new OrderPaidNotification($lockedOrder->id));
            return $lockedOrder;
        });
    }
}
