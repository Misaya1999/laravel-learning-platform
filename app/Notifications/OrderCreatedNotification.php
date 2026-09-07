<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification implements ShouldQueue
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
        $courseNames = $order->items->pluck('course_name')->join(', ');

        return (new MailMessage)
            ->subject("Đã tạo đơn hàng {$order->code}")
            ->greeting("Xin chào {$notifiable->name},")
            ->line("Đơn hàng {$order->code} của bạn đã được tạo.")
            ->line("Khóa học: {$courseNames}")
            ->line('Tổng cộng: '.((float) $order->total <= 0 ? 'Miễn phí' : number_format($order->total, 0, ',', '.').'đ'))
            ->line($order->payment_method === 'free' ? 'Khóa học miễn phí đang được kích hoạt cho bạn.' : 'Đơn hàng đang chờ xác nhận thanh toán.')
            ->action('Xem đơn hàng', route('site.orders.show', $order));
    }
}
