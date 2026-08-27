<?php

namespace Tests\Feature;

use App\Models\CashierShift;
use App\Models\MonthlyCustomer;
use App\Models\MonthlyPayment;
use App\Services\PosManagerService;
use Exception;
use Tests\TestCase;

class PosAccountingTest extends TestCase
{
    public function test_monthly_payment_recording_and_void(): void
    {
        $customer = MonthlyCustomer::firstOrCreate(['name' => 'Pak Joko'], [
            'monthly_price' => 175000,
            'is_active' => true,
        ]);

        $shift = CashierShift::firstOrCreate(['status' => 'open'], [
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $pos = new PosManagerService();

        // 1. Record Cash Payment
        $payment = $pos->recordMonthlyPayment([
            'customer_id' => $customer->id,
            'billing_month' => '2026-09-01',
            'amount_paid' => 175000,
            'paid_at' => '2026-09-02',
            'payment_method' => 'cash',
        ]);

        $this->assertEquals('paid', $payment->status);
        $this->assertEquals(175000, (float) $payment->amount_paid);

        // Expected cash should include opening + payment
        $shift->refresh();
        $this->assertEquals(275000, (float) $shift->expected_cash);

        // 2. Prevent duplicate payment for same period
        $this->expectException(Exception::class);
        $pos->recordMonthlyPayment([
            'customer_id' => $customer->id,
            'billing_month' => '2026-09-01',
            'amount_paid' => 175000,
            'paid_at' => '2026-09-02',
            'payment_method' => 'cash',
        ]);
    }

    public function test_cashier_shift_close_and_discrepancy(): void
    {
        $pos = new PosManagerService();
        $shift = $pos->openShift(200000);

        $this->assertEquals('open', $shift->status);
        $this->assertEquals(200000, (float) $shift->opening_cash);

        // Close shift with actual cash 195000 (Shortage of 5000)
        $closed = $pos->closeShift($shift, 195000);

        $this->assertEquals('closed', $closed->status);
        $this->assertEquals(-5000, (float) $closed->discrepancy);
    }
}
