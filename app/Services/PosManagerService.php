<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CashierShift;
use App\Models\MonthlyCustomer;
use App\Models\MonthlyPayment;
use App\Models\VoucherSale;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class PosManagerService
{
    /**
     * Get POS financial and shift overview for specified month.
     */
    public function getOverview(?string $month = null): array
    {
        $targetMonth = $month ? Carbon::parse($month)->startOfMonth() : Carbon::now()->startOfMonth();

        // 1. Voucher Revenue (Only activated vouchers - PRD Section 5.11.1)
        $voucherSalesQuery = VoucherSale::where('status', 'completed')
            ->whereBetween('activated_at', [$targetMonth, $targetMonth->copy()->endOfMonth()]);

        $totalVoucherRevenue = (float) $voucherSalesQuery->sum('selling_price');
        $totalVoucherProfit = (float) $voucherSalesQuery->sum('profit');
        $totalVouchersUsed = $voucherSalesQuery->count();

        // 2. Monthly Customer Billings (PRD Section 5.11.2)
        $activeCustomers = MonthlyCustomer::where('is_active', true)->get();
        $totalMonthlyExpected = (float) $activeCustomers->sum('monthly_price');

        $paidMonthlyPayments = MonthlyPayment::where('status', 'paid')
            ->whereDate('billing_month', $targetMonth->toDateString())
            ->get();

        $totalMonthlyPaid = (float) $paidMonthlyPayments->sum('amount_paid');
        $totalMonthlyUnpaid = max(0, $totalMonthlyExpected - $totalMonthlyPaid);

        // 3. Active Shift Status (PRD Section 5.11.3)
        $activeShift = CashierShift::where('status', 'open')->latest()->first();
        if ($activeShift) {
            $this->recalculateShiftExpectedCash($activeShift);
        }

        return [
            'period' => \App\Support\FormatHelper::formatMonthIndo($targetMonth),
            'voucher_revenue' => $totalVoucherRevenue,
            'voucher_profit' => $totalVoucherProfit,
            'vouchers_used_count' => $totalVouchersUsed,
            'monthly_expected' => $totalMonthlyExpected,
            'monthly_paid' => $totalMonthlyPaid,
            'monthly_unpaid' => $totalMonthlyUnpaid,
            'total_gross_income' => $totalVoucherRevenue + $totalMonthlyPaid,
            'active_shift' => $activeShift,
        ];
    }

    /**
     * Record a manual monthly payment.
     */
    public function recordMonthlyPayment(array $data): MonthlyPayment
    {
        $customerId = $data['customer_id'];
        $customer = MonthlyCustomer::findOrFail($customerId);

        $billingMonth = Carbon::parse($data['billing_month'] ?? now())->startOfMonth()->toDateString();
        $paidAt = Carbon::parse($data['paid_at'] ?? now())->toDateString();
        $amountPaid = (float) ($data['amount_paid'] ?? $customer->monthly_price);
        $method = $data['payment_method'] ?? 'cash';
        $activeShift = CashierShift::where('status', 'open')->latest()->first();

        // Prevent duplicate paid transaction for the same month unless voided (FR-11.11)
        $existing = MonthlyPayment::where('monthly_customer_id', $customerId)
            ->whereDate('billing_month', $billingMonth)
            ->where('status', '<>', 'void')
            ->first();

        if ($existing) {
            throw new Exception("Tagihan periode {$billingMonth} sudah tercatat sebelumnya.");
        }

        $status = ($amountPaid >= (float) $customer->monthly_price) ? 'paid' : 'partial';

        DB::beginTransaction();
        try {
            $payment = MonthlyPayment::create([
                'monthly_customer_id' => $customer->id,
                'billing_month' => $billingMonth,
                'monthly_price' => $customer->monthly_price,
                'amount_paid' => $amountPaid,
                'paid_at' => $paidAt,
                'payment_method' => $method,
                'status' => $status,
                'recorded_by_user_id' => auth()->id() ?? null,
                'shift_id' => $activeShift?->id,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($activeShift && $method === 'cash') {
                $this->recalculateShiftExpectedCash($activeShift);
            }

            AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'action' => 'monthly_payment_created',
                'entity_type' => 'MonthlyPayment',
                'entity_id' => $payment->id,
                'new_values' => [
                    'customer' => $customer->name,
                    'amount' => $amountPaid,
                    'method' => $method,
                    'billing_month' => $billingMonth,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);

            DB::commit();
            return $payment;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Void / cancel a payment (FR-11.11 & PRD Section 9).
     */
    public function voidPayment(MonthlyPayment $payment, string $reason = 'Pembatalan transaksi'): bool
    {
        DB::beginTransaction();
        try {
            $oldStatus = $payment->status;
            $payment->update([
                'status' => 'void',
                'notes' => ($payment->notes ? $payment->notes . ' | ' : '') . 'VOID: ' . $reason,
            ]);

            if ($payment->shift_id) {
                $shift = CashierShift::find($payment->shift_id);
                if ($shift) {
                    $this->recalculateShiftExpectedCash($shift);
                }
            }

            AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'action' => 'monthly_payment_voided',
                'entity_type' => 'MonthlyPayment',
                'entity_id' => $payment->id,
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => 'void', 'reason' => $reason],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Open a new cashier shift.
     */
    public function openShift(float $openingCash = 0, ?int $userId = null): CashierShift
    {
        // Close any lingering open shift
        CashierShift::where('status', 'open')->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $shift = CashierShift::create([
            'user_id' => $userId ?? auth()->id(),
            'opened_at' => now(),
            'opening_cash' => $openingCash,
            'expected_cash' => $openingCash,
            'status' => 'open',
        ]);

        AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => 'cashier_shift_opened',
            'entity_type' => 'CashierShift',
            'entity_id' => $shift->id,
            'new_values' => ['opening_cash' => $openingCash],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return $shift;
    }

    /**
     * Close cashier shift with physical cash reconciliation.
     */
    public function closeShift(CashierShift $shift, float $actualCash): CashierShift
    {
        $this->recalculateShiftExpectedCash($shift);

        $expected = (float) $shift->expected_cash;
        $discrepancy = $actualCash - $expected;

        $shift->update([
            'closed_at' => now(),
            'actual_cash' => $actualCash,
            'discrepancy' => $discrepancy,
            'status' => 'closed',
        ]);

        AuditLog::create([
            'user_id' => auth()->id() ?? null,
            'action' => 'cashier_shift_closed',
            'entity_type' => 'CashierShift',
            'entity_id' => $shift->id,
            'new_values' => [
                'expected_cash' => $expected,
                'actual_cash' => $actualCash,
                'discrepancy' => $discrepancy,
            ],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return $shift;
    }

    /**
     * Recalculate Expected Cash for a shift:
     * Opening Cash + Cash Voucher Sales + Cash Monthly Payments (Transfer/QRIS is excluded - FR-11.16).
     */
    public function recalculateShiftExpectedCash(CashierShift $shift): float
    {
        $opening = (float) $shift->opening_cash;

        // Cash Vouchers in this shift
        $voucherCash = (float) VoucherSale::where('shift_id', $shift->id)
            ->where('status', 'completed')
            ->sum('selling_price');

        // Cash Monthly Payments in this shift
        $monthlyCash = (float) MonthlyPayment::where('shift_id', $shift->id)
            ->where('status', '<>', 'void')
            ->where('payment_method', 'cash')
            ->sum('amount_paid');

        $totalExpected = $opening + $voucherCash + $monthlyCash;

        $shift->update(['expected_cash' => $totalExpected]);

        return $totalExpected;
    }
}
