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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->string('template')->default('photo-video-social');
            $table->string('title');

            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->unsignedBigInteger('quote_id')->nullable()->index();

            // Client party details (as they appear on the agreement)
            $table->string('client_name');
            $table->string('client_registration')->nullable(); // Certificate / Business name / ID no.
            $table->string('client_po_box')->nullable();
            $table->string('client_address')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('signatory_name')->nullable();
            $table->string('signatory_position')->nullable();

            // Commercial & deliverable variables used to fill the template
            $table->json('fields')->nullable();

            // The agreement text (HTML) – generated from the template, optionally customised by AI / edited
            $table->longText('body')->nullable();
            $table->text('ai_instructions')->nullable();
            $table->boolean('ai_generated')->default(false);

            $table->string('status')->default('Draft'); // Draft, Sent, Viewed, Signed, Void
            $table->string('access_token', 64)->unique();

            $table->dateTime('sent_at')->nullable();
            $table->dateTime('viewed_at')->nullable();
            $table->dateTime('provider_signed_at')->nullable();

            // Client e-signature record
            $table->dateTime('signed_at')->nullable();
            $table->longText('client_signature')->nullable(); // PNG data URL
            $table->string('client_signed_name')->nullable();
            $table->string('client_signed_position')->nullable();
            $table->string('client_signed_ip', 64)->nullable();
            $table->string('client_signed_user_agent', 500)->nullable();
            $table->boolean('data_consent')->default(false);
            $table->string('body_hash', 64)->nullable(); // sha256 of the exact text that was signed

            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
