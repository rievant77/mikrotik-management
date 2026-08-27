<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. router_settings
        Schema::create('router_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('host', 100);
            $table->integer('api_port')->default(8728);
            $table->string('username', 100);
            $table->text('password'); // encrypted
            $table->boolean('use_ssl')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('connection_timeout_ms')->default(3000);
            $table->timestamp('last_successful_poll_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->timestamps();
        });

        // 2. hotspot_profiles
        Schema::create('hotspot_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->integer('shared_users')->default(1);
            $table->string('rate_limit', 100)->nullable();
            $table->string('validity', 50)->nullable();
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->bigInteger('data_limit_bytes')->default(0);
            $table->string('expired_mode', 50)->default('Remove');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. hotspot_users
        Schema::create('hotspot_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->nullable()->constrained('hotspot_profiles')->nullOnDelete();
            $table->string('username', 100)->unique();
            $table->string('password', 255);
            $table->string('uptime_limit', 50)->nullable();
            $table->string('comment', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });

        // 4. monthly_customers
        Schema::create('monthly_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->index();
            $table->foreignId('hotspot_user_id')->nullable()->constrained('hotspot_users')->nullOnDelete();
            $table->string('contact', 100)->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('monthly_price', 12, 2)->default(0);
            $table->integer('billing_day')->nullable()->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. cashier_shifts
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_cash', 12, 2)->default(0);
            $table->decimal('expected_cash', 12, 2)->default(0);
            $table->decimal('actual_cash', 12, 2)->nullable();
            $table->decimal('discrepancy', 12, 2)->nullable();
            $table->string('status', 20)->default('open'); // open, closed
            $table->timestamps();
        });

        // 6. hotspot_sessions
        Schema::create('hotspot_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotspot_user_id')->nullable()->constrained('hotspot_users')->nullOnDelete();
            $table->string('username', 100)->index();
            $table->string('mac_address', 20)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('interface', 50)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('last_seen_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('status', 20)->default('active'); // active, ended
            $table->bigInteger('last_bytes_in')->default(0);
            $table->bigInteger('last_bytes_out')->default(0);
            $table->bigInteger('total_bytes_in')->default(0);
            $table->bigInteger('total_bytes_out')->default(0);
            $table->bigInteger('current_rx_bps')->default(0);
            $table->bigInteger('current_tx_bps')->default(0);
            $table->timestamps();
        });

        // 7. usage_snapshots
        Schema::create('usage_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('hotspot_sessions')->cascadeOnDelete();
            $table->timestamp('recorded_at')->index();
            $table->bigInteger('bytes_in')->default(0);
            $table->bigInteger('bytes_out')->default(0);
            $table->bigInteger('delta_bytes_in')->default(0);
            $table->bigInteger('delta_bytes_out')->default(0);
            $table->bigInteger('upload_bps')->default(0);
            $table->bigInteger('download_bps')->default(0);
            $table->integer('uptime_seconds')->default(0);
            $table->uuid('collector_run_id')->index();
            $table->timestamp('created_at')->nullable();

            $table->index(['session_id', 'recorded_at']);
            $table->unique(['collector_run_id', 'session_id']);
        });

        // 8. daily_user_usage_summaries
        Schema::create('daily_user_usage_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotspot_user_id')->nullable()->constrained('hotspot_users')->nullOnDelete();
            $table->string('username', 100)->index();
            $table->date('usage_date')->index();
            $table->bigInteger('total_bytes_in')->default(0);
            $table->bigInteger('total_bytes_out')->default(0);
            $table->bigInteger('total_bytes')->default(0);
            $table->integer('total_uptime_seconds')->default(0);
            $table->integer('session_count')->default(0);
            $table->timestamps();

            $table->unique(['username', 'usage_date']);
        });

        // 9. voucher_sales
        Schema::create('voucher_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotspot_user_id')->nullable()->constrained('hotspot_users')->nullOnDelete();
            $table->string('username', 100)->index();
            $table->string('profile_name', 100);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('profit', 12, 2)->default(0);
            $table->timestamp('activated_at')->index();
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('cashier_shifts')->nullOnDelete();
            $table->string('status', 20)->default('completed'); // completed, void
            $table->timestamps();
        });

        // 10. monthly_payments
        Schema::create('monthly_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_customer_id')->constrained('monthly_customers')->restrictOnDelete();
            $table->date('billing_month')->index(); // YYYY-MM-01
            $table->decimal('monthly_price', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->date('paid_at');
            $table->string('payment_method', 20)->default('cash'); // cash, transfer, qris
            $table->string('status', 20)->default('paid'); // paid, partial, void
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('cashier_shifts')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 11. audit_logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100)->index();
            $table->string('entity_type', 100)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('monthly_payments');
        Schema::dropIfExists('voucher_sales');
        Schema::dropIfExists('daily_user_usage_summaries');
        Schema::dropIfExists('usage_snapshots');
        Schema::dropIfExists('hotspot_sessions');
        Schema::dropIfExists('cashier_shifts');
        Schema::dropIfExists('monthly_customers');
        Schema::dropIfExists('hotspot_users');
        Schema::dropIfExists('hotspot_profiles');
        Schema::dropIfExists('router_settings');
    }
};
