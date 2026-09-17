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
        Schema::create('fundraising_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('organization'); // e.g. D-Prize, Wildlife Fund, UNDP
            $table->string('program_title')->nullable(); // e.g. Custom solution, The Connect and Create programme
            $table->text('application_link')->nullable();
            $table->decimal('amount_kes', 14, 2)->default(0);
            $table->string('amount_display')->nullable(); // e.g. KES 2,588,000 or Capacity-building
            $table->string('funding_type')->default('Grant'); // Grant, Fellowship, Capacity-building, Residency, Competition, Partnership
            $table->string('deadline')->nullable(); // Date string or 'Rolling basis'
            $table->string('status')->default('Identified'); // Identified, In Progress, Submitted, Reviewing, Won / Awarded, Missed, Rejected
            $table->string('category')->default('open_calls'); // open_calls, partnerships, fellowships
            $table->string('partnership_entity_type')->nullable(); // Grantmakers/CBOs, Associations/Cooperatives, Corporate Institutions, Academia
            $table->string('partner_organization')->nullable(); // e.g. Pankaj Social Service, Kilimora CLG
            $table->string('lead_owner')->nullable()->default('Barny Kiome');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fundraising_opportunities');
    }
};
