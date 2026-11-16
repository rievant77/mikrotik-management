<?php

namespace Tests\Feature;

use App\Models\DailyUserUsageSummary;
use App\Models\HotspotProfile;
use App\Models\HotspotUser;
use App\Models\User;
use App\Services\RouterOsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class HistoricalUsageEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_historical_usage_page_renders_with_category_filter_table_headers_and_pagination(): void
    {
        $user = User::factory()->create();

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([]);
        $mockRouter->shouldReceive('getDhcpLeases')->andReturn([]);
        $this->app->instance(RouterOsService::class, $mockRouter);

        $prof = HotspotProfile::create([
            'name' => '1-Jam-Voucher',
            'category' => 'voucher',
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'test_voucher_1',
            'password' => '1234',
            'profile_id' => $prof->id,
            'is_active' => true,
        ]);

        DailyUserUsageSummary::create([
            'username' => 'test_voucher_1',
            'usage_date' => now()->toDateString(),
            'total_bytes_in' => 1048576,
            'total_bytes_out' => 2097152,
            'total_bytes' => 3145728,
            'total_uptime_seconds' => 3600,
            'session_count' => 1,
        ]);

        $response = $this->actingAs($user)->get('/users/historical');

        $response->assertStatus(200);

        // 1. Assert Category Filter
        $response->assertSee('Semua Kategori');
        $response->assertSee('Voucher (Profit)');
        $response->assertSee('Bulanan (Profit)');
        $response->assertSee('Pribadi (Non-Profit)');

        // 2. Assert Table Headers and alignments
        $response->assertSee('User');
        $response->assertSee('text-center">Kategori', false);
        $response->assertSee('text-center">Status', false);
        $response->assertSee('Paket Profil');
        $response->assertSee('text-center">Jumlah Sesi', false);
        $response->assertSee('text-center">Total Uptime', false);
        $response->assertSee('text-right">Upload (Tx)', false);
        $response->assertSee('text-right">Download (Rx)', false);
        $response->assertSee('text-right font-bold">Total Pemakaian', false);
        $response->assertSee('text-center">Aksi', false);

        // 3. Assert Pagination controls
        $response->assertSee('Menampilkan');
        $response->assertSee('Per halaman:');
        $response->assertSee('Prev');
        $response->assertSee('Next');
    }

    public function test_historical_usage_csv_export_includes_category_column(): void
    {
        $user = User::factory()->create();

        $mockRouter = Mockery::mock(RouterOsService::class);
        $mockRouter->shouldReceive('getActiveHotspotSessions')->andReturn([]);
        $mockRouter->shouldReceive('getDhcpLeases')->andReturn([]);
        $this->app->instance(RouterOsService::class, $mockRouter);

        $prof = HotspotProfile::create([
            'name' => 'Paket-Rumahan',
            'category' => 'bulanan',
            'is_active' => true,
        ]);

        HotspotUser::create([
            'username' => 'member_bulanan_1',
            'password' => '1234',
            'profile_id' => $prof->id,
            'is_active' => true,
        ]);

        DailyUserUsageSummary::create([
            'username' => 'member_bulanan_1',
            'usage_date' => now()->toDateString(),
            'total_bytes_in' => 5242880,
            'total_bytes_out' => 10485760,
            'total_bytes' => 15728640,
            'total_uptime_seconds' => 7200,
            'session_count' => 2,
        ]);

        $response = $this->actingAs($user)->get('/users/historical?export=csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Username,Kategori,Profile,Status,Sesi', $content);
        $this->assertStringContainsString('member_bulanan_1,BULANAN,Paket-Rumahan', $content);
    }
}
