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
        if (!Schema::hasTable('traffic_category_stats')) {
            Schema::create('traffic_category_stats', function (Blueprint $table) {
                $table->id();
                $table->date('recorded_date')->index();
                $table->unsignedTinyInteger('recorded_hour')->default(0)->index();
                $table->string('category')->index(); // video, social_media, gaming, cloud_work, browsing, other
                $table->string('platform')->index(); // YouTube, TikTok, WhatsApp, etc.
                $table->unsignedBigInteger('bytes_in')->default(0);
                $table->unsignedBigInteger('bytes_out')->default(0);
                $table->unsignedBigInteger('total_bytes')->default(0);
                $table->timestamps();

                $table->unique(['recorded_date', 'recorded_hour', 'platform'], 'unique_date_hour_platform');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traffic_category_stats');
    }
};
