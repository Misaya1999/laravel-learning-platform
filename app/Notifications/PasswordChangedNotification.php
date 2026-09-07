<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Mật khẩu KhoaHocPlus đã được thay đổi')
            ->greeting('Xin chào ' . $notifiable->name . ',')
            ->line('Mật khẩu tài khoản KhoaHocPlus của bạn vừa được thay đổi thành công.')
            ->line('Các phiên đăng nhập trước đó đã được kết thúc để bảo vệ tài khoản.')
            ->line('Nếu không phải bạn thực hiện, hãy liên hệ quản trị viên ngay.')
            ->action('Đăng nhập tài khoản', route('login'))
            ->salutation('Trân trọng, KhoaHocPlus');
    }
}
