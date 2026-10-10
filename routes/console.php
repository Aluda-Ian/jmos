<?php

use App\Services\MeetingReminderService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('jmos:meeting-reminders', function (MeetingReminderService $reminders) {
    $sent = $reminders->sendDue();
    $this->info("Meeting reminders sent — 1 day: {$sent['day']}, 1 hour: {$sent['hour']}");
})->purpose('Email attendees 1 day and 1 hour before calendar events');

Schedule::command('jmos:meeting-reminders')->everyFiveMinutes()->withoutOverlapping();
