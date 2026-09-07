<?php

namespace App\Notifications;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $enrollmentId) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $enrollment = Enrollment::with('course')->findOrFail($this->enrollmentId);
        $days = $enrollment->remainingDays();

        return (new MailMessage)
            ->subject("Khóa học {$enrollment->course->name} sắp hết hạn")
            ->greeting("Xin chào {$notifiable->name},")
            ->line("Quyền truy cập khóa học “{$enrollment->course->name}” còn {$days} ngày.")
            ->line('Thời hạn đến '. $enrollment->expires_at->format('d/m/Y H:i').'.')
            ->action('Tiếp tục học', route('site.learning.show', $enrollment->course))
            ->line('Hãy hoàn thành các bài học còn lại trước khi khóa học hết hạn.');
    }
}
