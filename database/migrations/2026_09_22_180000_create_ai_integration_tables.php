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
        // 1. Gated AI Reports & Analytics (Gemini)
        if (! Schema::hasTable('ai_reports')) {
            Schema::create('ai_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('user_role')->default('member');
                $table->string('report_type'); // project_status, financial_summary, executive_digest
                $table->string('data_scope')->nullable();
                $table->longText('summary_content');
                $table->json('structured_data')->nullable();
                $table->string('model_used')->default('gemini-1.5-flash');
                $table->string('cache_key')->index();
                $table->timestamps();
            });
        }

        // 2. Chatbot Conversations & Audit Sessions (Claude)
        if (! Schema::hasTable('ai_chat_conversations')) {
            Schema::create('ai_chat_conversations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('session_id')->index();
                $table->string('mode')->default('general_help'); // general_help, lead_gen, fundraising_partner
                $table->json('messages'); // list of turns with timestamp & role
                $table->boolean('escalated')->default(false);
                $table->string('escalation_reason')->nullable();
                $table->timestamps();
            });
        }

        // 3. Sourced Leads & Grants from AI Research
        if (! Schema::hasTable('ai_opportunities')) {
            Schema::create('ai_opportunities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type')->default('lead'); // lead, grant, partnership
                $table->string('title');
                $table->string('organization')->nullable();
                $table->decimal('estimated_value', 15, 2)->nullable();
                $table->string('source')->nullable();
                $table->text('summary')->nullable();
                $table->unsignedInteger('confidence_score')->nullable();
                $table->string('status')->default('discovered'); // discovered, reviewing, converted, rejected
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_opportunities');
        Schema::dropIfExists('ai_chat_conversations');
        Schema::dropIfExists('ai_reports');
    }
};
