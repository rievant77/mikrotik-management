<?php

namespace Tests\Unit;

use App\Models\DailyExpense;
use App\Models\MonthlyExpenseEntry;
use App\Models\MonthlyExpenseTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosExpenseModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_daily_expense(): void
    {
        $user = User::factory()->create();
        $expense = DailyExpense::create([
            'description' => 'Beli kabel patchcord',
            'amount' => 50000,
            'expense_date' => '2026-11-19',
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('daily_expenses', [
            'description' => 'Beli kabel patchcord',
            'amount' => 50000,
        ]);
        $this->assertEquals($user->id, $expense->creator->id);
    }

    public function test_template_and_monthly_entry_relationship(): void
    {
        $template = MonthlyExpenseTemplate::create([
            'name' => 'ISP Dedicated',
            'amount' => 1500000,
            'is_active' => true,
        ]);

        $entry = MonthlyExpenseEntry::create([
            'template_id' => $template->id,
            'name' => $template->name,
            'amount' => $template->amount,
            'billing_month' => '2026-11-01',
        ]);

        $this->assertDatabaseHas('monthly_expense_entries', [
            'name' => 'ISP Dedicated',
            'amount' => 1500000,
        ]);
        $this->assertEquals('2026-11-01', $entry->billing_month->format('Y-m-d'));
        $this->assertEquals($template->id, $entry->template->id);
    }
}
