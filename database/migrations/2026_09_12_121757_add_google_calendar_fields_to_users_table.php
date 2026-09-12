<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_calendar_email')->nullable()->after('initials');
            $table->string('google_calendar_status')->nullable()->default('disconnected')->after('google_calendar_email');
            $table->timestamp('google_calendar_synced_at')->nullable()->after('google_calendar_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_calendar_email',
                'google_calendar_status',
                'google_calendar_synced_at',
            ]);
        });
    }
};
