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
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'assigned_to_id')) {
                $table->foreignId('assigned_to_id')->nullable()->after('project_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('tasks', 'assigned_by_id')) {
                $table->foreignId('assigned_by_id')->nullable()->after('assigned_to_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'assigned_by_id')) {
                $table->dropConstrainedForeignId('assigned_by_id');
            }
            if (Schema::hasColumn('tasks', 'assigned_to_id')) {
                $table->dropConstrainedForeignId('assigned_to_id');
            }
        });
    }
};
