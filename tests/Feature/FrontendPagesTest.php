<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class FrontendPagesTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('MikroTik Manager');
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@mikrotik.lan',
            'password' => bcrypt('password'),
        ]);

        $loginRes = $this->post('/login', [
            'email' => 'admin@mikrotik.lan',
            'password' => 'password',
        ]);
        $loginRes->assertRedirect('/');

        $this->assertAuthenticatedAs($user);

        $logoutRes = $this->post('/logout');
        $logoutRes->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_all_frontend_routes_render_successfully_when_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $routes = [
            '/',
            '/users',
            '/users/historical',
            '/users/budi-santoso',
            '/devices',
            '/traffic-analytics',
            '/hotspot/profiles',
            '/hotspot/users',
            '/hotspot/generate',
            '/vouchers/print/58mm',
            '/vouchers/print/80mm',
            '/vouchers/print/grid',
            '/pos',
            '/pos/vouchers',
            '/pos/monthly',
            '/pos/shifts',
            '/settings/router',
            '/settings/profile',
            '/settings/templates',
            '/settings/maintenance',
            '/audit-logs',
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }

        // Test Profile CRUD elements and optional rate_limit & selling_price & unlimited shared_users
        $profileRes = $this->get('/hotspot/profiles');
        $profileRes->assertSee('Sinkron dari Router');

        $createFreeProfileRes = $this->postJson('/hotspot/profiles', [
            'name' => 'FREE_UNLIMITED_PROFILE',
            'rate_limit' => '', // blank = unlimited
            'selling_price' => '', // blank = 0 / free
            'shared_users' => 0, // 0 = unlimited devices
            'validity' => 'Unlimited',
        ]);
        $createFreeProfileRes->assertStatus(200);
        $this->assertDatabaseHas('hotspot_profiles', [
            'name' => 'FREE_UNLIMITED_PROFILE',
            'rate_limit' => null,
            'selling_price' => 0,
            'shared_users' => 0,
        ]);

        $createdProf = \App\Models\HotspotProfile::where('name', 'FREE_UNLIMITED_PROFILE')->first();
        $updateProfRes = $this->putJson('/hotspot/profiles/' . $createdProf->id, [
            'name' => 'FREE_UNLIMITED_PROFILE',
            'rate_limit' => null,
            'selling_price' => 0,
            'shared_users' => 0,
        ]);
        $updateProfRes->assertStatus(200);
        $this->assertDatabaseHas('hotspot_profiles', [
            'id' => $createdProf->id,
            'shared_users' => 0,
            'rate_limit' => null,
            'selling_price' => 0,
        ]);

        // Test User CRUD elements
        $userRes = $this->get('/hotspot/users');
        $userRes->assertSee('Sinkron dari Router');

        // Test Monthly Customer CRUD elements
        $monthlyRes = $this->get('/pos/monthly');
        $monthlyRes->assertSee('Tambah Customer');
    }

    public function test_pages_render_without_intl_with_usage_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create daily summary
        \App\Models\DailyUserUsageSummary::create([
            'username' => 'testuser',
            'usage_date' => now()->toDateString(),
            'total_bytes_in' => 524288000,
            'total_bytes_out' => 1073741824,
            'total_bytes' => 1598029824,
            'total_uptime_seconds' => 7200,
            'session_count' => 3,
        ]);

        $dashRes = $this->get('/');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('1.5 GB');

        $histRes = $this->get('/users/historical');
        $histRes->assertStatus(200);
        $histRes->assertSee('1.5 GB');

        $posRes = $this->get('/pos');
        $posRes->assertStatus(200);

        // Test devices page loads cleanly with mobile cards and clean status classification
        $devRes = $this->get('/devices');
        $devRes->assertStatus(200);
        $devRes->assertSee('Live Devices & Koneksi Jaringan', false);
        $devRes->assertSee('Total Perangkat');
        $devRes->assertSee('Online Hotspot');
        $devRes->assertSee('Standby');
        $devRes->assertSee('Offline / Idle', false);

        $devLiveRes = $this->get('/api/devices/live');
        $devLiveRes->assertStatus(200);
        $devLiveRes->assertJsonStructure([
            'devices',
            'stats' => ['total', 'online', 'standby', 'offline'],
            'count',
            'timestamp',
        ]);

        // Test device detail API endpoint with multi-user history and quota accumulation
        $devDetailRes = $this->get('/api/devices/AA:BB:CC:DD:EE:FF/detail');
        $devDetailRes->assertStatus(200);
        $devDetailRes->assertJsonStructure([
            'success',
            'mac_address',
            'vendor',
            'quota_stats' => [
                'total_bytes',
                'total_bytes_formatted',
                'download_formatted',
                'upload_formatted',
                'session_count',
                'unique_users_count',
            ],
            'session_history',
        ]);

        // Test device action endpoints
        $makeStaticRes = $this->postJson('/api/devices/action/make-static', [
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'ip_address' => '192.168.88.50',
            'comment' => 'HP Kasir',
        ]);
        $makeStaticRes->assertStatus(200);
        $makeStaticRes->assertJsonStructure(['success', 'message']);

        $ipBindingRes = $this->postJson('/api/devices/action/ip-binding', [
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'type' => 'bypassed',
            'comment' => 'Bypass Device',
        ]);
        $ipBindingRes->assertStatus(200);
        $ipBindingRes->assertJsonStructure(['success', 'message']);

        $kickRes = $this->postJson('/api/devices/action/kick', [
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'username' => 'testuser',
        ]);
        $kickRes->assertStatus(200);
        $kickRes->assertJsonStructure(['success', 'message']);

        $commentRes = $this->postJson('/api/devices/action/comment', [
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'comment' => 'Laptop Admin',
        ]);
        $commentRes->assertStatus(200);
        $commentRes->assertJsonStructure(['success', 'message']);

        // Test online users page loads cleanly with mobile-friendly cards and live API
        $userOnlineRes = $this->get('/users');
        $userOnlineRes->assertStatus(200);
        $userOnlineRes->assertSee('User Hotspot Online');
        $userOnlineRes->assertSee('Detail User');
        $userOnlineRes->assertSee('Putus / Kick', false);

        $userLiveRes = $this->get('/users/api/live');
        $userLiveRes->assertStatus(200);
        $userLiveRes->assertJsonStructure([
            'sessions',
            'count',
            'timestamp',
        ]);

        // Test user detail page with filters
        $userDetailRes = $this->get('/users/testuser?period=7days');
        $userDetailRes->assertStatus(200);
        $userDetailRes->assertSee('Filter Periode & Riwayat', false);
        $userDetailRes->assertSee('7 Hari');
        $userDetailRes->assertSee('Semua Waktu');

        // Test single-day hourly breakdown chart on Historical Usage
        $histTodayRes = $this->get('/users/historical?period=today');
        $histTodayRes->assertStatus(200);
        $histTodayRes->assertSee('Grafik Konsumsi Data Per-Jam (24 Jam: 00:00 - 23:00)', false);
        $histTodayRes->assertSee('"00:00"', false);
        $histTodayRes->assertSee('"12:00"', false);
        $histTodayRes->assertSee('"23:00"', false);

        // Test single-day hourly breakdown chart on User Detail
        $userTodayRes = $this->get('/users/testuser?period=today');
        $userTodayRes->assertStatus(200);
        $userTodayRes->assertSee('Grafik Konsumsi Kuota User (24 Jam: 00:00 - 23:00)', false);
        $userTodayRes->assertSee('"00:00"', false);
        $userTodayRes->assertSee('"23:00"', false);

        // Test Vouchers Inventory List Page
        $vouchersRes = $this->get('/vouchers');
        $vouchersRes->assertStatus(200);
        $vouchersRes->assertSee('Daftar & Cetak Voucher', false);
        $vouchersRes->assertSee('Total Voucher');
        $vouchersRes->assertSee('Nilai Inventory');

        // Create dummy vouchers for print and delete tests
        $v1 = \App\Models\HotspotUser::create([
            'username' => 'TESTVC01',
            'password' => 'TESTVC01',
            'comment' => 'Batch 2026-08-30 Test',
            'is_active' => true,
        ]);
        $v2 = \App\Models\HotspotUser::create([
            'username' => 'TESTVC02',
            'password' => '1234',
            'comment' => 'Batch 2026-08-30 Test',
            'is_active' => true,
        ]);

        // Re-request /vouchers to verify batch query execution with data
        $vouchersWithBatchesRes = $this->get('/vouchers');
        $vouchersWithBatchesRes->assertStatus(200);
        $vouchersWithBatchesRes->assertSee('Batch 2026-08-30 Test');

        // Test selective print grid with real QR Code
        $printGridRes = $this->get('/vouchers/print/grid?ids=' . $v1->id . ',' . $v2->id);
        $printGridRes->assertStatus(200);
        $printGridRes->assertSee('TESTVC01');
        $printGridRes->assertSee('TESTVC02');
        $printGridRes->assertSee('shape-rendering="crispEdges"', false);

        // Test selective thermal print 58mm & 80mm with real QR Code & Barcode
        $print58Res = $this->get('/vouchers/print/58mm?ids=' . $v1->id . ',' . $v2->id);
        $print58Res->assertStatus(200);
        $print58Res->assertSee('TESTVC01');
        $print58Res->assertSee('TESTVC02');
        $print58Res->assertSee('Scan QR untuk Login Otomatis');
        $print58Res->assertSee('shape-rendering="crispEdges"', false);

        $print80Res = $this->get('/vouchers/print/80mm?ids=' . $v1->id);
        $print80Res->assertStatus(200);
        $print80Res->assertSee('TESTVC01');
        $print80Res->assertSee('shape-rendering="crispEdges"', false);

        // Test batch delete vouchers
        $deleteBatchRes = $this->postJson('/vouchers/batch-delete', [
            'ids' => [$v1->id, $v2->id],
        ]);
        $deleteBatchRes->assertStatus(200);
        $deleteBatchRes->assertJsonStructure(['success', 'message', 'deleted_count']);
        $this->assertDatabaseMissing('hotspot_users', ['id' => $v1->id]);
        $this->assertDatabaseMissing('hotspot_users', ['id' => $v2->id]);

        // Test Inactive vs Active status filter on Hotspot Users & Vouchers
        $activeUser = \App\Models\HotspotUser::create([
            'username' => 'ALPHA_ACTIVE',
            'password' => '1234',
            'is_active' => true,
        ]);
        $inactiveUser = \App\Models\HotspotUser::create([
            'username' => 'BETA_INACTIVE',
            'password' => '1234',
            'is_active' => false,
        ]);

        // Default vouchers index shows active only
        $vouchersActiveOnly = $this->get('/vouchers');
        $vouchersActiveOnly->assertStatus(200);
        $vouchersActiveOnly->assertSee('ALPHA_ACTIVE');
        $vouchersActiveOnly->assertDontSee('BETA_INACTIVE');

        // Filter inactive vouchers
        $vouchersInactiveOnly = $this->get('/vouchers?status=inactive');
        $vouchersInactiveOnly->assertStatus(200);
        $vouchersInactiveOnly->assertSee('BETA_INACTIVE');
        $vouchersInactiveOnly->assertDontSee('ALPHA_ACTIVE');

        // Default hotspot users page shows active only
        $hotspotUsersActive = $this->get('/hotspot/users');
        $hotspotUsersActive->assertStatus(200);
        $hotspotUsersActive->assertSee('ALPHA_ACTIVE');
        $hotspotUsersActive->assertDontSee('BETA_INACTIVE');

        // Filter inactive hotspot users
        $hotspotUsersInactive = $this->get('/hotspot/users?status=inactive');
        $hotspotUsersInactive->assertStatus(200);
        $hotspotUsersInactive->assertSee('BETA_INACTIVE');
        $hotspotUsersInactive->assertDontSee('ALPHA_ACTIVE');
    }

    public function test_dashboard_and_historical_usage_consistency(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // User A was online earlier (in DB only) = 500 MB
        \App\Models\DailyUserUsageSummary::create([
            'username' => 'user_a',
            'usage_date' => now()->toDateString(),
            'total_bytes_in' => 200 * 1024 * 1024,
            'total_bytes_out' => 300 * 1024 * 1024,
            'total_bytes' => 500 * 1024 * 1024,
            'total_uptime_seconds' => 3600,
            'session_count' => 1,
        ]);

        // User B was also logged in today = 200 MB
        \App\Models\DailyUserUsageSummary::create([
            'username' => 'user_b',
            'usage_date' => now()->toDateString(),
            'total_bytes_in' => 50 * 1024 * 1024,
            'total_bytes_out' => 150 * 1024 * 1024,
            'total_bytes' => 200 * 1024 * 1024,
            'total_uptime_seconds' => 1800,
            'session_count' => 1,
        ]);

        // Total = 700 MB
        $dashRes = $this->get('/');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('700 MB');

        $histRes = $this->get('/users/historical?period=today');
        $histRes->assertStatus(200);
        $histRes->assertSee('700 MB');
    }

    public function test_dashboard_renders_detailed_router_status_and_health_panel(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        \App\Models\RouterSetting::create([
            'name' => 'MikroTik Gateway Utama',
            'host' => '192.168.88.1',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'secret',
            'is_active' => true,
            'last_successful_poll_at' => now(),
        ]);

        $dashRes = $this->get('/');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('Kesehatan & Spesifikasi Hardware Router', false);
        $dashRes->assertSee('Beban CPU');
        $dashRes->assertSee('Pemakaian Memori (RAM)');
        $dashRes->assertSee('Uptime & Storage', false);
        $dashRes->assertSee('192.168.88.1');

        $apiRes = $this->get('/api/dashboard/live');
        $apiRes->assertStatus(200);
        $apiRes->assertJsonStructure([
            'online_users',
            'download_rate_mbps',
            'upload_rate_mbps',
            'router_info' => [
                'is_online',
                'name',
                'board_name',
                'version',
                'cpu_load',
                'free_memory',
                'total_memory',
                'uptime',
                'host',
                'api_port'
            ]
        ]);
    }

    public function test_profile_business_settings_preserved_during_router_sync(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Create a profile with specific business prices and settings
        $profile = \App\Models\HotspotProfile::create([
            'name' => 'PREMIUM_10MBPS',
            'rate_limit' => '10M/10M',
            'shared_users' => 3,
            'selling_price' => 25000,
            'cost_price' => 12000,
            'validity' => '15 Hari',
            'expired_mode' => 'Notice',
            'is_active' => true,
        ]);

        // 2. Setup active router setting and mock RouterOS returning this profile and a brand new profile
        \App\Models\RouterSetting::firstOrCreate(
            ['host' => '192.168.88.1'],
            [
                'name' => 'MikroTik Gateway Test',
                'api_port' => 8728,
                'username' => 'admin',
                'password' => 'secret',
                'is_active' => true,
            ]
        );

        $this->mock(\App\Services\RouterOsService::class, function ($mock) {
            $mock->shouldReceive('testConnection')->andReturn(['success' => true, 'message' => 'Connected']);
            $mock->shouldReceive('getHotspotProfiles')->andReturn([
                [
                    '.id' => '*1',
                    'name' => 'PREMIUM_10MBPS',
                    'rate-limit' => '10M/10M',
                    'shared-users' => '3',
                ],
                [
                    '.id' => '*2',
                    'name' => 'NEW_WINBOX_PROFILE',
                    'rate-limit' => '2M/2M',
                    'shared-users' => '1',
                ],
            ]);
            $mock->shouldReceive('getHotspotUsers')->andReturn([]);
            $mock->shouldReceive('getActiveHotspotSessions')->andReturn([]);
            $mock->shouldReceive('getDhcpLeases')->andReturn([]);
        });

        // 3. Trigger manual sync from Hotspot Profile Controller
        $syncRes = $this->postJson('/hotspot/profiles/sync');
        $syncRes->assertStatus(200);

        // 4. Assert that PREMIUM_10MBPS kept its custom selling_price, cost_price, validity, expired_mode
        $refreshed = \App\Models\HotspotProfile::where('name', 'PREMIUM_10MBPS')->first();
        $this->assertEquals(25000, (float) $refreshed->selling_price, 'Selling price must not be reset to 0');
        $this->assertEquals(12000, (float) $refreshed->cost_price, 'Cost price must not be reset to 0');
        $this->assertEquals('15 Hari', $refreshed->validity, 'Validity must not be reset');
        $this->assertEquals('Notice', $refreshed->expired_mode, 'Expired mode must not be reset to Remove');
        $this->assertEquals(3, $refreshed->shared_users);

        // 5. Trigger sync from Router Setting Controller
        $routerSyncRes = $this->postJson('/settings/router/sync');
        $routerSyncRes->assertStatus(200);

        $refreshedAgain = \App\Models\HotspotProfile::where('name', 'PREMIUM_10MBPS')->first();
        $this->assertEquals(25000, (float) $refreshedAgain->selling_price, 'Selling price must remain 25000 after router/sync');
        $this->assertEquals('Notice', $refreshedAgain->expired_mode, 'Expired mode must remain Notice after router/sync');

        // Check that NEW_WINBOX_PROFILE was created with safe defaults
        $newProf = \App\Models\HotspotProfile::where('name', 'NEW_WINBOX_PROFILE')->first();
        $this->assertNotNull($newProf);
        $this->assertEquals('2M/2M', $newProf->rate_limit);
        $this->assertEquals(0, (float) $newProf->selling_price);
    }

    public function test_uptime_remaining_calculation_and_live_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Test FormatHelper::getUptimeProgress with limit
        $progress = \App\Support\FormatHelper::getUptimeProgress('30m', '3 Jam');
        $this->assertTrue($progress['has_limit']);
        $this->assertEquals(1800, $progress['used_seconds']);
        $this->assertEquals(10800, $progress['limit_seconds']);
        $this->assertEquals(9000, $progress['remaining_seconds']);
        $this->assertEquals('2 jam 30 mnt', $progress['remaining_formatted']);
        $this->assertEquals(83, $progress['remaining_percent']);

        // 2. Test FormatHelper::getUptimeProgress with unlimited
        $unlimited = \App\Support\FormatHelper::getUptimeProgress('2h 15m', 'Unlimited');
        $this->assertFalse($unlimited['has_limit']);
        $this->assertNull($unlimited['remaining_formatted']);

        // 3. Test Live User Polling endpoint returns remaining uptime
        $profile = \App\Models\HotspotProfile::create([
            'name' => 'PAKET_3_JAM',
            'validity' => '3 Jam',
            'selling_price' => 5000,
            'is_active' => true,
        ]);

        $hotspotUser = \App\Models\HotspotUser::create([
            'username' => 'testuser-voucher',
            'password' => '123456',
            'profile_id' => $profile->id,
            'is_active' => true,
        ]);

        $this->mock(\App\Services\RouterOsService::class, function ($mock) {
            $mock->shouldReceive('getActiveHotspotSessions')->andReturn([
                [
                    '.id' => '*10',
                    'user' => 'testuser-voucher',
                    'address' => '192.168.88.50',
                    'mac-address' => 'AA:BB:CC:DD:EE:FF',
                    'uptime' => '45m00s',
                    'bytes-in' => 10485760,
                    'bytes-out' => 52428800,
                    'rx-rate' => 1000000,
                    'tx-rate' => 2000000,
                ]
            ]);
        });

        $res = $this->getJson(route('users.user.live', 'testuser-voucher'));
        $res->assertStatus(200);
        $res->assertJson([
            'is_online' => true,
            'has_limit' => true,
            'remaining_uptime' => '2 jam 15 mnt',
            'remaining_percentage' => 75,
        ]);
    }

    public function test_voucher_expiry_service_remove_and_notice_modes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Create Profile with Expired Mode = 'Remove'
        $profRemove = \App\Models\HotspotProfile::create([
            'name' => 'EXP_REMOVE_PROF',
            'validity' => '1 Jam',
            'selling_price' => 3000,
            'cost_price' => 1000,
            'expired_mode' => 'Remove',
            'is_active' => true,
        ]);

        $voucherRemove = \App\Models\HotspotUser::create([
            'username' => 'vc-remove-01',
            'password' => '111',
            'profile_id' => $profRemove->id,
            'uptime_limit' => '1h',
            'is_active' => true,
        ]);

        // 2. Create Profile with Expired Mode = 'Notice'
        $profNotice = \App\Models\HotspotProfile::create([
            'name' => 'EXP_NOTICE_PROF',
            'validity' => '30 Menit',
            'selling_price' => 2000,
            'cost_price' => 500,
            'expired_mode' => 'Notice',
            'is_active' => true,
        ]);

        $voucherNotice = \App\Models\HotspotUser::create([
            'username' => 'vc-notice-02',
            'password' => '222',
            'profile_id' => $profNotice->id,
            'uptime_limit' => '30m',
            'is_active' => true,
        ]);

        // Mock RouterOS returning both users where vc-remove-01 has used 1h (expired) and vc-notice-02 has used 30m (expired)
        $mock = \Mockery::mock(\App\Services\RouterOsService::class);
        $mock->shouldReceive('getHotspotUsers')->andReturn([
            [
                '.id' => '*1',
                'name' => 'vc-remove-01',
                'uptime' => '1h00m00s',
                'limit-uptime' => '1h',
            ],
            [
                '.id' => '*2',
                'name' => 'vc-notice-02',
                'uptime' => '30m00s',
                'limit-uptime' => '30m',
            ],
        ]);
        $mock->shouldReceive('removeHotspotUser')->with('vc-remove-01')->once()->andReturn(true);
        $mock->shouldReceive('disconnectHotspotUser')->with('vc-remove-01')->andReturn(true);
        $mock->shouldReceive('setHotspotUserStatus')->with('vc-notice-02', false)->once()->andReturn(true);
        $mock->shouldReceive('disconnectHotspotUser')->with('vc-notice-02')->andReturn(true);

        $expiryService = new \App\Services\VoucherExpiryService($mock);
        $result = $expiryService->sweepExpiredUsers();

        $this->assertEquals(2, $result['processed']);
        $this->assertEquals(1, $result['removed']);
        $this->assertEquals(1, $result['disabled']);

        // Assert vc-remove-01 is deleted from DB
        $this->assertNull(\App\Models\HotspotUser::where('username', 'vc-remove-01')->first());

        // Assert sale was recorded in VoucherSale
        $sale = \App\Models\VoucherSale::where('username', 'vc-remove-01')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(3000, (float) $sale->selling_price);

        // Assert vc-notice-02 is NOT deleted, but is_active = false
        $noticeUser = \App\Models\HotspotUser::where('username', 'vc-notice-02')->first();
        $this->assertNotNull($noticeUser);
        $this->assertFalse((bool) $noticeUser->is_active);
    }

    public function test_fup_evaluation_throttles_user_and_manual_reset(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Create Profile with FUP Enabled (Limit: 5 GB, Rate Limit: 1M/1M)
        $fupProfile = \App\Models\HotspotProfile::create([
            'name' => 'FUP_5GB_PROF',
            'rate_limit' => '10M/10M',
            'validity' => '30 Hari',
            'selling_price' => 50000,
            'fup_enabled' => true,
            'fup_limit_display' => '5 GB',
            'fup_limit_bytes' => 5 * 1024 * 1024 * 1024,
            'fup_rate_limit' => '1M/1M',
            'fup_reset_cycle' => 'daily',
            'is_active' => true,
        ]);

        $fupUser = \App\Models\HotspotUser::create([
            'username' => 'fup-user-01',
            'password' => 'pass123',
            'profile_id' => $fupProfile->id,
            'fup_usage_bytes' => 4 * 1024 * 1024 * 1024, // 4 GB used
            'fup_active' => false,
            'is_active' => true,
        ]);

        $mock = \Mockery::mock(\App\Services\RouterOsService::class);
        $mock->shouldReceive('updateSimpleQueueRateLimit')->with('fup-user-01', '1M/1M')->once()->andReturn(true);

        $fupService = new \App\Services\FupManagementService($mock);

        // 2. Add delta that pushes usage over 5 GB threshold (add 1.5 GB)
        $delta = (int) (1.5 * 1024 * 1024 * 1024);
        $throttled = $fupService->evaluateUserFup($fupUser, $delta, $mock);

        $this->assertTrue($throttled);
        $fupUser->refresh();
        $this->assertTrue((bool) $fupUser->fup_active, 'User must be marked fup_active = true');
        $this->assertNotNull($fupUser->fup_triggered_at);

        // 3. Check User Progress payload
        $progress = $fupService->getUserFupProgress($fupUser);
        $this->assertTrue($progress['fup_enabled']);
        $this->assertTrue($progress['fup_active']);
        $this->assertEquals('1M/1M', $progress['fup_rate_limit']);
        $this->assertEquals('10M/10M', $progress['normal_rate_limit']);

        // 4. Test Manual Reset via HTTP Endpoint
        $this->mock(\App\Services\RouterOsService::class, function ($m) {
            $m->shouldReceive('updateSimpleQueueRateLimit')->with('fup-user-01', '10M/10M')->once()->andReturn(true);
        });

        $res = $this->postJson(route('users.reset-fup', 'fup-user-01'));
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $fupUser->refresh();
        $this->assertFalse((bool) $fupUser->fup_active, 'User must be restored from FUP active state');
        $this->assertEquals(0, $fupUser->fup_usage_bytes);
    }

    public function test_currency_model_serialization_and_profile_price_update(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create profile with selling price = 5000 and cost price = 2000
        $profile = \App\Models\HotspotProfile::create([
            'name' => 'PAKET_5K',
            'validity' => '3 Jam',
            'selling_price' => 5000,
            'cost_price' => 2000,
            'is_active' => true,
        ]);

        // When converted to array / JSON, selling_price must be numeric float (5000), not string ("5000.00")
        $json = $profile->toArray();
        $this->assertSame(5000.0, (float) $json['selling_price']);
        $this->assertIsFloat($json['selling_price']);

        // Update profile with selling_price = 5000
        $this->mock(\App\Services\RouterOsService::class, function ($m) {
            $m->shouldReceive('updateHotspotProfile')->andReturn(true);
        });

        $res = $this->putJson("/hotspot/profiles/{$profile->id}", [
            'name' => 'PAKET_5K',
            'selling_price' => 5000,
            'cost_price' => 2000,
            'validity' => '3 Jam',
            'shared_users' => 1,
        ]);

        $res->assertStatus(200);
        $res->assertJsonPath('profile.selling_price', 5000);

        $profile->refresh();
        $this->assertEquals(5000, $profile->selling_price);
    }

    public function test_pos_monthly_customers_hotspot_linking_and_voucher_metrics(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Create a Hotspot User with Profile
        $profile = \App\Models\HotspotProfile::create([
            'name' => 'PAKET_RUMAH_10M',
            'selling_price' => 150000,
            'validity' => '30 Hari',
            'is_active' => true,
        ]);

        $hotspotUser = \App\Models\HotspotUser::create([
            'username' => 'budi-rumah-01',
            'password' => 'pass123',
            'profile_id' => $profile->id,
            'is_active' => true,
        ]);

        // 2. Create Monthly Customer linked to Hotspot User
        $res = $this->postJson(route('pos.monthly.customers.store'), [
            'name' => 'Budi Santoso',
            'hotspot_user_id' => $hotspotUser->id,
            'monthly_price' => 150000,
            'billing_day' => 5,
            'contact' => '08123456789',
            'address' => 'Blok B No 12',
        ]);

        $res->assertStatus(200);
        $res->assertJson(['success' => true]);
        $customerId = $res->json('customer.id');

        $customer = \App\Models\MonthlyCustomer::with('hotspotUser.profile')->find($customerId);
        $this->assertNotNull($customer);
        $this->assertEquals('Budi Santoso', $customer->name);
        $this->assertEquals($hotspotUser->id, $customer->hotspot_user_id);
        $this->assertEquals('budi-rumah-01', $customer->hotspotUser->username);

        // 3. Test POS Monthly page returns customers and hotspotUsers
        $pageRes = $this->get(route('pos.monthly'));
        $pageRes->assertStatus(200);
        $pageRes->assertViewHas('customers');
        $pageRes->assertViewHas('hotspotUsers');

        // 4. Test POS Vouchers page returns metrics
        $voucherSale = \App\Models\VoucherSale::create([
            'username' => 'vc-pos-001',
            'profile_name' => 'PAKET_RUMAH_10M',
            'cost_price' => 50000,
            'selling_price' => 150000,
            'profit' => 100000,
            'activated_at' => now(),
            'status' => 'completed',
        ]);

        $vouchersRes = $this->get(route('pos.vouchers'));
        $vouchersRes->assertStatus(200);
        $vouchersRes->assertViewHas('metrics');
        $vouchersRes->assertViewHas('sales');
        $metrics = $vouchersRes->viewData('metrics');
        $this->assertGreaterThanOrEqual(1, $metrics['total_count']);
        $this->assertGreaterThanOrEqual(150000, $metrics['total_revenue']);
    }

    public function test_monthly_invoicing_batch_generate_and_installments(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Create 2 active customers
        $c1 = \App\Models\MonthlyCustomer::create([
            'name' => 'Pelanggan Alpha',
            'monthly_price' => 150000,
            'billing_day' => 5,
            'is_active' => true,
        ]);

        $c2 = \App\Models\MonthlyCustomer::create([
            'name' => 'Pelanggan Beta',
            'monthly_price' => 200000,
            'billing_day' => 10,
            'is_active' => true,
        ]);

        // 2. Test Batch Invoices Generation for 2026-09
        $genRes = $this->postJson(route('pos.monthly.invoices.generate'), [
            'billing_month' => '2026-09',
        ]);
        $genRes->assertStatus(200);
        $genRes->assertJson([
            'success' => true,
            'generated' => 2,
            'skipped' => 0,
        ]);

        // Assert 2 invoices created in DB
        $invoices = \App\Models\MonthlyInvoice::whereDate('billing_month', '2026-09-01')->get();
        $this->assertCount(2, $invoices);
        $inv1 = $invoices->where('monthly_customer_id', $c1->id)->first();
        $this->assertNotNull($inv1);
        $this->assertEquals(150000, (float) $inv1->amount);
        $this->assertEquals(150000, (float) $inv1->balance_due);
        $this->assertEquals('unpaid', $inv1->status);

        // 3. Test Generating again skips already generated invoices (no duplicates)
        $genRes2 = $this->postJson(route('pos.monthly.invoices.generate'), [
            'billing_month' => '2026-09',
        ]);
        $genRes2->assertStatus(200);
        $genRes2->assertJson([
            'success' => true,
            'generated' => 0,
            'skipped' => 2,
        ]);

        // 4. Test Partial Payment (Angsuran #1: Rp 50.000)
        $payRes1 = $this->postJson(route('pos.monthly.invoices.pay', $inv1->id), [
            'amount_paid' => 50000,
            'paid_at' => '2026-09-02',
            'payment_method' => 'transfer',
            'notes' => 'Angsuran 1',
        ]);
        $payRes1->assertStatus(200);
        $payRes1->assertJson(['success' => true]);

        $inv1->refresh();
        $this->assertEquals(50000, (float) $inv1->amount_paid);
        $this->assertEquals(100000, (float) $inv1->balance_due);
        $this->assertEquals('partial', $inv1->status);

        // 5. Test Settlement Payment (Pelunasan: Rp 100.000)
        $payRes2 = $this->postJson(route('pos.monthly.invoices.pay', $inv1->id), [
            'amount_paid' => 100000,
            'paid_at' => '2026-09-15',
            'payment_method' => 'cash',
            'notes' => 'Pelunasan sisa',
        ]);
        $payRes2->assertStatus(200);

        $inv1->refresh();
        $this->assertEquals(150000, (float) $inv1->amount_paid);
        $this->assertEquals(0, (float) $inv1->balance_due);
        $this->assertEquals('paid', $inv1->status);

        // 6. Test VOID Payment #2 restores balance and partial status
        $payment2 = \App\Models\MonthlyPayment::where('monthly_invoice_id', $inv1->id)
            ->where('notes', 'Pelunasan sisa')
            ->first();
        $this->assertNotNull($payment2);

        $voidRes = $this->postJson(route('pos.monthly.payments.void', $payment2->id), [
            'reason' => 'Salah input',
        ]);
        $voidRes->assertStatus(200);

        $inv1->refresh();
        $this->assertEquals(50000, (float) $inv1->amount_paid);
        $this->assertEquals(100000, (float) $inv1->balance_due);
        $this->assertEquals('partial', $inv1->status);
    }

    public function test_pos_executive_dashboard_and_monthly_profit_calculations(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Create a customer with monthly price & cost price
        $cust = \App\Models\MonthlyCustomer::create([
            'name' => 'Pelanggan Rumah Fiber',
            'monthly_price' => 200000,
            'cost_price' => 75000, // Upstream modal
            'billing_day' => 1,
            'is_active' => true,
        ]);

        // Generate invoice for current month
        $monthStr = now()->format('Y-m');
        $genRes = $this->postJson(route('pos.monthly.invoices.generate'), [
            'billing_month' => $monthStr,
        ]);
        $genRes->assertStatus(200);

        $invoice = \App\Models\MonthlyInvoice::where('monthly_customer_id', $cust->id)
            ->whereDate('billing_month', now()->startOfMonth()->toDateString())
            ->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(75000, (float) $invoice->cost_price);

        // Pay the invoice in full
        $this->postJson(route('pos.monthly.invoices.pay', $invoice->id), [
            'amount_paid' => 200000,
            'paid_at' => now()->toDateString(),
            'payment_method' => 'transfer',
            'notes' => 'Transfer BCA',
        ]);

        // 2. Create a voucher sale
        \App\Models\VoucherSale::create([
            'username' => 'vc-dash-01',
            'profile_name' => 'PAKET_5K_3JAM',
            'cost_price' => 2000,
            'selling_price' => 5000,
            'profit' => 3000,
            'activated_at' => now(),
            'status' => 'completed',
        ]);

        // 3. Test POS Executive Dashboard Overview
        $res = $this->get(route('pos.index', ['month' => $monthStr]));
        $res->assertStatus(200);
        $res->assertSee('Dashboard Finansial');
        $res->assertSee('Total Pemasukan Kas');
        $res->assertSee('Total Laba Bersih');
        $res->assertSee('Tren Arus Kas');

        $overview = $res->viewData('overview');
        $this->assertNotNull($overview);

        // Assert Monthly Profit = 200.000 - 75.000 = 125.000
        $this->assertEquals(200000, (float) $overview['monthly_paid']);
        $this->assertEquals(75000, (float) $overview['monthly_cost']);
        $this->assertEquals(125000, (float) $overview['monthly_profit']);

        // Assert Voucher Profit = 3.000
        $this->assertEquals(5000, (float) $overview['voucher_revenue']);
        $this->assertEquals(3000, (float) $overview['voucher_profit']);

        // Assert Total Gross = 205.000, Total Net Profit = 128.000
        $this->assertEquals(205000, (float) $overview['total_gross_income']);
        $this->assertEquals(128000, (float) $overview['total_net_profit']);

        // Assert Chart Series Exist
        $this->assertNotEmpty($overview['chart_categories']);
        $this->assertNotEmpty($overview['chart_voucher_series']);
        $this->assertNotEmpty($overview['chart_monthly_series']);
        $this->assertNotEmpty($overview['chart_profit_series']);
    }
}







