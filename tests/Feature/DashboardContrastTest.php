<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardContrastTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_with_high_contrast_classes(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('text-zinc-400 truncate">/ ');
        $response->assertDontSee('text-xs text-zinc-400">Saat Ini');
        $response->assertSee('text-zinc-500 dark:text-zinc-400');
    }
}
