<?php

namespace Tests\Feature;

use App\Models\HotspotProfile;
use App\Models\HotspotUser;
use App\Models\MonthlyCustomer;
use App\Models\MonthlyInvoice;
use App\Models\User;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    public function test_empty_query_returns_default_navigation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $res = $this->getJson(route('api.search.global', ['q' => '']));
        $res->assertStatus(200);
        $res->assertJsonStructure([
            'query',
            'results' => [
                'navigation',
                'users',
                'customers',
                'devices',
            ]
        ]);
        $this->assertNotEmpty($res->json('results.navigation'));
    }

    public function test_search_matches_hotspot_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $profile = HotspotProfile::create([
            'name' => '10Mbps-Search',
            'rate_limit' => '10M/10M',
            'selling_price' => 50000,
        ]);

        $hotspotUser = HotspotUser::create([
            'profile_id' => $profile->id,
            'username' => 'searchable_user_99',
            'password' => 'pass123',
            'is_active' => true,
            'comment' => 'VIP User Antigravity',
        ]);

        $res = $this->getJson(route('api.search.global', ['q' => 'searchable_user_99']));
        $res->assertStatus(200);
        
        $users = $res->json('results.users');
        $this->assertCount(1, $users);
        $this->assertEquals('searchable_user_99', $users[0]['title']);
    }

    public function test_search_matches_customers_and_invoices(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $customer = MonthlyCustomer::create([
            'name' => 'Bapak John Doe Hotspot',
            'contact' => '081987654321',
            'address' => 'Jl. Merdeka No. 10',
            'monthly_price' => 150000,
            'billing_cycle_day' => 1,
            'is_active' => true,
        ]);

        $invoice = MonthlyInvoice::create([
            'monthly_customer_id' => $customer->id,
            'invoice_number' => 'INV-SEARCH-TEST-001',
            'billing_month' => '2026-08-01',
            'amount' => 150000,
            'amount_paid' => 0,
            'balance_due' => 150000,
            'status' => 'unpaid',
            'due_date' => now()->addDays(5),
        ]);

        // Search Customer
        $res1 = $this->getJson(route('api.search.global', ['q' => 'John Doe']));
        $res1->assertStatus(200);
        $this->assertNotEmpty($res1->json('results.customers'));

        // Search Invoice Number
        $res2 = $this->getJson(route('api.search.global', ['q' => 'INV-SEARCH-TEST']));
        $res2->assertStatus(200);
        $this->assertNotEmpty($res2->json('results.customers'));
    }

    public function test_search_matches_navigation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $res = $this->getJson(route('api.search.global', ['q' => 'Voucher']));
        $res->assertStatus(200);
        $navs = $res->json('results.navigation');
        $this->assertNotEmpty($navs);
    }
}
