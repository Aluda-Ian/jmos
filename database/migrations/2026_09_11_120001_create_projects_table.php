<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->string('client');
            $table->string('project_type')->nullable();
            $table->string('project_manager')->nullable();
            $table->string('stage')->default('brief'); // brief, concept, pre-pro, shoot, edit, review, delivery
            $table->string('status')->default('On track'); // On track, At risk, Blocked, Delivering, Completed
            $table->string('priority')->default('Medium'); // High, Medium, Low
            $table->string('deadline')->nullable();
            $table->decimal('budget', 12, 2)->default(0);
            $table->integer('progress_pct')->default(0);
            $table->string('waiting_on')->default('us'); // us, client
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
