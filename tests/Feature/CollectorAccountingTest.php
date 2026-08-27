<?php

namespace Tests\Feature;

use App\Models\CashierShift;
use App\Models\HotspotProfile;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use App\Models\VoucherSale;
use App\Services\CollectorService;
use App\Services\RouterOsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CollectorAccountingTest extends TestCase
{
    public function test_voucher_revenue_is_triggered_on_first_activation_only(): void
    {
        $profile = HotspotProfile::firstOrCreate(['name' => 'Test-3Jam'], [
            'selling_price' => 3000,
            'cost_price' => 1500,
            'validity' => '3h',
        ]);

        $user = HotspotUser::firstOrCreate(['username' => 'vc-test01'], [
            'profile_id' => $profile->id,
            'password' => '123',
            'uptime_limit' => '3h',
        ]);

        $shift = CashierShift::firstOrCreate(['status' => 'open'], [
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        // Mock RouterOS returning active session for vc-test01
        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getDhcpLeases')->andReturn([])->byDefault();
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([
            [
                'user' => 'vc-test01',
                'mac-address' => '00:11:22:33:44:55',
                'address' => '192.168.88.150',
                'bytes-in' => '500000',
                'bytes-out' => '2000000',
                'uptime' => '10m',
            ]
        ]);

        $collector = new CollectorService($mockRouter);
        $res = $collector->collect();

        $this->assertEquals('success', $res['status']);
        $this->assertEquals(1, $res['sessions_processed']);

        // Assert VoucherSale is recorded exactly once
        $sale = VoucherSale::where('username', 'vc-test01')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(3000, (float) $sale->selling_price);
        $this->assertEquals(1500, (float) $sale->profit);

        // Run collector again with incremented bytes -> should NOT duplicate voucher sale
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([
            [
                'user' => 'vc-test01',
                'mac-address' => '00:11:22:33:44:55',
                'address' => '192.168.88.150',
                'bytes-in' => '1000000',
                'bytes-out' => '4000000',
                'uptime' => '20m',
            ]
        ]);

        $collector->collect();

        $this->assertEquals(1, VoucherSale::where('username', 'vc-test01')->count());
    }

    public function test_counter_reset_does_not_produce_negative_deltas(): void
    {
        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getDhcpLeases')->andReturn([])->byDefault();

        // Mock 1st poll (100MB) and 2nd poll (rebooted counter 10MB)
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturnValues([
            [
                [
                    'user' => 'user-reset-test',
                    'mac-address' => 'AA:BB:CC:DD:EE:FF',
                    'address' => '192.168.88.200',
                    'bytes-in' => '100000000',
                    'bytes-out' => '100000000',
                    'uptime' => '1h',
                ]
            ],
            [
                [
                    'user' => 'user-reset-test',
                    'mac-address' => 'AA:BB:CC:DD:EE:FF',
                    'address' => '192.168.88.200',
                    'bytes-in' => '10000000',
                    'bytes-out' => '10000000',
                    'uptime' => '5m',
                ]
            ]
        ]);

        $collector = new CollectorService($mockRouter);
        $collector->collect();

        $session = HotspotSession::where('username', 'user-reset-test')->first();
        $this->assertNotNull($session);
        $this->assertEquals(100000000, $session->total_bytes_in);

        // Second poll
        $res2 = $collector->collect();
        $this->assertEquals('success', $res2['status'], $res2['message'] ?? 'No error message');

        $session->refresh();
        // Total bytes should accumulate without dropping or going negative: 100MB + 10MB = 110MB
        $this->assertEquals(110000000, $session->total_bytes_in);
    }
}
