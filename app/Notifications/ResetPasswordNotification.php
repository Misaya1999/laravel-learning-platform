<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(private readonly string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
        $expires = config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Đặt lại mật khẩu KhoaHocPlus')
            ->greeting('Xin chào ' . $notifiable->name . ',')
            ->line('Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản KhoaHocPlus của bạn.')
            ->action('Đặt lại mật khẩu', $url)
            ->line('Liên kết này có hiệu lực trong ' . $expires . ' phút và chỉ sử dụng một lần.')
            ->line('Nếu bạn không yêu cầu đổi mật khẩu, hãy bỏ qua email này.')
            ->salutation('Trân trọng, KhoaHocPlus');
    }
}
