<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class FrontendPagesTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('MikroTik Manager');
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@mikrotik.lan',
            'password' => bcrypt('password'),
        ]);

        $loginRes = $this->post('/login', [
            'email' => 'admin@mikrotik.lan',
            'password' => 'password',
        ]);
        $loginRes->assertRedirect('/');

        $this->assertAuthenticatedAs($user);

        $logoutRes = $this->post('/logout');
        $logoutRes->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_all_frontend_routes_render_successfully_when_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $routes = [
            '/',
            '/users',
            '/users/historical',
            '/users/budi-santoso',
            '/devices',
            '/hotspot/profiles',
            '/hotspot/users',
            '/hotspot/generate',
            '/vouchers/print/58mm',
            '/vouchers/print/80mm',
            '/vouchers/print/grid',
            '/pos',
            '/pos/vouchers',
            '/pos/monthly',
            '/pos/shifts',
            '/settings/router',
            '/settings/roles',
            '/settings/templates',
            '/audit-logs',
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
        }

        // Test Profile CRUD elements
        $profileRes = $this->get('/hotspot/profiles');
        $profileRes->assertSee('Sinkron dari Router');

        // Test User CRUD elements
        $userRes = $this->get('/hotspot/users');
        $userRes->assertSee('Sinkron dari Router');

        // Test Monthly Customer CRUD elements
        $monthlyRes = $this->get('/pos/monthly');
        $monthlyRes->assertSee('Tambah Customer');
    }

    public function test_pages_render_without_intl_with_usage_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create daily summary
        \App\Models\DailyUserUsageSummary::create([
            'username' => 'testuser',
            'usage_date' => now()->toDateString(),
            'total_bytes_in' => 524288000,
            'total_bytes_out' => 1073741824,
            'total_bytes' => 1598029824,
            'total_uptime_seconds' => 7200,
            'session_count' => 3,
        ]);

        $dashRes = $this->get('/');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('1.5 GB');

        $histRes = $this->get('/users/historical');
        $histRes->assertStatus(200);
        $histRes->assertSee('1.5 GB');

        $posRes = $this->get('/pos');
        $posRes->assertStatus(200);

        // Test devices page loads cleanly in direct mode
        $devRes = $this->get('/devices');
        $devRes->assertStatus(200);

        // Test online users page loads cleanly in direct mode
        $userOnlineRes = $this->get('/users');
        $userOnlineRes->assertStatus(200);
    }
}
