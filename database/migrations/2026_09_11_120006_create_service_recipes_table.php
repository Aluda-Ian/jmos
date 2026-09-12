<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10);
            $table->json('stages');
            $table->string('deliverables');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_recipes');
    }
};
