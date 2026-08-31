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
        Schema::create('monthly_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('monthly_customer_id')->constrained('monthly_customers')->cascadeOnDelete();
            $table->date('billing_month')->index(); // YYYY-MM-01
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('unpaid')->index(); // unpaid, partial, paid, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('monthly_payments', function (Blueprint $table) {
            $table->foreignId('monthly_invoice_id')->nullable()->after('monthly_customer_id')->constrained('monthly_invoices')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_payments', function (Blueprint $table) {
            $table->dropForeign(['monthly_invoice_id']);
            $table->dropColumn('monthly_invoice_id');
        });

        Schema::dropIfExists('monthly_invoices');
    }
};
