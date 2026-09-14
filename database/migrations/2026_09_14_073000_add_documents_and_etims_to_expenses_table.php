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
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('etims_number')->nullable()->after('etr');
            $table->string('receipt_url')->nullable()->after('etims_number');
            $table->string('receipt_name')->nullable()->after('receipt_url');
            $table->text('notes')->nullable()->after('receipt_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['etims_number', 'receipt_url', 'receipt_name', 'notes']);
        });
    }
};
