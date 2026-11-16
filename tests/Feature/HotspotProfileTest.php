<?php

namespace Tests\Feature;

use App\Models\HotspotProfile;
use App\Models\User;
use App\Services\RouterOsService;
use Mockery;
use Tests\TestCase;

class HotspotProfileTest extends TestCase
{
    public function test_can_create_profile_with_fup_percentage_and_percentage_cost_price(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('addHotspotProfile')
            ->once()
            ->with('PROFIT_10M', '10M/10M', 1)
            ->andReturn(true);
        $this->app->instance(RouterOsService::class, $mockRouter);

        $response = $this->postJson(route('hotspot.profiles.store'), [
            'name' => 'PROFIT_10M',
            'category' => 'voucher',
            'rate_limit' => '10M/10M',
            'shared_users' => 1,
            'validity' => '12h',
            'cost_price_type' => 'percentage',
            'cost_price_percent' => 20,
            'selling_price' => 10000,
            'expired_mode' => 'Notice & Record',
            'fup_enabled' => true,
            'fup_limit_display' => '5GB',
            'fup_rate_percent' => 50,
            'fup_reset_cycle' => 'daily',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $profile = HotspotProfile::where('name', 'PROFIT_10M')->first();
        $this->assertNotNull($profile);
        $this->assertEquals('voucher', $profile->category);
        $this->assertEquals('percentage', $profile->cost_price_type);
        $this->assertEquals(20.0, $profile->cost_price_percent);
        $this->assertEquals(2000.0, $profile->cost_price); // 20% of 10000
        $this->assertEquals('Notice & Record', $profile->expired_mode);
        $this->assertEquals('12h', $profile->validity);
        $this->assertEquals(50, $profile->fup_rate_percent);
        $this->assertEquals('5M/5M', $profile->fup_rate_limit); // 50% of 10M/10M
    }

    public function test_can_create_pribadi_profile_with_custom_cost_price(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('addHotspotProfile')
            ->once()
            ->with('PRIBADI_ADMIN', '20M/20M', 2)
            ->andReturn(true);
        $this->app->instance(RouterOsService::class, $mockRouter);

        $response = $this->postJson(route('hotspot.profiles.store'), [
            'name' => 'PRIBADI_ADMIN',
            'category' => 'pribadi',
            'rate_limit' => '20M/20M',
            'shared_users' => 2,
            'validity' => '30d',
            'cost_price_type' => 'custom',
            'cost_price' => 0,
            'selling_price' => 0,
            'expired_mode' => 'Notice',
        ]);

        $response->assertStatus(200);
        $profile = HotspotProfile::where('name', 'PRIBADI_ADMIN')->first();
        $this->assertNotNull($profile);
        $this->assertEquals('pribadi', $profile->category);
        $this->assertEquals(0, $profile->cost_price);
        $this->assertEquals(0, $profile->selling_price);
    }

    public function test_hotspot_profiles_view_renders_with_enhanced_ui_elements(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getHotspotProfiles')->andReturn([]);
        $this->app->instance(RouterOsService::class, $mockRouter);

        $response = $this->get(route('hotspot.profiles'));
        $response->assertStatus(200);

        // 1. Single plus icon in Tambah Profile button (no duplicate text "+")
        $response->assertSee('<span>Tambah Profile</span>', false);
        $response->assertDontSee('<span>+ Tambah Profile</span>', false);

        // 2. View modes and Toolbar
        $response->assertSee('Grid');
        $response->assertSee('Tabel');
        $response->assertSee('Semua Kategori');
        $response->assertSee('Semua Status');

        // 3. Modals and Options
        $response->assertSee('Notice & Record', false);
        $response->assertSee('12 Jam (12h)');
        $response->assertSee('Persentase (%) dari Harga Jual');
        $response->assertSee('Voucher (Profit)');
        $response->assertSee('Bulanan (Profit)');
        $response->assertSee('Pribadi (Non-Profit)');
    }

    public function test_can_create_bulanan_profile_and_legacy_profit_maps_to_voucher(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('addHotspotProfile')
            ->twice()
            ->andReturn(true);
        $this->app->instance(RouterOsService::class, $mockRouter);

        // 1. Bulanan profile
        $resBulanan = $this->postJson(route('hotspot.profiles.store'), [
            'name' => 'BULANAN_HOME_150K',
            'category' => 'bulanan',
            'rate_limit' => '10M/10M',
            'shared_users' => 3,
            'validity' => '30d',
            'cost_price' => 50000,
            'selling_price' => 150000,
        ]);
        $resBulanan->assertStatus(200);
        $this->assertDatabaseHas('hotspot_profiles', [
            'name' => 'BULANAN_HOME_150K',
            'category' => 'bulanan',
            'selling_price' => 150000,
            'cost_price' => 50000,
        ]);

        // 2. Legacy 'profit' maps to 'voucher'
        $resLegacy = $this->postJson(route('hotspot.profiles.store'), [
            'name' => 'LEGACY_PROFIT_TEST',
            'category' => 'profit',
            'rate_limit' => '5M/5M',
            'shared_users' => 1,
            'validity' => '24h',
            'selling_price' => 5000,
            'cost_price' => 1000,
        ]);
        $resLegacy->assertStatus(200);
        $this->assertDatabaseHas('hotspot_profiles', [
            'name' => 'LEGACY_PROFIT_TEST',
            'category' => 'voucher',
        ]);
    }

    public function test_voucher_generator_excludes_bulanan_profiles(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getHotspotServers')->andReturn([]);
        $this->app->instance(RouterOsService::class, $mockRouter);

        $pBulanan = HotspotProfile::create([
            'name' => 'PROF_BULANAN_EXCLUDE',
            'category' => 'bulanan',
            'is_active' => true,
        ]);

        $pVoucher = HotspotProfile::create([
            'name' => 'PROF_VOUCHER_INCLUDE',
            'category' => 'voucher',
            'is_active' => true,
        ]);

        $pPribadi = HotspotProfile::create([
            'name' => 'PROF_PRIBADI_INCLUDE',
            'category' => 'pribadi',
            'is_active' => true,
        ]);

        $response = $this->get(route('hotspot.generate'));
        $response->assertStatus(200);

        $response->assertViewHas('profiles', function ($profiles) use ($pBulanan, $pVoucher, $pPribadi) {
            $ids = $profiles->pluck('id')->all();
            return in_array($pVoucher->id, $ids)
                && !in_array($pPribadi->id, $ids)
                && !in_array($pBulanan->id, $ids);
        });

        $response->assertViewHas('allProfiles', function ($allProfiles) use ($pBulanan, $pVoucher, $pPribadi) {
            $ids = $allProfiles->pluck('id')->all();
            return in_array($pVoucher->id, $ids)
                && in_array($pPribadi->id, $ids)
                && in_array($pBulanan->id, $ids);
        });
    }

    public function test_monthly_pos_saves_customer_with_bulanan_profile_and_inherits_prices(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $profile = HotspotProfile::create([
            'name' => 'BULANAN_VIP_200K',
            'category' => 'bulanan',
            'selling_price' => 200000,
            'cost_price' => 75000,
            'is_active' => true,
        ]);

        // 1. Save customer with profile_id
        $resCust = $this->postJson(route('pos.monthly.customers.store'), [
            'name' => 'Budi Santoso',
            'profile_id' => $profile->id,
            'monthly_price' => 200000,
            'cost_price' => 0, // left 0 to test fallback to profile cost_price
            'billing_day' => 5,
        ]);

        $resCust->assertStatus(200);
        $resCust->assertJson(['success' => true]);
        $customer = \App\Models\MonthlyCustomer::where('name', 'Budi Santoso')->first();
        $this->assertNotNull($customer);
        $this->assertEquals($profile->id, $customer->profile_id);

        // 2. Generate monthly invoice and verify fallback to profile's cost_price
        $posService = new \App\Services\PosManagerService();
        $resGen = $posService->generateMonthlyInvoices(now()->format('Y-m'));

        $this->assertTrue($resGen['success']);
        $this->assertEquals(1, $resGen['generated']);
        $invoice = \App\Models\MonthlyInvoice::where('monthly_customer_id', $customer->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(200000.0, $invoice->amount);
        $this->assertEquals(75000.0, $invoice->cost_price); // inherited from profile cost_price
    }

    public function test_voucher_expiry_with_notice_and_record_mode_disables_user_without_deleting(): void
    {
        $profile = HotspotProfile::create([
            'name' => 'PROFILE_NOTICE_RECORD',
            'expired_mode' => 'Notice & Record',
            'validity' => '1h',
            'selling_price' => 5000,
            'cost_price' => 1000,
        ]);

        $user = \App\Models\HotspotUser::create([
            'username' => 'vc-nr-test-01',
            'password' => '1234',
            'profile_id' => $profile->id,
            'is_active' => true,
            'expired_at' => now()->subMinutes(10), // expired 10 minutes ago
        ]);

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getHotspotUsers')->andReturn([
            ['name' => 'vc-nr-test-01', 'profile' => 'PROFILE_NOTICE_RECORD', 'disabled' => 'false']
        ]);
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([]);
        $mockRouter->shouldReceive('setHotspotUserStatus')->with('vc-nr-test-01', false)->andReturn(true);
        $mockRouter->shouldReceive('disconnectHotspotUser')->with('vc-nr-test-01')->andReturn(true);
        $this->app->instance(RouterOsService::class, $mockRouter);

        $expiryService = new \App\Services\VoucherExpiryService();
        $results = $expiryService->sweepExpiredUsers();

        $this->assertEquals(1, $results['disabled']);
        $this->assertEquals(0, $results['removed']);

        // User must NOT be deleted from DB, but set to inactive
        $this->assertDatabaseHas('hotspot_users', [
            'id' => $user->id,
            'username' => 'vc-nr-test-01',
            'is_active' => false,
        ]);

        // Sale must be recorded
        $this->assertDatabaseHas('voucher_sales', [
            'username' => 'vc-nr-test-01',
            'status' => 'completed',
        ]);
    }
}
