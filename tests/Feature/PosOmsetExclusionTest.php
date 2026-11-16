<?php

namespace Tests\Feature;

use App\Models\CashierShift;
use App\Models\HotspotProfile;
use App\Models\User;
use App\Models\VoucherSale;
use App\Services\PosManagerService;
use App\Services\RouterOsService;
use Mockery;
use Tests\TestCase;

class PosOmsetExclusionTest extends TestCase
{
    public function test_pribadi_profile_vouchers_are_excluded_from_dashboard_and_pos_revenue(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Mock RouterOsService for dashboard calls
        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([]);
        $mockRouter->shouldReceive('getSystemResource')->andReturn(['cpu-load' => 5, 'free-memory' => 1000000, 'uptime' => '1d']);
        $mockRouter->shouldReceive('getInterfaceTraffic')->andReturn(['rx_bps' => 0, 'tx_bps' => 0]);
        $mockRouter->shouldReceive('getDhcpLeases')->andReturn([]);
        $this->app->instance(RouterOsService::class, $mockRouter);

        // 1. Create profiles
        $profitProfile = HotspotProfile::create([
            'name' => 'PKT_PROFIT_TEST',
            'category' => 'profit',
            'selling_price' => 5000,
            'cost_price' => 2000,
            'validity' => '1d',
            'expired_mode' => 'Remove',
        ]);

        $pribadiProfile = HotspotProfile::create([
            'name' => 'PKT_PRIBADI_TEST',
            'category' => 'pribadi',
            'selling_price' => 15000,
            'cost_price' => 0,
            'validity' => '30d',
            'expired_mode' => 'Notice',
        ]);

        $shift = CashierShift::create([
            'opened_at' => now(),
            'opening_cash' => 50000,
            'status' => 'open',
        ]);

        // 2. Create voucher sales for today in this shift
        VoucherSale::create([
            'username' => 'vc-profit-01',
            'profile_name' => $profitProfile->name,
            'cost_price' => 2000,
            'selling_price' => 5000,
            'profit' => 3000,
            'activated_at' => now(),
            'shift_id' => $shift->id,
            'status' => 'completed',
        ]);

        VoucherSale::create([
            'username' => 'vc-pribadi-01',
            'profile_name' => $pribadiProfile->name,
            'cost_price' => 0,
            'selling_price' => 15000,
            'profit' => 15000,
            'activated_at' => now(),
            'shift_id' => $shift->id,
            'status' => 'completed',
        ]);

        // 3. Verify CashierShift expected_cash: should ONLY include opening_cash (50000) + profit voucher (5000) = 55000 (NOT 70000)
        $posService = new PosManagerService();
        $expectedCash = $posService->recalculateShiftExpectedCash($shift);
        $this->assertEquals(55000.0, $expectedCash);

        // 4. Verify PosManagerService getDetailedDashboardAnalytics
        $analytics = $posService->getDetailedDashboardAnalytics(now()->format('Y-m'));
        $this->assertEquals(5000.0, $analytics['voucher_revenue']);

        // 5. Verify DashboardController liveData JSON
        $dashRes = $this->getJson(route('api.dashboard.live'));
        $dashRes->assertStatus(200);
        $dashData = $dashRes->json();
        $this->assertEquals(5000.0, (float) $dashData['today_revenue']);
        $this->assertEquals(1, (int) $dashData['today_vouchers_sold']);

        // 6. Verify Dashboard view data
        $dashViewRes = $this->get(route('dashboard'));
        $dashViewRes->assertStatus(200);
        $dashViewRes->assertViewHas('todayRevenue', 5000.0);
        $dashViewRes->assertViewHas('todayVouchersSold', 1);

        // 7. Verify POS vouchers view metrics
        $posVoucherRes = $this->get(route('pos.vouchers'));
        $posVoucherRes->assertStatus(200);
        $posVoucherRes->assertViewHas('metrics', function ($m) {
            return $m['total_revenue'] === 5000.0 && $m['total_count'] === 1;
        });
    }
}
