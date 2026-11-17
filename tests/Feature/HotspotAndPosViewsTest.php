<?php

namespace Tests\Feature;

use App\Models\HotspotProfile;
use App\Models\HotspotVoucher;
use App\Models\RouterSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotspotAndPosViewsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        RouterSetting::create([
            'name' => 'Main Router',
            'host' => '192.168.88.1',
            'port' => 8728,
            'username' => 'admin',
            'password' => 'secret',
            'timeout' => 5,
        ]);
    }

    public function test_hotspot_profiles_view_renders_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('hotspot.profiles'));
        $response->assertStatus(200);
        $response->assertSee('Paket');
    }

    public function test_hotspot_users_view_renders_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('hotspot.users'));
        $response->assertStatus(200);
        $response->assertSee('User Hotspot');
    }

    public function test_hotspot_generate_view_renders_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('hotspot.generate'));
        $response->assertStatus(200);
        $response->assertSee('Generate Voucher');
    }

    public function test_vouchers_index_view_renders_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('vouchers.index'));
        $response->assertStatus(200);
        $response->assertSee('Voucher');
    }

    public function test_pos_index_view_renders_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('pos.index'));
        $response->assertStatus(200);
    }

    public function test_pos_vouchers_view_renders_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('pos.vouchers'));
        $response->assertStatus(200);
    }

    public function test_pos_monthly_view_renders_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('pos.monthly'));
        $response->assertStatus(200);
    }

    public function test_pos_shifts_view_renders_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('pos.shifts'));
        $response->assertStatus(200);
    }
}
