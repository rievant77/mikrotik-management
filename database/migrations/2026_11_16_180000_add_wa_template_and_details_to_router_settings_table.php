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
            $table->string('header_subtext', 150)->nullable()->default('Internet Cepat & Stabil');
            $table->string('terms_text', 255)->nullable()->default('1 Voucher hanya untuk 1 Perangkat');
            $table->text('wa_template')->nullable();
            $table->boolean('show_qr')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('router_settings', function (Blueprint $table) {
            $table->dropColumn(['header_subtext', 'terms_text', 'wa_template', 'show_qr']);
        });
    }
};
