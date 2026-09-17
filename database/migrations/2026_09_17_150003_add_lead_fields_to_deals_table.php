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
        Schema::table('deals', function (Blueprint $table) {
            $table->unsignedBigInteger('lead_id')->nullable()->after('id')->index();
            $table->unsignedBigInteger('client_id')->nullable()->after('lead_id')->index();
            $table->string('contact_person')->nullable()->after('client_name');
            $table->string('deal_owner')->nullable()->after('contact_person');
            $table->date('expected_close_date')->nullable()->after('value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn(['lead_id', 'client_id', 'contact_person', 'deal_owner', 'expected_close_date']);
        });
    }
};
