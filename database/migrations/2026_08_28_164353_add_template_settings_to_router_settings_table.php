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
        Schema::table('router_settings', function (Blueprint $table) {
            $table->string('hotspot_name', 150)->nullable()->default('MIKROTIK HOTSPOT');
            $table->string('login_url', 150)->nullable()->default('http://hotspot.lan');
            $table->string('footer_text', 255)->nullable()->default('Terima kasih atas kunjungan Anda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('router_settings', function (Blueprint $table) {
            $table->dropColumn(['hotspot_name', 'login_url', 'footer_text']);
        });
    }
};
