<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class CancelExpiredPendingOrders extends Command
{
    protected $signature = 'orders:cancel-expired-pending {--hours= : Số giờ chờ trước khi tự hủy}';
    protected $description = 'Tự hủy đơn chờ quá hạn chưa gửi biên lai';

    public function handle(): int
    {
        $hours = max(1, (int) ($this->option('hours') ?: config('payment.pending_expiration_hours', 48)));
        $cancelled = Order::where('status', Order::STATUS_PENDING)
            ->whereNull('receipt_path')
            ->where('created_at', '<=', now()->subHours($hours))
            ->update(['status' => Order::STATUS_CANCELLED, 'updated_at' => now()]);

        $this->info("Đã tự hủy {$cancelled} đơn chờ quá {$hours} giờ chưa có biên lai.");
        return self::SUCCESS;
    }
}
