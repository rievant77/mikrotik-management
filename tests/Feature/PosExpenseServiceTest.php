<?php

namespace Tests\Feature;

use App\Models\DailyExpense;
use App\Models\MonthlyExpenseEntry;
use App\Models\MonthlyExpenseTemplate;
use App\Services\PosManagerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosExpenseServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_generate_monthly_entries_from_templates(): void
    {
        $service = app(PosManagerService::class);

        MonthlyExpenseTemplate::create(['name' => 'ISP 1', 'amount' => 500000, 'is_active' => true]);
        MonthlyExpenseTemplate::create(['name' => 'Listrik', 'amount' => 300000, 'is_active' => true]);
        MonthlyExpenseTemplate::create(['name' => 'Nonaktif', 'amount' => 100000, 'is_active' => false]);

        $data = $service->getExpensesForMonth('2026-11');

        $this->assertCount(2, $data['monthly_entries']);
        $this->assertEquals(800000, $data['monthly_total']);
    }

    public function test_snapshot_isolation_preserves_past_month_amount(): void
    {
        $service = app(PosManagerService::class);

        $template = MonthlyExpenseTemplate::create(['name' => 'ISP', 'amount' => 500000, 'is_active' => true]);

        // Generate for Month A (November)
        $service->getExpensesForMonth('2026-11');

        // Update template price in Month B (December)
        $template->update(['amount' => 600000]);

        // Generate for Month B
        $service->getExpensesForMonth('2026-12');

        // Verify Month A entry remains 500,000 while Month B is 600,000
        $entryNov = MonthlyExpenseEntry::whereDate('billing_month', '2026-11-01')->first();
        $entryDec = MonthlyExpenseEntry::whereDate('billing_month', '2026-12-01')->first();

        $this->assertEquals(500000, (float) $entryNov->amount);
        $this->assertEquals(600000, (float) $entryDec->amount);
    }

    public function test_dashboard_analytics_includes_expenses_and_operational_profit(): void
    {
        $service = app(PosManagerService::class);

        DailyExpense::create([
            'description' => 'Bensin & ATK',
            'amount' => 50000,
            'expense_date' => '2026-11-10',
        ]);

        MonthlyExpenseTemplate::create(['name' => 'ISP', 'amount' => 200000, 'is_active' => true]);

        $analytics = $service->getDetailedDashboardAnalytics('2026-11');

        $this->assertEquals(50000, $analytics['daily_expenses_total']);
        $this->assertEquals(200000, $analytics['monthly_expenses_total']);
        $this->assertEquals(250000, $analytics['total_expenses']);
        $this->assertEquals($analytics['total_net_profit'] - 250000, $analytics['total_operational_profit']);
    }
}
