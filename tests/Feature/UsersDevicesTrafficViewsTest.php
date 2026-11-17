<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersDevicesTrafficViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_devices_traffic_pages_render_accessible_forms(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('users.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('users.historical'))->assertStatus(200);
        $this->actingAs($user)->get(route('devices.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('traffic.index'))->assertStatus(200);
    }
}
