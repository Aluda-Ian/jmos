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
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable()->index()->after('client');
            }
            if (! Schema::hasColumn('invoices', 'project_id')) {
                $table->unsignedBigInteger('project_id')->nullable()->index()->after('client_id');
            }
            if (! Schema::hasColumn('invoices', 'title')) {
                $table->string('title')->nullable()->after('project_id');
            }
            if (! Schema::hasColumn('invoices', 'items')) {
                $table->json('items')->nullable()->after('type'); // [{description, quantity, rate, amount}]
            }
            if (! Schema::hasColumn('invoices', 'subtotal')) {
                $table->decimal('subtotal', 14, 2)->default(0)->after('amount');
            }
            if (! Schema::hasColumn('invoices', 'discount')) {
                $table->decimal('discount', 14, 2)->default(0)->after('subtotal');
            }
            if (! Schema::hasColumn('invoices', 'tax')) {
                $table->decimal('tax', 14, 2)->default(0)->after('discount');
            }
            if (! Schema::hasColumn('invoices', 'notes')) {
                $table->text('notes')->nullable()->after('due_date');
            }
            if (! Schema::hasColumn('invoices', 'quote_id')) {
                $table->unsignedBigInteger('quote_id')->nullable()->index()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'client_id',
                'project_id',
                'title',
                'items',
                'subtotal',
                'discount',
                'tax',
                'notes',
                'quote_id',
            ]);
        });
    }
};
