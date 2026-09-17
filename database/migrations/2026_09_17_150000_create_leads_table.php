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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('lead_name');
            $table->string('company')->nullable();
            $table->string('title')->nullable(); // Designation / Role
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('lead_source')->default('Web Research'); // Web Research, Referral, LinkedIn, Cold Outreach, Website, Campaign, Partner, Inbound, Other
            $table->string('lead_status')->default('New'); // New, Attempted to Contact, Contacted, In Discussion, Qualified, Junk/Lost, Converted
            $table->string('lead_owner')->nullable()->default('Jeota Media');
            $table->string('rating')->default('Warm'); // Hot, Warm, Cold
            $table->string('industry')->nullable(); // Corporate, Tech, Agency, Agritech, Hospitality, Direct, etc.
            $table->decimal('annual_revenue', 14, 2)->default(0); // Estimated Value / Annual Budget
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->text('notes')->nullable();

            // Zoho CRM Conversion Tracking
            $table->boolean('is_converted')->default(false);
            $table->timestamp('converted_at')->nullable();
            $table->unsignedBigInteger('converted_client_id')->nullable(); // Converted Account
            $table->unsignedBigInteger('converted_contact_id')->nullable(); // Converted Contact
            $table->unsignedBigInteger('converted_deal_id')->nullable(); // Converted Pipeline Deal

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
