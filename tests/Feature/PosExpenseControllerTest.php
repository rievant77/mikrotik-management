<?php

namespace Tests\Feature;

use App\Models\DailyExpense;
use App\Models\MonthlyExpenseEntry;
use App\Models\MonthlyExpenseTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_view_expenses_page(): void
    {
        $response = $this->get(route('pos.expenses'));
        $response->assertStatus(200);
        $response->assertViewIs('pos.expenses');
    }

    public function test_can_crud_daily_expense(): void
    {
        // Store
        $postRes = $this->postJson('/pos/expenses/daily', [
            'description' => 'Servis Mikrotik',
            'amount' => 120000,
            'expense_date' => '2026-11-19',
        ]);
        $postRes->assertStatus(200)->assertJson(['success' => true]);
        $expense = DailyExpense::first();
        $this->assertNotNull($expense);

        // Update
        $putRes = $this->putJson("/pos/expenses/daily/{$expense->id}", [
            'description' => 'Servis Mikrotik Core',
            'amount' => 150000,
            'expense_date' => '2026-11-19',
        ]);
        $putRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('daily_expenses', ['amount' => 150000]);

        // Delete
        $delRes = $this->deleteJson("/pos/expenses/daily/{$expense->id}");
        $delRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('daily_expenses', ['id' => $expense->id]);
    }

    public function test_can_crud_expense_template_and_update_entry(): void
    {
        // Store template
        $tRes = $this->postJson('/pos/expenses/templates', [
            'name' => 'Sewa Tiang',
            'amount' => 250000,
            'is_active' => true,
        ]);
        $tRes->assertStatus(200)->assertJson(['success' => true]);
        $template = MonthlyExpenseTemplate::first();
        $this->assertNotNull($template);

        // Update template
        $tUpdateRes = $this->putJson("/pos/expenses/templates/{$template->id}", [
            'name' => 'Sewa Tiang ISP',
            'amount' => 260000,
            'is_active' => true,
        ]);
        $tUpdateRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('monthly_expense_templates', ['name' => 'Sewa Tiang ISP', 'amount' => 260000]);

        // View expenses page to trigger entry creation
        $this->get('/pos/expenses?month=2026-11');
        $entry = MonthlyExpenseEntry::where('template_id', $template->id)->first();
        $this->assertNotNull($entry);

        // Update entry specifically for this month
        $eRes = $this->putJson("/pos/expenses/entries/{$entry->id}", [
            'amount' => 275000,
            'notes' => 'Ada kenaikan sewa bulan ini',
        ]);
        $eRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('monthly_expense_entries', [
            'id' => $entry->id,
            'amount' => 275000,
            'notes' => 'Ada kenaikan sewa bulan ini',
        ]);

        // Delete template
        $tDelRes = $this->deleteJson("/pos/expenses/templates/{$template->id}");
        $tDelRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseMissing('monthly_expense_templates', ['id' => $template->id]);
    }
}
