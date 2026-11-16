<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing profit categories to voucher
        if (Schema::hasColumn('hotspot_profiles', 'category')) {
            DB::table('hotspot_profiles')
                ->where('category', 'profit')
                ->update(['category' => 'voucher']);
        }

        // Add profile_id to monthly_customers
        Schema::table('monthly_customers', function (Blueprint $table) {
            $table->foreignId('profile_id')
                ->nullable()
                ->after('hotspot_user_id')
                ->constrained('hotspot_profiles')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('profile_id');
        });
    }
};
