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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('drive_link')->nullable()->after('waiting_on');
            $table->string('brief_link')->nullable()->after('drive_link');
            $table->string('treatment_link')->nullable()->after('brief_link');
            $table->string('playbook_link')->nullable()->after('treatment_link');
            $table->text('notes')->nullable()->after('playbook_link');
            $table->json('files')->nullable()->after('notes');
            $table->json('comments')->nullable()->after('files');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'drive_link',
                'brief_link',
                'treatment_link',
                'playbook_link',
                'notes',
                'files',
                'comments',
            ]);
        });
    }
};
