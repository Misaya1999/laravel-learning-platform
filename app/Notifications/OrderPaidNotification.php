<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $orderId) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = Order::with('items')->findOrFail($this->orderId);

        return (new MailMessage)
            ->subject("Thanh toán thành công - {$order->code}")
            ->greeting("Xin chào {$notifiable->name},")
            ->line("Đơn hàng {$order->code} đã được xác nhận thành công.")
            ->line('Các khóa học trong đơn đã được kích hoạt trong tài khoản của bạn.')
            ->action('Bắt đầu học', route('site.my-courses.index'))
            ->line('Cảm ơn bạn đã đồng hành cùng KhoaHocPlus.');
    }
}
