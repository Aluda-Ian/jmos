<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('client_name');
            $table->string('stage')->default('lead'); // lead, meeting, proposal, negotiation, won
            $table->decimal('value', 12, 2)->default(0);
            $table->string('meta_text')->nullable();
            $table->boolean('is_won')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
