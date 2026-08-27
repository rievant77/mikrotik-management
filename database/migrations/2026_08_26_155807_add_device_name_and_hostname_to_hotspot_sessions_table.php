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
        Schema::table('hotspot_sessions', function (Blueprint $table) {
            $table->string('device_name', 150)->nullable()->after('mac_address');
            $table->string('hostname', 150)->nullable()->after('device_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotspot_sessions', function (Blueprint $table) {
            $table->dropColumn(['device_name', 'hostname']);
        });
    }
};
