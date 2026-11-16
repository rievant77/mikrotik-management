<?php

namespace Tests\Feature;

use App\Models\HotspotProfile;
use App\Models\User;
use App\Services\VoucherBatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateVoucherEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_voucher_page_renders_with_icon_default_length_and_voucher_profiles(): void
    {
        $user = User::factory()->create();

        $voucherProfile = HotspotProfile::create([
            'name' => 'Profile-Voucher-Test',
            'category' => 'voucher',
            'selling_price' => 5000,
            'validity' => '1 Hari',
            'is_active' => true,
        ]);

        $monthlyProfile = HotspotProfile::create([
            'name' => 'Profile-Bulanan-Test',
            'category' => 'bulanan',
            'selling_price' => 100000,
            'validity' => '1 Bulan',
            'is_active' => true,
        ]);

        $privateProfile = HotspotProfile::create([
            'name' => 'Profile-Pribadi-Test',
            'category' => 'pribadi',
            'selling_price' => 0,
            'validity' => 'Unlimited',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/hotspot/generate');

        $response->assertStatus(200);

        // 1. Tombol user hotspot memiliki icon
        $response->assertSee('User Hotspot');
        $response->assertSee('M12 4.354a4 4 0 110 5.292');

        // 2. Name Length default adalah 4
        $response->assertSee('length: 4');

        // 3. Profile default isinya adalah hanya kategori voucher
        $response->assertSee('Voucher (Default)');
        $viewProfiles = $response->viewData('profiles');
        $this->assertNotNull($viewProfiles);
        $this->assertTrue($viewProfiles->contains('name', 'Profile-Voucher-Test'));
        $this->assertFalse($viewProfiles->contains('name', 'Profile-Bulanan-Test'));
        $this->assertFalse($viewProfiles->contains('name', 'Profile-Pribadi-Test'));

        // All profiles are available for client-side selection
        $allProfiles = $response->viewData('allProfiles');
        $this->assertNotNull($allProfiles);
        $this->assertCount(3, $allProfiles);
    }

    public function test_voucher_batch_service_defaults_to_length_4(): void
    {
        HotspotProfile::create([
            'name' => 'Default-Voucher-Profile',
            'category' => 'voucher',
            'is_active' => true,
        ]);

        $service = new VoucherBatchService();
        $result = $service->generate([
            'profile' => 'Default-Voucher-Profile',
            'qty' => 3,
            'credentialMode' => 'same',
            'charSet' => 'numeric',
            // Omit 'length' to test default fallback
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals(3, $result['count']);
        foreach ($result['vouchers'] as $voucher) {
            $this->assertEquals(4, strlen($voucher['username']));
            $this->assertEquals($voucher['username'], $voucher['password']);
        }
    }
}
