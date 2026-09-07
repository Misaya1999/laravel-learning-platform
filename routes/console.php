<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('courses:send-expiry-reminders')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('orders:cancel-expired-pending')->hourly()->withoutOverlapping();
