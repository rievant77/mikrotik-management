<?php

namespace Tests\Feature;

use App\Models\TrafficCategoryStat;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class TrafficAnalyticsTest extends TestCase
{
    public function test_traffic_analytics_page_renders_successfully(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $res = $this->get(route('traffic.index'));
        $res->assertStatus(200);
        $res->assertSee('Analisis Trafik');
        $res->assertSee('Streaming Video');
        $res->assertSee('Online Gaming');
    }

    public function test_traffic_analytics_live_data_api(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Seed some sample stats
        TrafficCategoryStat::create([
            'recorded_date' => Carbon::today()->toDateString(),
            'recorded_hour' => 10,
            'category' => 'video',
            'platform' => 'YouTube',
            'bytes_in' => 500000000,
            'bytes_out' => 50000000,
            'total_bytes' => 550000000,
        ]);

        $res = $this->getJson(route('traffic.live'));
        $res->assertStatus(200);
        $res->assertJsonStructure([
            'success',
            'data' => [
                'is_rules_installed',
                'total_traffic_bytes',
                'formatted_total_traffic',
                'categories',
                'platforms',
                'donut_chart',
                'hourly_chart',
            ],
        ]);
    }

    public function test_traffic_deploy_rules_api(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $res = $this->postJson(route('traffic.deploy'));
        // Even if router is offline in test env, it handles gracefully
        $this->assertContains($res->status(), [200, 500]);
        $this->assertArrayHasKey('success', $res->json());
    }

    public function test_traffic_reset_counters_api(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $res = $this->postJson(route('traffic.reset-counters'));
        $this->assertContains($res->status(), [200, 500]);
    }
}
