<?php

namespace Tests\Feature;

use App\Models\RouterSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarColoredIconsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_contains_thematic_icon_colors(): void
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

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('text-indigo-500');
        $response->assertSee('text-emerald-500');
        $response->assertSee('text-sky-500');
        $response->assertSee('text-rose-500');
        $response->assertSee('text-amber-500');
        $response->assertSee('text-teal-500');
    }
}
