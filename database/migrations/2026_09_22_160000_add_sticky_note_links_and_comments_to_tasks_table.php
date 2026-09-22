<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (! Schema::hasColumn('tasks', 'sticky_color')) {
                $table->string('sticky_color')->nullable()->after('assigned_color');
            }
            if (! Schema::hasColumn('tasks', 'links')) {
                $table->json('links')->nullable()->after('sticky_color');
            }
            if (! Schema::hasColumn('tasks', 'comments')) {
                $table->json('comments')->nullable()->after('links');
            }
            if (! Schema::hasColumn('tasks', 'priority')) {
                $table->string('priority')->default('medium')->after('stage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $cols = ['description', 'sticky_color', 'links', 'comments', 'priority'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('tasks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
