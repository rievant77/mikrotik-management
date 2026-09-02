<?php

namespace Tests\Feature;

use App\Models\DeviceWebHistory;
use App\Models\User;
use App\Services\RouterOsService;
use App\Services\WebHistoryService;
use Carbon\Carbon;
use Tests\TestCase;

class DeviceWebActivityTest extends TestCase
{
    public function test_device_web_activity_api_returns_success_and_structure(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Pre-create some web history records
        DeviceWebHistory::create([
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'ip_address' => '192.168.88.150',
            'username' => 'testuser',
            'domain' => 'youtube.com',
            'site_name' => 'YouTube',
            'category' => 'video',
            'protocol' => 'https',
            'port' => 443,
            'hit_count' => 12,
            'total_bytes' => 104857600,
            'first_seen_at' => Carbon::now()->subHours(3),
            'last_seen_at' => Carbon::now(),
        ]);

        $res = $this->getJson('/api/devices/AA:BB:CC:DD:EE:FF/web-activity?ip=192.168.88.150&username=testuser');
        $res->assertStatus(200);
        $res->assertJsonStructure([
            'success',
            'data' => [
                'mac_address',
                'ip_address',
                'username',
                'live_connections',
                'live_count',
                'history',
                'history_count',
            ],
        ]);

        $this->assertGreaterThanOrEqual(1, $res->json('data.history_count'));
        $this->assertEquals('youtube.com', $res->json('data.history.0.domain'));
        $this->assertEquals('Streaming Video', $res->json('data.history.0.category_label'));
    }

    public function test_web_history_service_classifies_domains_correctly(): void
    {
        $service = new WebHistoryService(new RouterOsService());

        $yt = $service->classifyDomain('googlevideo.com');
        $this->assertEquals('video', $yt['category']);
        $this->assertEquals('Streaming Video', $yt['label']);

        $wa = $service->classifyDomain('web.whatsapp.com');
        $this->assertEquals('social_media', $wa['category']);
        $this->assertEquals('WhatsApp', $wa['site_name']);

        $ml = $service->classifyDomain('mobilelegends.com');
        $this->assertEquals('gaming', $ml['category']);

        $shopee = $service->classifyDomain('shopee.co.id');
        $this->assertEquals('ecommerce', $shopee['category']);

        $detik = $service->classifyDomain('news.detik.com');
        $this->assertEquals('news', $detik['category']);
    }

    public function test_resolve_domain_from_dns_map(): void
    {
        $service = new WebHistoryService(new RouterOsService());

        $dnsMap = [
            '103.1.2.3' => 'www.kompas.com',
            '104.28.1.1' => 'tokopedia.com.',
        ];

        $res1 = $service->resolveDomain('103.1.2.3', $dnsMap);
        $this->assertEquals('kompas.com', $res1);

        $res2 = $service->resolveDomain('104.28.1.1', $dnsMap);
        $this->assertEquals('tokopedia.com', $res2);
    }
}
