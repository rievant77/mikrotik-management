<?php

namespace Tests\Feature;

use App\Models\RouterSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SidebarTelemetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_displays_real_router_data(): void
    {
        $user = User::factory()->create();
        RouterSetting::create([
            'name' => 'MikroTik-UTAMA',
            'app_name' => 'MikroTik Manager',
            'host' => '10.20.30.1',
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'secret',
            'is_active' => true,
            'last_successful_poll_at' => now(),
        ]);

        Cache::put('router:system_resource', [
            'board-name' => 'RB4011iGS+',
            'version' => '7.16.1',
            'cpu-load' => 12,
        ], 120);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('RB4011iGS+');
        $response->assertSee('10.20.30.1');
        $response->assertDontSee('CCR1009-Core');
        $response->assertDontSee('192.168.88.1');
    }
}
