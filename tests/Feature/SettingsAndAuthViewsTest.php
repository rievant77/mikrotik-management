<?php

namespace Tests\Feature;

use App\Models\RouterSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAndAuthViewsTest extends TestCase
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
            'username' => 'admin',
            'password' => 'secret',
            'port' => 8728,
            'is_active' => true,
        ]);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    public function test_settings_router_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.router'));

        $response->assertStatus(200);
    }

    public function test_settings_profile_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.profile'));

        $response->assertStatus(200);
    }

    public function test_settings_templates_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.templates'));

        $response->assertStatus(200);
    }

    public function test_settings_maintenance_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.maintenance'));

        $response->assertStatus(200);
    }

    public function test_audit_logs_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('audit.index'));

        $response->assertStatus(200);
    }
}
