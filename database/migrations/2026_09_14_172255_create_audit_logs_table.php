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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->default('System');
            $table->string('user_role')->default('system');
            $table->string('action', 50); // CREATE, UPDATE, DELETE, AUTH, SYSTEM
            $table->string('entity_type', 100)->nullable(); // Project, Client, Shoot, Task, Invoice, System
            $table->string('entity_id', 100)->nullable();
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['action', 'created_at']);
            $table->index(['entity_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
