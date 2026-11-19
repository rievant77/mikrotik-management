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
        // 1. Daily Expenses (ad-hoc daily operational expenses)
        Schema::create('daily_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description', 255);
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('expense_date');
        });

        // 2. Monthly Expense Templates (master recurring fixed items like ISP, Electricity)
        Schema::create('monthly_expense_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('amount', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Monthly Expense Entries (monthly frozen snapshots)
        Schema::create('monthly_expense_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('monthly_expense_templates')->nullOnDelete();
            $table->string('name', 100);
            $table->decimal('amount', 12, 2);
            $table->date('billing_month');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['template_id', 'billing_month']);
            $table->index('billing_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_expense_entries');
        Schema::dropIfExists('monthly_expense_templates');
        Schema::dropIfExists('daily_expenses');
    }
};
