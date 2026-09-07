<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminLiveStatusService
{
    public function snapshot(): array
    {
        $onlineIds = DB::table('sessions')
            ->join('users', 'users.id', '=', 'sessions.user_id')
            ->where('sessions.last_activity', '>=', now()->subMinutes(5)->timestamp)
            ->distinct()
            ->pluck('sessions.user_id')
            ->map(fn ($id) => (int) $id)
            ->values();
        $total = User::count();

        return [
            'online' => $onlineIds->count(),
            'offline' => max(0, $total - $onlineIds->count()),
            'online_user_ids' => $onlineIds,
            'pending_orders' => Order::where('status', Order::STATUS_PENDING)->count(),
            'submitted_receipts' => Order::where('status', Order::STATUS_PENDING)->whereNotNull('receipt_path')->count(),
        ];
    }
}
