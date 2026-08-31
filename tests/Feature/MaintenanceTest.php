<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\DailyUserUsageSummary;
use App\Models\HotspotProfile;
use App\Models\HotspotUser;
use App\Models\MonthlyCustomer;
use App\Models\MonthlyInvoice;
use App\Models\MonthlyPayment;
use App\Models\UsageSnapshot;
use App\Models\User;
use App\Models\VoucherSale;
use App\Services\RouterOsService;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    public function test_maintenance_page_renders_and_returns_counts(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $res = $this->get(route('settings.maintenance'));
        $res->assertStatus(200);
        $res->assertSee('Pemeliharaan');
        $res->assertSee('Reset Data Penjualan');
        $res->assertSee('Reset Pengguna Hotspot');
        $res->assertSee('Reset Counter Internet');
        $res->assertViewHas('counts');

        $jsonRes = $this->getJson(route('settings.maintenance'));
        $jsonRes->assertStatus(200);
        $jsonRes->assertJsonStructure(['success', 'counts']);
    }

    public function test_reset_sales_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Seed some sales and invoice records
        VoucherSale::create([
            'username' => 'vc-reset-01',
            'profile_name' => 'PAKET_1',
            'cost_price' => 1000,
            'selling_price' => 3000,
            'profit' => 2000,
            'activated_at' => now(),
            'status' => 'completed',
        ]);

        $cust = MonthlyCustomer::create([
            'name' => 'Cust Reset',
            'monthly_price' => 100000,
            'is_active' => true,
        ]);

        $inv = MonthlyInvoice::create([
            'invoice_number' => 'INV-202608-9999',
            'monthly_customer_id' => $cust->id,
            'billing_month' => now()->startOfMonth()->toDateString(),
            'amount' => 100000,
            'balance_due' => 100000,
            'status' => 'unpaid',
        ]);

        MonthlyPayment::create([
            'monthly_customer_id' => $cust->id,
            'monthly_invoice_id' => $inv->id,
            'billing_month' => now()->startOfMonth()->toDateString(),
            'amount_paid' => 100000,
            'paid_at' => now()->toDateString(),
            'status' => 'paid',
        ]);

        // Wrong confirmation text must fail
        $badRes = $this->postJson(route('settings.maintenance.reset'), [
            'action' => 'reset_sales',
            'confirmation_text' => 'WRONG',
        ]);
        $badRes->assertStatus(422);

        // Correct confirmation text
        $res = $this->postJson(route('settings.maintenance.reset'), [
            'action' => 'reset_sales',
            'confirmation_text' => 'RESET',
        ]);
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $this->assertEquals(0, VoucherSale::count());
        $this->assertEquals(0, MonthlyInvoice::count());
        $this->assertEquals(0, MonthlyPayment::count());
    }

    public function test_reset_internet_counters_and_fup(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Mock RouterOS service
        $this->mock(RouterOsService::class, function ($m) {
            $m->shouldReceive('resetHotspotUserCounters')->andReturn(true);
        });

        $profile = HotspotProfile::create([
            'name' => 'FUP_PROF',
            'selling_price' => 50000,
            'fup_enabled' => true,
            'fup_limit_bytes' => 1000000000,
            'fup_rate_limit' => '512k/512k',
            'is_active' => true,
        ]);

        $hotspotUser = HotspotUser::create([
            'username' => 'user-counter-test',
            'password' => '1234',
            'profile_id' => $profile->id,
            'fup_usage_bytes' => 1500000000,
            'fup_active' => true,
            'is_active' => true,
        ]);

        DailyUserUsageSummary::create([
            'username' => 'user-counter-test',
            'usage_date' => now()->toDateString(),
            'total_bytes_in' => 500000000,
            'total_bytes_out' => 500000000,
            'total_bytes' => 1000000000,
            'total_uptime_seconds' => 3600,
        ]);

        $res = $this->postJson(route('settings.maintenance.reset'), [
            'action' => 'reset_counters',
            'confirmation_text' => 'RESET',
            'target_username' => 'user-counter-test',
        ]);
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $this->assertEquals(0, DailyUserUsageSummary::where('username', 'user-counter-test')->count());

        $hotspotUser->refresh();
        $this->assertEquals(0, $hotspotUser->fup_usage_bytes);
        $this->assertFalse($hotspotUser->fup_active);
    }

    public function test_reset_hotspot_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->mock(RouterOsService::class, function ($m) {
            $m->shouldReceive('removeAllHotspotUsers')->andReturn(true);
        });

        $profile = HotspotProfile::create([
            'name' => 'PROFILE_TEST',
            'selling_price' => 5000,
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'hotspot-user-01',
            'password' => '1234',
            'profile_id' => $profile->id,
            'is_active' => true,
        ]);

        $res = $this->postJson(route('settings.maintenance.reset'), [
            'action' => 'reset_users',
            'confirmation_text' => 'RESET',
            'delete_from_router' => true,
            'include_monthly_customers' => false,
        ]);
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $this->assertEquals(0, HotspotUser::count());
    }
}
