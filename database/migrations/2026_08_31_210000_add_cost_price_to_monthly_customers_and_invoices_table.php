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
        Schema::table('monthly_customers', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('monthly_price');
        });

        Schema::table('monthly_invoices', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->default(0)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_invoices', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });

        Schema::table('monthly_customers', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
