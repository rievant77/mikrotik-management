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
        if (!Schema::hasTable('device_web_histories')) {
            Schema::create('device_web_histories', function (Blueprint $table) {
                $table->id();
                $table->string('mac_address', 24)->index();
                $table->string('ip_address', 45)->nullable()->index();
                $table->string('username')->nullable()->index();
                $table->string('domain')->index();
                $table->string('site_name')->nullable();
                $table->string('category')->default('general')->index(); // video, social_media, gaming, ecommerce, news, cloud_work, general
                $table->string('protocol', 10)->default('https');
                $table->unsignedSmallInteger('port')->default(443);
                $table->unsignedInteger('hit_count')->default(1);
                $table->unsignedBigInteger('total_bytes')->default(0);
                $table->dateTime('first_seen_at')->nullable();
                $table->dateTime('last_seen_at')->nullable()->index();
                $table->timestamps();

                $table->unique(['mac_address', 'domain'], 'unique_device_domain');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_web_histories');
    }
};
