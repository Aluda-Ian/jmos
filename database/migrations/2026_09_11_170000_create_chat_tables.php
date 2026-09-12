<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_threads', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('direct'); // 'group' or 'direct'
            $table->string('title')->nullable(); // channel name (e.g. #general, #production)
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('chat_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('thread_id');
            $table->unsignedBigInteger('user_id');
            $table->dateTime('last_read_at')->nullable();
            $table->boolean('notified_initial_email')->default(false);
            $table->timestamps();

            $table->foreign('thread_id')->references('id')->on('chat_threads')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['thread_id', 'user_id']);
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('thread_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('message');
            $table->string('attachment_name')->nullable();
            $table->string('attachment_url')->nullable();
            $table->timestamps();

            $table->foreign('thread_id')->references('id')->on('chat_threads')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_participants');
        Schema::dropIfExists('chat_threads');
    }
};
