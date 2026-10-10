<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Attendee emails and the 1-day / 1-hour reminder tracking for calendar events.
     */
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->json('attendee_emails')->nullable()->after('attendees');
            $table->timestamp('reminder_day_sent_at')->nullable();
            $table->timestamp('reminder_hour_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn(['attendee_emails', 'reminder_day_sent_at', 'reminder_hour_sent_at']);
        });
    }
};
