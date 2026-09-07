<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Notifications\CourseExpiringNotification;
use Illuminate\Console\Command;

class SendCourseExpiryReminders extends Command
{
    protected $signature = 'courses:send-expiry-reminders {--days=7 : Số ngày cần nhắc trước khi hết hạn}';
    protected $description = 'Gửi email nhắc người học về các khóa học sắp hết hạn';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $sent = 0;

        Enrollment::query()
            ->with(['user', 'course'])
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->whereNull('expiry_reminder_sent_at')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays($days))
            ->chunkById(100, function ($enrollments) use (&$sent) {
                foreach ($enrollments as $enrollment) {
                    if (! $enrollment->user || ! $enrollment->course) continue;
                    $enrollment->user->notify(new CourseExpiringNotification($enrollment->id));
                    $enrollment->update(['expiry_reminder_sent_at' => now()]);
                    $sent++;
                }
            });

        $this->info("Đã xếp hàng {$sent} email nhắc hết hạn.");
        return self::SUCCESS;
    }
}
