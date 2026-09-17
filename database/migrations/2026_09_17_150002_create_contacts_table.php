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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('contact_name');
            $table->string('company_name')->nullable();
            $table->unsignedBigInteger('client_id')->nullable()->index(); // Account reference
            $table->unsignedBigInteger('lead_id')->nullable()->index();
            $table->string('title')->nullable(); // Designation / Role
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('owner')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
