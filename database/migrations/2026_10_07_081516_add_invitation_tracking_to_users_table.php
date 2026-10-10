<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track when a team member was invited and when they activated their account.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('invitation_sent_at')->nullable()->after('email_verified_at');
            $table->timestamp('activated_at')->nullable()->after('invitation_sent_at');
            $table->timestamp('last_login_at')->nullable()->after('activated_at');
        });

        // Anyone who has already signed in (has an API token) is treated as active.
        $signedIn = DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\\Models\\User')
            ->select('tokenable_id', DB::raw('MIN(created_at) as first_login'), DB::raw('MAX(COALESCE(last_used_at, created_at)) as last_login'))
            ->groupBy('tokenable_id')
            ->get();

        foreach ($signedIn as $row) {
            DB::table('users')->where('id', $row->tokenable_id)->update([
                'activated_at' => $row->first_login,
                'last_login_at' => $row->last_login,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['invitation_sent_at', 'activated_at', 'last_login_at']);
        });
    }
};
