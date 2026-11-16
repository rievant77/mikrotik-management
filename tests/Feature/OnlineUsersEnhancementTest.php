<?php

namespace Tests\Feature;

use App\Models\HotspotProfile;
use App\Models\HotspotUser;
use App\Models\User;
use App\Services\RouterOsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class OnlineUsersEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_online_users_page_renders_with_kpi_metrics_and_category_filters(): void
    {
        $user = User::factory()->create();

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([
            [
                '.id' => '*1',
                'user' => 'user_voucher',
                'address' => '192.168.88.10',
                'mac-address' => '00:11:22:33:44:55',
                'uptime' => '1h30m',
                'bytes-in' => '1048576',
                'bytes-out' => '2097152',
                'rx-rate' => '1000000',
                'tx-rate' => '500000',
            ],
            [
                '.id' => '*2',
                'user' => 'user_bulanan',
                'address' => '192.168.88.20',
                'mac-address' => 'AA:BB:CC:DD:EE:FF',
                'uptime' => '2d4h',
                'bytes-in' => '10485760',
                'bytes-out' => '20971520',
                'rx-rate' => '2000000',
                'tx-rate' => '1000000',
            ],
        ]);

        $mockRouter->shouldReceive('getDhcpLeases')->andReturn([]);
        $this->app->instance(RouterOsService::class, $mockRouter);

        $profVoucher = HotspotProfile::create([
            'name' => '1-Jam-Voucher',
            'category' => 'voucher',
            'is_active' => true,
        ]);

        $profBulanan = HotspotProfile::create([
            'name' => 'Paket-Rumahan-30D',
            'category' => 'bulanan',
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'user_voucher',
            'password' => '1234',
            'profile_id' => $profVoucher->id,
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'user_bulanan',
            'password' => 'pass123',
            'profile_id' => $profBulanan->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/users');

        $response->assertStatus(200);

        // 1. Assert 5 KPI metrics exist
        $response->assertSee('Total Online');
        $response->assertSee('Voucher');
        $response->assertSee('Bulanan');
        $response->assertSee('Pribadi');
        $response->assertSee('Trafik Live');

        // 2. Assert Category Filter dropdown
        $response->assertSee('Kategori:');
        $response->assertSee('Semua Kategori');
        $response->assertSee('Voucher (Profit)');
        $response->assertSee('Bulanan (Profit)');
        $response->assertSee('Pribadi (Non-Profit)');

        // 3. Assert Table Headers and alignments
        $response->assertSee('User & Perangkat', false);
        $response->assertSee('IP / MAC Address', false);
        $response->assertSee('text-center">Kategori', false);
        $response->assertSee('Paket Profil');
        $response->assertSee('text-center">Uptime', false);
        $response->assertSee('text-right">Live Rate (Rx / Tx)', false);
        $response->assertSee('text-right">Total Kuota Sesi', false);
        $response->assertSee('text-center">Aksi', false);

        // 4. Assert Action Buttons
        $response->assertSee('Detail');
        $response->assertSee('Kick');
    }

    public function test_online_users_live_api_returns_category_formatted_uptime_and_stats(): void
    {
        $user = User::factory()->create();

        $prof = HotspotProfile::create([
            'name' => 'Paket-Bulanan-VIP',
            'category' => 'bulanan',
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'client_rumahan_1',
            'password' => '1234',
            'profile_id' => $prof->id,
            'is_active' => true,
        ]);

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([
            [
                '.id' => '*9',
                'user' => 'client_rumahan_1',
                'address' => '192.168.88.99',
                'mac-address' => '12:34:56:78:9A:BC',
                'uptime' => '2h15m30s',
                'bytes-in' => '5000000',
                'bytes-out' => '10000000',
                'rx-rate' => '1500000',
                'tx-rate' => '500000',
            ],
        ]);
        $mockRouter->shouldReceive('getDhcpLeases')->andReturn([]);
        $this->app->instance(RouterOsService::class, $mockRouter);

        $response = $this->actingAs($user)->getJson('/users/api/live');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals(1, $data['count']);
        $this->assertArrayHasKey('stats', $data);
        $this->assertEquals(1, $data['stats']['total']);
        $this->assertEquals(0, $data['stats']['voucher']);
        $this->assertEquals(1, $data['stats']['bulanan']);
        $this->assertEquals(0, $data['stats']['pribadi']);
        $this->assertEquals(1500000, $data['stats']['total_rx_bps']);
        $this->assertEquals(500000, $data['stats']['total_tx_bps']);
        $this->assertEquals(15000000, $data['stats']['total_bytes']);

        $session = $data['sessions'][0];
        $this->assertEquals('client_rumahan_1', $session['username']);
        $this->assertEquals('bulanan', $session['category']);
        $this->assertEquals('2 jam 15 mnt', $session['uptime_formatted']);
        $this->assertGreaterThan(0, $session['uptime_seconds']);
    }

    public function test_online_users_disconnect_action_executes_successfully(): void
    {
        $user = User::factory()->create();

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('disconnectHotspotUser')
            ->with('user_to_kick')
            ->once()
            ->andReturn(true);

        $this->app->instance(RouterOsService::class, $mockRouter);

        $response = $this->actingAs($user)->postJson('/users/user_to_kick/disconnect');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}
