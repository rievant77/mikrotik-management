<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traffic_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 50)->default('browsing'); // video, social_media, gaming, cloud_work, browsing
            $table->string('mark_name', 100)->unique(); // e.g. agy_cm_mlbb
            $table->string('address_list_name', 100)->nullable(); // e.g. agy_list_mlbb
            $table->json('fqdn_domains')->nullable();
            $table->text('tls_host')->nullable();
            $table->text('tcp_ports')->nullable();
            $table->text('udp_ports')->nullable();
            $table->boolean('is_quic')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traffic_signatures');
    }
};
