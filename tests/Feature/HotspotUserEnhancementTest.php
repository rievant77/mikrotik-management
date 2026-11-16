<?php

namespace Tests\Feature;

use App\Models\HotspotProfile;
use App\Models\HotspotUser;
use App\Models\User;
use App\Services\RouterOsService;
use Mockery;
use Tests\TestCase;

class HotspotUserEnhancementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([]);
        $mockRouter->shouldReceive('getHotspotUsers')->andReturn([]);
        $mockRouter->shouldReceive('getHotspotProfiles')->andReturn([]);
        $this->app->instance(RouterOsService::class, $mockRouter);
    }

    public function test_hotspot_users_and_profiles_have_no_redundant_sync_and_single_plus_icon(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Hotspot Users view check
        $resUsers = $this->get(route('hotspot.users'));
        $resUsers->assertStatus(200);

        // Single plus icon (no duplicate '+')
        $resUsers->assertSee('<span>Tambah User</span>', false);
        $resUsers->assertDontSee('<span>+ Tambah User</span>', false);

        // No redundant page-level sync button (navbar handles this)
        $resUsers->assertDontSee('syncUsers()', false);

        // 2. Hotspot Profiles view check
        $resProfiles = $this->get(route('hotspot.profiles'));
        $resProfiles->assertStatus(200);

        // No redundant page-level sync button (navbar handles this)
        $resProfiles->assertDontSee('syncProfiles()', false);
    }

    public function test_hotspot_users_controller_filters_by_category_and_provides_active_sessions_and_pagination(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([
            ['user' => 'user-active-01', 'uptime' => '45m10s', 'session-time-left' => '2h14m50s']
        ]);
        $mockRouter->shouldReceive('getHotspotUsers')->andReturn([]);
        $this->app->instance(RouterOsService::class, $mockRouter);

        $pVoucher = HotspotProfile::create([
            'name' => 'PROF_VOUCHER',
            'category' => 'voucher',
            'selling_price' => 5000,
        ]);
        $pBulanan = HotspotProfile::create([
            'name' => 'PROF_BULANAN',
            'category' => 'bulanan',
            'selling_price' => 150000,
        ]);
        $pPribadi = HotspotProfile::create([
            'name' => 'PROF_PRIBADI',
            'category' => 'pribadi',
            'selling_price' => 0,
        ]);

        $u1 = HotspotUser::create([
            'username' => 'user-active-01',
            'password' => '111',
            'profile_id' => $pVoucher->id,
            'is_active' => true,
        ]);

        $u2 = HotspotUser::create([
            'username' => 'user-bln-01',
            'password' => '222',
            'profile_id' => $pBulanan->id,
            'is_active' => true,
        ]);

        $u3 = HotspotUser::create([
            'username' => 'user-prb-01',
            'password' => '333',
            'profile_id' => $pPribadi->id,
            'is_active' => true,
        ]);

        // 1. Filter Category = Voucher
        $resVoucher = $this->get(route('hotspot.users', ['category' => 'voucher']));
        $resVoucher->assertStatus(200);
        $resVoucher->assertViewHas('users', function ($users) use ($u1, $u2, $u3) {
            $ids = $users->pluck('id')->all();
            return in_array($u1->id, $ids) && !in_array($u2->id, $ids) && !in_array($u3->id, $ids);
        });

        // 2. Active Session Map is passed to view
        $resVoucher->assertViewHas('activeUserMap', function ($map) {
            return isset($map['user-active-01']) && $map['user-active-01']['uptime'] === '45m10s';
        });

        // 3. Filter Category = Bulanan
        $resBulanan = $this->get(route('hotspot.users', ['category' => 'bulanan']));
        $resBulanan->assertStatus(200);
        $resBulanan->assertViewHas('users', function ($users) use ($u1, $u2, $u3) {
            $ids = $users->pluck('id')->all();
            return in_array($u2->id, $ids) && !in_array($u1->id, $ids) && !in_array($u3->id, $ids);
        });

        // 4. Pagination verification: 25 per page
        $resDefault = $this->get(route('hotspot.users'));
        $resDefault->assertStatus(200);
        $resDefault->assertViewHas('users', function ($users) {
            return $users->perPage() === 25;
        });

        // 5. Verify UI Elements: Category dropdown & badges & Uptime
        $resDefault->assertSee('Semua Kategori');
        $resDefault->assertSee('Voucher (Profit)');
        $resDefault->assertSee('Bulanan (Profit)');
        $resDefault->assertSee('Pribadi (Non-Profit)');
        $resDefault->assertSee('Online');
        $resDefault->assertSee('Voucher');
        $resDefault->assertSee('Bulanan');
        $resDefault->assertSee('Pribadi');
    }
}
