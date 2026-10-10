<?php

namespace App\Providers;

use App\Services\Ai\GeminiSettings;
use App\Services\MeetingReminderService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gemini key / model saved in Settings → AI Assistant override .env values
        GeminiSettings::apply();

        // Meeting reminders also run (at most every 5 minutes) after normal page/API requests,
        // so they still go out on hosting where the Laravel scheduler cron is not set up.
        if (! $this->app->runningInConsole()) {
            $this->app->terminating(fn () => app(MeetingReminderService::class)->sendDueThrottled());
        }
    }
}
