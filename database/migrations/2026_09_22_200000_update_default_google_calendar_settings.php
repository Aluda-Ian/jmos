<?php

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            ['key' => 'google_client_id', 'value' => env('GOOGLE_CLIENT_ID', ''), 'group' => 'google_calendar', 'is_secret' => false],
            ['key' => 'google_client_secret', 'value' => env('GOOGLE_CLIENT_SECRET', ''), 'group' => 'google_calendar', 'is_secret' => true],
            ['key' => 'google_calendar_id', 'value' => env('GOOGLE_CALENDAR_ID', 'jeotamedia@gmail.com'), 'group' => 'google_calendar', 'is_secret' => false],
            ['key' => 'google_sync_enabled', 'value' => '1', 'group' => 'google_calendar', 'is_secret' => false],
        ];

        foreach ($settings as $st) {
            SystemSetting::updateOrCreate(['key' => $st['key']], $st);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive rollback needed
    }
};
