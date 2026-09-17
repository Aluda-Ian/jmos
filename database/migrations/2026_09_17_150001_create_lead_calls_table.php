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
        Schema::create('lead_calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id')->nullable()->index();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->unsignedBigInteger('deal_id')->nullable()->index();
            $table->string('call_type')->default('Outbound'); // Outbound, Inbound
            $table->string('call_status')->default('Completed'); // Completed, Scheduled, Missed, Cancelled
            $table->string('purpose')->default('Discovery'); // Discovery, Pitch, Follow-up, Negotiation, General
            $table->string('outcome')->nullable(); // Interested, Meeting Scheduled, Follow-up Needed, Left Voicemail, Busy, Not Interested
            $table->integer('duration_minutes')->default(0);
            $table->dateTime('call_time')->nullable();
            $table->string('logged_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_calls');
    }
};
