<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('client_type')->default('Direct');
            $table->string('contact_person')->nullable();
            $table->string('owner')->nullable();
            $table->integer('projects')->default(0);
            $table->string('service')->nullable();
            $table->string('project_status')->default('Active');
            $table->decimal('project_value', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
