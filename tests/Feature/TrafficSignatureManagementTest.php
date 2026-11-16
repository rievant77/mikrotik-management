<?php

namespace Tests\Feature;

use App\Models\TrafficSignature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrafficSignatureManagementTest extends TestCase
{
    public function test_traffic_signature_default_seeding(): void
    {
        TrafficSignature::seedDefaults();

        $this->assertDatabaseHas('traffic_signatures', [
            'mark_name' => 'agy_cm_mlbb',
            'category' => 'gaming',
        ]);

        $this->assertDatabaseHas('traffic_signatures', [
            'mark_name' => 'agy_cm_youtube',
            'category' => 'video',
        ]);

        $count = TrafficSignature::count();
        $this->assertGreaterThanOrEqual(10, $count);
    }

    public function test_traffic_signature_model_casting_and_scopes(): void
    {
        $sig = TrafficSignature::create([
            'name' => 'Custom Valorant',
            'category' => 'gaming',
            'mark_name' => 'agy_cm_valorant',
            'address_list_name' => 'agy_list_valorant',
            'fqdn_domains' => ['riotgames.com', 'playvalorant.com'],
            'tls_host' => '*riotgames.com,*playvalorant.com',
            'tcp_ports' => '2099,5222',
            'udp_ports' => '7000-8000',
            'is_quic' => false,
            'is_active' => true,
            'is_system' => false,
        ]);

        $this->assertIsArray($sig->fqdn_domains);
        $this->assertContains('riotgames.com', $sig->fqdn_domains);
        $this->assertTrue($sig->is_active);
        $this->assertFalse($sig->is_system);

        $this->assertCount(1, TrafficSignature::where('mark_name', 'agy_cm_valorant')->active()->get());

        $sig->update(['is_active' => false]);
        $this->assertCount(0, TrafficSignature::where('mark_name', 'agy_cm_valorant')->active()->get());
    }

    public function test_get_platform_user_rank_returns_structured_leaderboard(): void
    {
        TrafficSignature::seedDefaults();
        $service = app(\App\Services\TrafficAnalyticsService::class);

        $rankData = $service->getPlatformUserRank('agy_cm_mlbb');

        $this->assertIsArray($rankData);
        $this->assertEquals('Mobile Legends: Bang Bang', $rankData['platform_name']);
        $this->assertEquals('gaming', $rankData['category_key']);
        $this->assertArrayHasKey('users', $rankData);
        $this->assertArrayHasKey('total_bytes', $rankData);
        $this->assertArrayHasKey('formatted_total_bytes', $rankData);
    }

    public function test_api_signatures_crud_and_endpoints(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Get signatures
        $res = $this->getJson(route('traffic.signatures'));
        $res->assertStatus(200);
        $res->assertJsonStructure(['success', 'data']);

        // 2. Create signature
        $postRes = $this->postJson(route('traffic.signatures.store'), [
            'name' => 'Custom Dota 2',
            'category' => 'gaming',
            'udp_ports' => '27015-27050',
            'tcp_ports' => '27015-27050',
            'fqdn_domains' => ['dota2.com', 'valvesoftware.com'],
        ]);
        $postRes->assertStatus(201);
        $this->assertDatabaseHas('traffic_signatures', ['name' => 'Custom Dota 2']);
        $newId = $postRes->json('data.id');

        // 3. Update signature
        $putRes = $this->putJson(route('traffic.signatures.update', $newId), [
            'name' => 'Custom Dota 2 & Steam',
            'udp_ports' => '27015-27060',
            'is_active' => true,
        ]);
        $putRes->assertStatus(200);
        $this->assertDatabaseHas('traffic_signatures', ['name' => 'Custom Dota 2 & Steam']);

        // 4. Delete signature
        $delRes = $this->deleteJson(route('traffic.signatures.destroy', $newId));
        $delRes->assertStatus(200);
        $this->assertDatabaseMissing('traffic_signatures', ['id' => $newId]);

        // 5. Reset defaults
        $resetRes = $this->postJson(route('traffic.signatures.reset-defaults'));
        $resetRes->assertStatus(200);
        $this->assertDatabaseHas('traffic_signatures', ['mark_name' => 'agy_cm_mlbb']);

        // 6. Platform User Rank API
        $rankRes = $this->getJson(route('traffic.platforms.users', 'agy_cm_mlbb'));
        $rankRes->assertStatus(200);
        $rankRes->assertJsonStructure([
            'success',
            'data' => [
                'platform_name',
                'mark_name',
                'category',
                'total_bytes',
                'formatted_total_bytes',
                'active_users_count',
                'users',
            ],
        ]);
    }
}
