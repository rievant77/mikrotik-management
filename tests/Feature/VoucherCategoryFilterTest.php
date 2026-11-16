<?php

namespace Tests\Feature;

use App\Models\HotspotProfile;
use App\Models\HotspotUser;
use App\Models\User;
use App\Services\RouterOsService;
use Mockery;
use Tests\TestCase;

class VoucherCategoryFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([]);
        $mockRouter->shouldReceive('getHotspotUsers')->andReturn([]);
        $mockRouter->shouldReceive('getHotspotServers')->andReturn([]);
        $this->app->instance(RouterOsService::class, $mockRouter);
    }

    public function test_vouchers_index_defaults_to_all_categories_and_displays_badges(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $profVoucher = HotspotProfile::create([
            'name' => 'PKT_VOUCHER_5K',
            'category' => 'voucher',
            'selling_price' => 5000,
            'is_active' => true,
        ]);

        $profBulanan = HotspotProfile::create([
            'name' => 'PKT_BULANAN_100K',
            'category' => 'bulanan',
            'selling_price' => 100000,
            'is_active' => true,
        ]);

        $profPribadi = HotspotProfile::create([
            'name' => 'PKT_PRIBADI_FREE',
            'category' => 'pribadi',
            'selling_price' => 0,
            'is_active' => true,
        ]);

        $u1 = HotspotUser::create([
            'username' => 'vc-vch-001',
            'password' => '111',
            'profile_id' => $profVoucher->id,
            'is_active' => true,
        ]);

        $u2 = HotspotUser::create([
            'username' => 'vc-bln-001',
            'password' => '222',
            'profile_id' => $profBulanan->id,
            'is_active' => true,
        ]);

        $u3 = HotspotUser::create([
            'username' => 'vc-prb-001',
            'password' => '333',
            'profile_id' => $profPribadi->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('vouchers.index'));
        $response->assertStatus(200);

        // All 3 vouchers visible by default
        $response->assertSee('vc-vch-001');
        $response->assertSee('vc-bln-001');
        $response->assertSee('vc-prb-001');

        // Badges rendered
        $response->assertSee('Voucher');
        $response->assertSee('Bulanan');
        $response->assertSee('Pribadi');

        // Dropdown toolbar options
        $response->assertSee('Semua Kategori');
        $response->assertSee('Voucher (Komersial)');
        $response->assertSee('Bulanan (Langganan)');
        $response->assertSee('Pribadi (Non-Profit)');
    }

    public function test_vouchers_index_filters_accurately_by_category(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $profVoucher = HotspotProfile::create([
            'name' => 'FILTER_VOUCHER',
            'category' => 'voucher',
            'selling_price' => 5000,
            'is_active' => true,
        ]);

        $profBulanan = HotspotProfile::create([
            'name' => 'FILTER_BULANAN',
            'category' => 'bulanan',
            'selling_price' => 150000,
            'is_active' => true,
        ]);

        $profPribadi = HotspotProfile::create([
            'name' => 'FILTER_PRIBADI',
            'category' => 'pribadi',
            'selling_price' => 0,
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'u-vch-only',
            'password' => '111',
            'profile_id' => $profVoucher->id,
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'u-bln-only',
            'password' => '222',
            'profile_id' => $profBulanan->id,
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'u-prb-only',
            'password' => '333',
            'profile_id' => $profPribadi->id,
            'is_active' => true,
        ]);

        // Filter 1: Voucher only
        $resVoucher = $this->get(route('vouchers.index', ['category' => 'voucher']));
        $resVoucher->assertStatus(200);
        $resVoucher->assertSee('u-vch-only');
        $resVoucher->assertDontSee('u-bln-only');
        $resVoucher->assertDontSee('u-prb-only');

        // Filter 2: Bulanan only
        $resBulanan = $this->get(route('vouchers.index', ['category' => 'bulanan']));
        $resBulanan->assertStatus(200);
        $resBulanan->assertSee('u-bln-only');
        $resBulanan->assertDontSee('u-vch-only');
        $resBulanan->assertDontSee('u-prb-only');

        // Filter 3: Pribadi only
        $resPribadi = $this->get(route('vouchers.index', ['category' => 'pribadi']));
        $resPribadi->assertStatus(200);
        $resPribadi->assertSee('u-prb-only');
        $resPribadi->assertDontSee('u-vch-only');
        $resPribadi->assertDontSee('u-bln-only');
    }

    public function test_vouchers_inventory_value_excludes_pribadi_and_scopes_by_category(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $profVoucher = HotspotProfile::create([
            'name' => 'INV_VOUCHER',
            'category' => 'voucher',
            'selling_price' => 10000,
            'is_active' => true,
        ]);

        $profPribadi = HotspotProfile::create([
            'name' => 'INV_PRIBADI',
            'category' => 'pribadi',
            'selling_price' => 50000, // even if it has nominal, it must be excluded as 0
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'inv-user-1',
            'password' => '111',
            'profile_id' => $profVoucher->id,
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'inv-user-2',
            'password' => '222',
            'profile_id' => $profPribadi->id,
            'is_active' => true,
        ]);

        // When viewing all categories: inventory value should only count the 10,000 voucher, NOT the 50,000 pribadi
        $resAll = $this->get(route('vouchers.index'));
        $resAll->assertStatus(200);
        $resAll->assertViewHas('stats', function ($stats) {
            return $stats['inventory_value'] === 10000.0;
        });

        // When viewing pribadi category: inventory value is 0.0
        $resPribadi = $this->get(route('vouchers.index', ['category' => 'pribadi']));
        $resPribadi->assertStatus(200);
        $resPribadi->assertViewHas('stats', function ($stats) {
            return $stats['inventory_value'] === 0.0;
        });
    }

    public function test_vouchers_print_grid_respects_category_filter(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $profVoucher = HotspotProfile::create([
            'name' => 'PRINT_VOUCHER',
            'category' => 'voucher',
            'selling_price' => 5000,
            'is_active' => true,
        ]);

        $profBulanan = HotspotProfile::create([
            'name' => 'PRINT_BULANAN',
            'category' => 'bulanan',
            'selling_price' => 100000,
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'print-vch-01',
            'password' => '111',
            'profile_id' => $profVoucher->id,
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'print-bln-01',
            'password' => '222',
            'profile_id' => $profBulanan->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('vouchers.print.grid', ['category' => 'voucher']));
        $response->assertStatus(200);

        $response->assertSee('print-vch-01');
        $response->assertDontSee('print-bln-01');
    }
}
