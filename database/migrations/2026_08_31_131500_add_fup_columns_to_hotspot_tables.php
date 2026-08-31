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
            $table->boolean('fup_enabled')->default(false)->after('expired_mode');
            $table->unsignedBigInteger('fup_limit_bytes')->nullable()->after('fup_enabled');
            $table->string('fup_limit_display', 50)->nullable()->after('fup_limit_bytes');
            $table->string('fup_rate_limit', 50)->nullable()->after('fup_limit_display');
            $table->string('fup_reset_cycle', 30)->default('daily')->after('fup_rate_limit');
        });

        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->boolean('fup_custom')->default(false)->after('expired_at');
            $table->unsignedBigInteger('fup_limit_bytes')->nullable()->after('fup_custom');
            $table->string('fup_rate_limit', 50)->nullable()->after('fup_limit_bytes');
            $table->boolean('fup_active')->default(false)->after('fup_rate_limit');
            $table->timestamp('fup_triggered_at')->nullable()->after('fup_active');
            $table->unsignedBigInteger('fup_usage_bytes')->default(0)->after('fup_triggered_at');
            $table->timestamp('fup_last_reset_at')->nullable()->after('fup_usage_bytes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotspot_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'fup_enabled',
                'fup_limit_bytes',
                'fup_limit_display',
                'fup_rate_limit',
                'fup_reset_cycle',
            ]);
        });

        Schema::table('hotspot_users', function (Blueprint $table) {
            $table->dropColumn([
                'fup_custom',
                'fup_limit_bytes',
                'fup_rate_limit',
                'fup_active',
                'fup_triggered_at',
                'fup_usage_bytes',
                'fup_last_reset_at',
            ]);
        });
    }
};
