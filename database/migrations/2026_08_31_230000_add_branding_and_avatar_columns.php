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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'avatar')) {
                    $table->string('avatar')->nullable()->after('email');
                }
            });
        }

        if (Schema::hasTable('router_settings')) {
            Schema::table('router_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('router_settings', 'app_name')) {
                    $table->string('app_name')->nullable()->default('MikroTik Manager')->after('name');
                }
                if (!Schema::hasColumn('router_settings', 'app_logo')) {
                    $table->string('app_logo')->nullable()->after('app_name');
                }
                if (!Schema::hasColumn('router_settings', 'app_favicon')) {
                    $table->string('app_favicon')->nullable()->after('app_logo');
                }
                if (!Schema::hasColumn('router_settings', 'tagline')) {
                    $table->string('tagline')->nullable()->default('Hotspot & Bandwidth Management')->after('app_favicon');
                }
                if (!Schema::hasColumn('router_settings', 'contact_phone')) {
                    $table->string('contact_phone')->nullable()->after('tagline');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'avatar')) {
                    $table->dropColumn('avatar');
                }
            });
        }

        if (Schema::hasTable('router_settings')) {
            Schema::table('router_settings', function (Blueprint $table) {
                $columns = ['app_name', 'app_logo', 'app_favicon', 'tagline', 'contact_phone'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('router_settings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
