<?php

namespace Tests\Feature;

use App\Models\CashierShift;
use App\Models\MonthlyCustomer;
use App\Models\MonthlyInvoice;
use App\Models\MonthlyPayment;
use App\Models\User;
use App\Services\PosManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyInvoiceManagementTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_selective_customer_invoice_generation(): void
    {
        $c1 = MonthlyCustomer::create(['name' => 'Cust One', 'monthly_price' => 100000, 'is_active' => true]);
        $c2 = MonthlyCustomer::create(['name' => 'Cust Two', 'monthly_price' => 150000, 'is_active' => true]);
        $c3 = MonthlyCustomer::create(['name' => 'Cust Three', 'monthly_price' => 200000, 'is_active' => true]);

        // Generate only for c1 and c3
        $response = $this->postJson(route('pos.monthly.invoices.generate'), [
            'billing_month' => '2026-11',
            'customer_ids' => [$c1->id, $c3->id],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'generated' => 2,
        ]);

        $this->assertNotNull(MonthlyInvoice::where('monthly_customer_id', $c1->id)->whereDate('billing_month', '2026-11-01')->first());
        $this->assertNotNull(MonthlyInvoice::where('monthly_customer_id', $c3->id)->whereDate('billing_month', '2026-11-01')->first());
        $this->assertNull(MonthlyInvoice::where('monthly_customer_id', $c2->id)->whereDate('billing_month', '2026-11-01')->first());
    }

    public function test_single_invoice_deletion_cascades_payments(): void
    {
        $customer = MonthlyCustomer::create(['name' => 'Cust Cascade', 'monthly_price' => 120000, 'is_active' => true]);
        $pos = new PosManagerService();

        $payment = $pos->recordMonthlyPayment([
            'customer_id' => $customer->id,
            'billing_month' => '2026-11-01',
            'amount_paid' => 120000,
            'paid_at' => '2026-11-02',
            'payment_method' => 'transfer',
        ]);

        $invoice = MonthlyInvoice::where('monthly_customer_id', $customer->id)->first();
        $this->assertNotNull($invoice);
        $this->assertDatabaseHas('monthly_payments', ['monthly_invoice_id' => $invoice->id]);

        $response = $this->deleteJson(route('pos.monthly.invoices.delete', $invoice->id));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('monthly_invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('monthly_payments', ['id' => $payment->id]);
    }

    public function test_single_invoice_deletion_recalculates_cashier_shift(): void
    {
        $shift = CashierShift::create([
            'user_id' => $this->user->id,
            'opened_at' => now(),
            'opening_cash' => 50000,
            'status' => 'open',
        ]);

        $customer = MonthlyCustomer::create(['name' => 'Cust Shift', 'monthly_price' => 100000, 'is_active' => true]);
        $pos = new PosManagerService();

        $payment = $pos->recordMonthlyPayment([
            'customer_id' => $customer->id,
            'billing_month' => '2026-11-01',
            'amount_paid' => 100000,
            'paid_at' => '2026-11-02',
            'payment_method' => 'cash',
        ]);

        $shift->refresh();
        $this->assertEquals(150000, (float) $shift->expected_cash);

        $invoice = MonthlyInvoice::where('monthly_customer_id', $customer->id)->first();
        $response = $this->deleteJson(route('pos.monthly.invoices.delete', $invoice->id));
        $response->assertStatus(200);

        $shift->refresh();
        $this->assertEquals(50000, (float) $shift->expected_cash);
    }

    public function test_batch_invoice_deletion(): void
    {
        $c1 = MonthlyCustomer::create(['name' => 'Batch Cust 1', 'monthly_price' => 80000, 'is_active' => true]);
        $c2 = MonthlyCustomer::create(['name' => 'Batch Cust 2', 'monthly_price' => 90000, 'is_active' => true]);
        $c3 = MonthlyCustomer::create(['name' => 'Batch Cust 3', 'monthly_price' => 100000, 'is_active' => true]);

        $pos = new PosManagerService();
        $pos->generateMonthlyInvoices('2026-11');

        $invoices = MonthlyInvoice::whereDate('billing_month', '2026-11-01')->get();
        $this->assertCount(3, $invoices);

        $inv1 = $invoices->where('monthly_customer_id', $c1->id)->first();
        $inv2 = $invoices->where('monthly_customer_id', $c2->id)->first();
        $inv3 = $invoices->where('monthly_customer_id', $c3->id)->first();

        // Add payment to inv1
        $pos->recordInvoicePayment($inv1, [
            'amount_paid' => 80000,
            'paid_at' => '2026-11-02',
            'payment_method' => 'transfer',
        ]);

        // Batch delete inv1 and inv2
        $response = $this->postJson(route('pos.monthly.invoices.batch-delete'), [
            'invoice_ids' => [$inv1->id, $inv2->id],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'deleted_count' => 2,
        ]);

        $this->assertDatabaseMissing('monthly_invoices', ['id' => $inv1->id]);
        $this->assertDatabaseMissing('monthly_invoices', ['id' => $inv2->id]);
        $this->assertDatabaseHas('monthly_invoices', ['id' => $inv3->id]);
        $this->assertDatabaseMissing('monthly_payments', ['monthly_invoice_id' => $inv1->id]);
    }
}
