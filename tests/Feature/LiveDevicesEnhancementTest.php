<?php

namespace Tests\Feature;

use App\Models\HotspotProfile;
use App\Models\HotspotUser;
use App\Models\User;
use App\Services\RouterOsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class LiveDevicesEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_devices_page_renders_with_category_filter_and_table_structure(): void
    {
        $user = User::factory()->create();

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getDhcpLeases')->andReturn([]);
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([]);
        $mockRouter->shouldReceive('getHotspotIpBindings')->andReturn([]);
        $this->app->instance(RouterOsService::class, $mockRouter);

        $response = $this->actingAs($user)->get('/devices');

        $response->assertStatus(200);

        // 1. Filter Kategori exists
        $response->assertSee('Semua Kategori');
        $response->assertSee('Voucher (Profit)');
        $response->assertSee('Bulanan (Profit)');
        $response->assertSee('Pribadi (Non-Profit)');
        $response->assertSee('Bypassed (PC/CCTV/Server)');
        $response->assertSee('Diblokir (IP Binding)');

        // 2. Status chips and KPI
        $response->assertSee('Diblokir');

        // 3. Table headers and alignment
        $response->assertSee('User / Bypass');
        $response->assertSee('Kategori');
        $response->assertSee('text-center">Status', false);
        $response->assertSee('text-right">Aksi', false);
    }

    public function test_bypassed_device_is_identified_and_categorized(): void
    {
        $user = User::factory()->create();

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getDhcpLeases')->andReturn([
            [
                '.id' => '*1',
                'mac-address' => '00:11:22:33:44:55',
                'address' => '192.168.88.50',
                'host-name' => 'CCTV-Gate',
                'status' => 'bound',
                'server' => 'hotspot',
                'dynamic' => 'false',
            ],
            [
                '.id' => '*2',
                'mac-address' => 'AA:BB:CC:DD:EE:FF',
                'address' => '192.168.88.60',
                'host-name' => 'Phone-User',
                'status' => 'bound',
                'server' => 'hotspot',
                'dynamic' => 'true',
            ],
        ]);

        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([
            [
                'mac-address' => 'AA:BB:CC:DD:EE:FF',
                'address' => '192.168.88.60',
                'user' => 'voucher_user_1',
            ],
        ]);

        $mockRouter->shouldReceive('getHotspotIpBindings')->andReturn([
            [
                '.id' => '*10',
                'mac-address' => '00:11:22:33:44:55',
                'type' => 'bypassed',
                'comment' => 'CCTV Pintu Depan',
                'disabled' => 'false',
            ],
        ]);

        $this->app->instance(RouterOsService::class, $mockRouter);

        $prof = HotspotProfile::create([
            'name' => 'Paket-Voucher',
            'category' => 'voucher',
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'voucher_user_1',
            'password' => '1234',
            'profile_id' => $prof->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/devices/live');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals(2, $data['count']);
        $this->assertEquals(1, $data['stats']['bypassed']);
        $this->assertEquals(1, $data['stats']['online']);

        // CCTV device
        $cctv = collect($data['devices'])->firstWhere('mac_address', '00:11:22:33:44:55');
        $this->assertNotNull($cctv);
        $this->assertTrue($cctv['is_bypassed']);
        $this->assertEquals('bypassed', $cctv['status']);
        $this->assertEquals('bypassed', $cctv['category']);
        $this->assertEquals('CCTV Pintu Depan', $cctv['binding_comment']);

        // Phone device
        $phone = collect($data['devices'])->firstWhere('mac_address', 'AA:BB:CC:DD:EE:FF');
        $this->assertNotNull($phone);
        $this->assertFalse($phone['is_bypassed']);
        $this->assertEquals('online', $phone['status']);
        $this->assertEquals('voucher', $phone['category']);
        $this->assertEquals('voucher_user_1', $phone['username']);
    }

    public function test_device_detail_ignores_disabled_blocked_binding(): void
    {
        $user = User::factory()->create();

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getDhcpLeases')->andReturn([
            [
                'mac-address' => '11:22:33:44:55:66',
                'address' => '192.168.88.75',
                'host-name' => 'Laptop-Saya',
                'status' => 'bound',
            ],
        ]);
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([]);

        // IP binding exists in router but is DISABLED
        $mockRouter->shouldReceive('getHotspotIpBinding')
            ->with('11:22:33:44:55:66')
            ->andReturn([
                '.id' => '*99',
                'mac-address' => '11:22:33:44:55:66',
                'type' => 'blocked',
                'disabled' => 'true',
            ]);

        $this->app->instance(RouterOsService::class, $mockRouter);

        $response = $this->actingAs($user)->getJson('/api/devices/11:22:33:44:55:66/detail');

        $response->assertStatus(200);
        $data = $response->json();

        // Must NOT return disabled block rule as active binding
        $this->assertNull($data['ip_binding']);
    }

    public function test_blocked_device_is_identified_and_filtered(): void
    {
        $user = User::factory()->create();

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getDhcpLeases')->andReturn([
            [
                '.id' => '*1',
                'mac-address' => '11:22:33:44:55:66',
                'address' => '192.168.88.88',
                'host-name' => 'Bad-Device',
                'status' => 'bound',
                'server' => 'hotspot',
                'dynamic' => 'false',
            ],
        ]);

        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([]);

        // IP binding has type blocked
        $mockRouter->shouldReceive('getHotspotIpBindings')->andReturn([
            [
                '.id' => '*10',
                'mac-address' => '11:22:33:44:55:66',
                'address' => '192.168.88.88',
                'type' => 'blocked',
                'comment' => 'Perangkat Diblokir Admin',
                'disabled' => 'false',
            ],
            // Extra binding not in DHCP leases
            [
                '.id' => '*11',
                'mac-address' => '99:88:77:66:55:44',
                'address' => '192.168.88.99',
                'type' => 'blocked',
                'comment' => 'Blokir Offline',
                'disabled' => 'false',
            ],
        ]);

        $this->app->instance(RouterOsService::class, $mockRouter);

        $response = $this->actingAs($user)->getJson('/api/devices/live');

        $response->assertStatus(200);
        $data = $response->json();

        // 2 devices total (1 from DHCP + 1 extra binding)
        $this->assertEquals(2, $data['count']);
        $this->assertEquals(2, $data['stats']['blocked']);

        $dev1 = collect($data['devices'])->firstWhere('mac_address', '11:22:33:44:55:66');
        $this->assertNotNull($dev1);
        $this->assertTrue($dev1['is_blocked']);
        $this->assertEquals('blocked', $dev1['status']);
        $this->assertEquals('blocked', $dev1['category']);
        $this->assertEquals('Perangkat Diblokir Admin', $dev1['binding_comment']);

        $dev2 = collect($data['devices'])->firstWhere('mac_address', '99:88:77:66:55:44');
        $this->assertNotNull($dev2);
        $this->assertTrue($dev2['is_blocked']);
        $this->assertEquals('blocked', $dev2['status']);
        $this->assertEquals('blocked', $dev2['category']);
        $this->assertEquals('Blokir Offline', $dev2['binding_comment']);
    }
}
