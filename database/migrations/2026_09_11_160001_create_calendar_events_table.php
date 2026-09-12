<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type', 50)->default('meeting'); // meeting, shoot, deadline, task, other
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('location')->nullable();
            $table->text('attendees')->nullable();
            $table->string('google_event_id')->nullable();
            $table->string('status', 30)->default('confirmed');
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
