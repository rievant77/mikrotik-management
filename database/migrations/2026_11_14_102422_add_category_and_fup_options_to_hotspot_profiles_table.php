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
        Schema::table('hotspot_profiles', function (Blueprint $table) {
            $table->string('category', 20)->default('voucher')->after('name');
            $table->string('cost_price_type', 20)->default('custom')->after('validity');
            $table->decimal('cost_price_percent', 5, 2)->nullable()->after('cost_price_type');
            $table->integer('fup_rate_percent')->nullable()->after('fup_limit_display');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotspot_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'cost_price_type',
                'cost_price_percent',
                'fup_rate_percent',
            ]);
        });
    }
};
