<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->string('client');
            $table->string('type')->default('Deposit 60%');
            $table->decimal('amount', 12, 2);
            $table->string('method')->nullable(); // M-Pesa, Bank, Cheque
            $table->boolean('etims')->default(false);
            $table->string('status')->default('Sent'); // Sent, Paid, Overdue
            $table->string('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
