<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CashierShift;
use App\Models\MonthlyCustomer;
use App\Models\MonthlyInvoice;
use App\Models\MonthlyPayment;
use App\Models\VoucherSale;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class PosManagerService
{
    /**
     * Get detailed POS executive dashboard analytics for specified month.
     */
    public function getDetailedDashboardAnalytics(?string $month = null): array
    {
        $targetMonth = $month ? Carbon::parse($month)->startOfMonth() : Carbon::now()->startOfMonth();
        $targetMonthEnd = $targetMonth->copy()->endOfMonth();
        $daysInMonth = $targetMonth->daysInMonth;

        // 1. Voucher Metrics
        $voucherSalesQuery = VoucherSale::where('status', 'completed')
            ->whereBetween('activated_at', [$targetMonth, $targetMonthEnd]);

        $totalVoucherRevenue = (float) $voucherSalesQuery->sum('selling_price');
        $totalVoucherCost = (float) $voucherSalesQuery->sum('cost_price');
        $totalVoucherProfit = (float) $voucherSalesQuery->sum('profit');
        $totalVouchersUsed = $voucherSalesQuery->count();
        $voucherProfitMargin = $totalVoucherRevenue > 0 ? round(($totalVoucherProfit / $totalVoucherRevenue) * 100, 1) : 0;

        // 2. Monthly Invoices & Payments Metrics
        $invoices = MonthlyInvoice::with('customer')
            ->whereDate('billing_month', $targetMonth->toDateString())
            ->where('status', '<>', 'cancelled')
            ->get();

        $monthlyPayments = MonthlyPayment::where('status', '<>', 'void')
            ->whereDate('billing_month', $targetMonth->toDateString())
            ->get();

        $totalMonthlyExpected = (float) ($invoices->count() > 0 ? $invoices->sum('amount') : MonthlyCustomer::where('is_active', true)->sum('monthly_price'));
        $totalMonthlyPaid = (float) ($invoices->count() > 0 ? $invoices->sum('amount_paid') : $monthlyPayments->sum('amount_paid'));
        $totalMonthlyUnpaid = max(0, $totalMonthlyExpected - $totalMonthlyPaid);

        // Estimate monthly cost: sum of cost_price for customers whose invoices were paid
        $totalMonthlyCost = 0;
        foreach ($invoices as $inv) {
            if ($inv->amount_paid > 0 && $inv->amount > 0) {
                $ratio = min(1, $inv->amount_paid / $inv->amount);
                $totalMonthlyCost += ((float) ($inv->cost_price ?? 0) * $ratio);
            }
        }
        $totalMonthlyProfit = max(0, $totalMonthlyPaid - $totalMonthlyCost);
        $monthlyProfitMargin = $totalMonthlyPaid > 0 ? round(($totalMonthlyProfit / $totalMonthlyPaid) * 100, 1) : 0;
        $monthlyCollectionRate = $totalMonthlyExpected > 0 ? round(($totalMonthlyPaid / $totalMonthlyExpected) * 100, 1) : 0;

        // 3. Consolidated Business KPIs
        $totalGrossIncome = $totalVoucherRevenue + $totalMonthlyPaid;
        $totalNetProfit = $totalVoucherProfit + $totalMonthlyProfit;
        $totalOperationalCost = $totalVoucherCost + $totalMonthlyCost;
        $overallProfitMargin = $totalGrossIncome > 0 ? round(($totalNetProfit / $totalGrossIncome) * 100, 1) : 0;

        $activeCustomersCount = MonthlyCustomer::where('is_active', true)->count();
        $arpu = $activeCustomersCount > 0 ? round($totalMonthlyPaid / $activeCustomersCount) : 0;

        // 4. Daily Trends (Categories, Voucher Series, Monthly Series, Profit Series)
        $categories = [];
        $dailyVoucherSeries = [];
        $dailyMonthlySeries = [];
        $dailyProfitSeries = [];

        $vouchersByDay = VoucherSale::where('status', 'completed')
            ->whereBetween('activated_at', [$targetMonth, $targetMonthEnd])
            ->get()
            ->groupBy(function ($v) {
                return Carbon::parse($v->activated_at)->toDateString();
            });

        $monthlyByDay = MonthlyPayment::where('status', '<>', 'void')
            ->whereDate('billing_month', $targetMonth->toDateString())
            ->get()
            ->groupBy(function ($p) {
                return Carbon::parse($p->paid_at)->toDateString();
            });

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = $targetMonth->copy()->day($day)->toDateString();
            $label = $targetMonth->copy()->day($day)->format('d M');
            $categories[] = $label;

            $vouchers = $vouchersByDay->get($dateStr, collect());
            $vRev = (float) $vouchers->sum('selling_price');
            $vProf = (float) $vouchers->sum('profit');

            $payments = $monthlyByDay->get($dateStr, collect());
            $mRev = (float) $payments->sum('amount_paid');
            $mProf = $mRev * ($totalMonthlyPaid > 0 ? ($totalMonthlyProfit / $totalMonthlyPaid) : 1);

            $dailyVoucherSeries[] = $vRev;
            $dailyMonthlySeries[] = $mRev;
            $dailyProfitSeries[] = round($vProf + $mProf);
        }

        // 5. Payment Methods Distribution
        $methodCash = (float) $monthlyPayments->where('payment_method', 'cash')->sum('amount_paid') + $totalVoucherRevenue;
        $methodTransfer = (float) $monthlyPayments->where('payment_method', 'transfer')->sum('amount_paid');
        $methodQris = (float) $monthlyPayments->where('payment_method', 'qris')->sum('amount_paid');

        // 6. Top Selling Voucher Profiles
        $topVoucherProfiles = VoucherSale::where('status', 'completed')
            ->whereBetween('activated_at', [$targetMonth, $targetMonthEnd])
            ->selectRaw('profile_name, COUNT(*) as count, SUM(selling_price) as revenue, SUM(profit) as profit')
            ->groupBy('profile_name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // 7. Subscription Health
        $subscriptionHealth = [
            'total_active' => $activeCustomersCount,
            'paid_count' => $invoices->where('status', 'paid')->count(),
            'partial_count' => $invoices->where('status', 'partial')->count(),
            'unpaid_count' => $invoices->where('status', 'unpaid')->count(),
        ];

        return [
            'period' => \App\Support\FormatHelper::formatMonthIndo($targetMonth),
            'month_value' => $targetMonth->format('Y-m'),
            'total_gross_income' => $totalGrossIncome,
            'total_net_profit' => $totalNetProfit,
            'total_cost' => $totalOperationalCost,
            'overall_profit_margin' => $overallProfitMargin,
            'arpu' => $arpu,
            'voucher_revenue' => $totalVoucherRevenue,
            'voucher_cost' => $totalVoucherCost,
            'voucher_profit' => $totalVoucherProfit,
            'voucher_count' => $totalVouchersUsed,
            'voucher_margin' => $voucherProfitMargin,
            'monthly_expected' => $totalMonthlyExpected,
            'monthly_paid' => $totalMonthlyPaid,
            'monthly_unpaid' => $totalMonthlyUnpaid,
            'monthly_cost' => $totalMonthlyCost,
            'monthly_profit' => $totalMonthlyProfit,
            'monthly_margin' => $monthlyProfitMargin,
            'monthly_collection_rate' => $monthlyCollectionRate,
            'chart_categories' => $categories,
            'chart_voucher_series' => $dailyVoucherSeries,
            'chart_monthly_series' => $dailyMonthlySeries,
            'chart_profit_series' => $dailyProfitSeries,
            'payment_methods' => [
                'cash' => $methodCash,
                'transfer' => $methodTransfer,
                'qris' => $methodQris,
            ],
            'top_profiles' => $topVoucherProfiles,
            'subscription_health' => $subscriptionHealth,
        ];
    }

    /**
     * Get POS financial overview for specified month.
     */
    public function getOverview(?string $month = null): array
    {
        return $this->getDetailedDashboardAnalytics($month);
    }

    /**
     * Batch or single generate monthly invoices for a given period (e.g. 2026-08).
     */
    public function generateMonthlyInvoices(string $billingMonth, ?array $customerIds = null): array
    {
        $targetMonth = Carbon::parse($billingMonth)->startOfMonth();
        $query = MonthlyCustomer::where('is_active', true);
        if (!empty($customerIds)) {
            $query->whereIn('id', $customerIds);
        }

        $customers = $query->get();
        $generated = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($customers as $c) {
                // Check if invoice already exists for this customer & billing month
                $existing = MonthlyInvoice::where('monthly_customer_id', $c->id)
                    ->whereDate('billing_month', $targetMonth->toDateString())
                    ->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                $seq = MonthlyInvoice::whereDate('billing_month', $targetMonth->toDateString())->count() + 1;
                $invNumber = sprintf('INV-%s-%04d', $targetMonth->format('Ym'), $seq);

                // Set due date based on customer's billing_day (default to 5th or last day of month)
                $day = max(1, min(28, (int) ($c->billing_day ?? 1)));
                $dueDate = $targetMonth->copy()->day($day)->toDateString();

                MonthlyInvoice::create([
                    'invoice_number' => $invNumber,
                    'monthly_customer_id' => $c->id,
                    'billing_month' => $targetMonth->toDateString(),
                    'amount' => (float) $c->monthly_price,
                    'cost_price' => (float) ($c->cost_price ?? 0),
                    'amount_paid' => 0,
                    'balance_due' => (float) $c->monthly_price,
                    'due_date' => $dueDate,
                    'status' => 'unpaid',
                    'notes' => 'Tagihan Periode ' . \App\Support\FormatHelper::formatMonthIndo($targetMonth),
                ]);

                $generated++;
            }

            AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'action' => 'invoices_generated',
                'entity_type' => 'MonthlyInvoice',
                'entity_id' => 0,
                'new_values' => [
                    'billing_month' => $targetMonth->toDateString(),
                    'generated' => $generated,
                    'skipped' => $skipped,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);

            DB::commit();
            return [
                'success' => true,
                'generated' => $generated,
                'skipped' => $skipped,
                'total_customers' => $customers->count(),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Record a direct monthly payment (creates invoice if not exists).
     */
    public function recordMonthlyPayment(array $data): MonthlyPayment
    {
        $customerId = $data['customer_id'];
        $customer = MonthlyCustomer::findOrFail($customerId);
        $billingMonth = Carbon::parse($data['billing_month'] ?? now())->startOfMonth()->toDateString();

        // Prevent duplicate paid transaction for the same month unless voided
        $existingPayment = MonthlyPayment::where('monthly_customer_id', $customerId)
            ->whereDate('billing_month', $billingMonth)
            ->where('status', '<>', 'void')
            ->first();

        if ($existingPayment) {
            throw new Exception("Tagihan periode {$billingMonth} sudah tercatat sebelumnya.");
        }

        $invoice = MonthlyInvoice::firstOrCreate([
            'monthly_customer_id' => $customer->id,
            'billing_month' => $billingMonth,
        ], [
            'invoice_number' => sprintf('INV-%s-%04d', Carbon::parse($billingMonth)->format('Ym'), MonthlyInvoice::whereDate('billing_month', $billingMonth)->count() + 1),
            'amount' => (float) ($customer->monthly_price ?: ($data['amount_paid'] ?? 0)),
            'amount_paid' => 0,
            'balance_due' => (float) ($customer->monthly_price ?: ($data['amount_paid'] ?? 0)),
            'status' => 'unpaid',
            'due_date' => Carbon::parse($billingMonth)->day(max(1, min(28, (int) ($customer->billing_day ?? 1))))->toDateString(),
        ]);

        $payment = $this->recordInvoicePayment($invoice, $data);

        $activeShift = CashierShift::where('status', 'open')->latest()->first();
        if ($activeShift && ($data['payment_method'] ?? 'cash') === 'cash') {
            $payment->update(['shift_id' => $activeShift->id]);
            $this->recalculateShiftExpectedCash($activeShift);
        }

        return $payment;
    }

    /**
     * Record installment or full payment against a specific invoice.
     */
    public function recordInvoicePayment(MonthlyInvoice $invoice, array $data): MonthlyPayment
    {
        $amountPaid = (float) ($data['amount_paid'] ?? $invoice->balance_due);
        if ($amountPaid <= 0) {
            throw new Exception("Nominal pembayaran harus lebih dari 0.");
        }

        if ($amountPaid > ($invoice->balance_due + 0.01)) {
            throw new Exception("Nominal pembayaran (Rp " . number_format($amountPaid, 0, ',', '.') . ") melebihi sisa tagihan (Rp " . number_format($invoice->balance_due, 0, ',', '.') . ").");
        }

        $paidAt = Carbon::parse($data['paid_at'] ?? now())->toDateString();
        $method = $data['payment_method'] ?? 'cash';

        DB::beginTransaction();
        try {
            // Create payment record
            $payment = MonthlyPayment::create([
                'monthly_customer_id' => $invoice->monthly_customer_id,
                'monthly_invoice_id' => $invoice->id,
                'billing_month' => $invoice->billing_month,
                'monthly_price' => $invoice->amount,
                'amount_paid' => $amountPaid,
                'paid_at' => $paidAt,
                'payment_method' => $method,
                'status' => 'paid',
                'recorded_by_user_id' => auth()->id() ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Update invoice balances and status
            $newAmountPaid = (float) ($invoice->amount_paid + $amountPaid);
            $newBalanceDue = max(0, (float) ($invoice->amount - $newAmountPaid));
            $newStatus = ($newBalanceDue <= 0) ? 'paid' : 'partial';

            $invoice->update([
                'amount_paid' => $newAmountPaid,
                'balance_due' => $newBalanceDue,
                'status' => $newStatus,
            ]);

            AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'action' => 'invoice_payment_recorded',
                'entity_type' => 'MonthlyPayment',
                'entity_id' => $payment->id,
                'new_values' => [
                    'invoice_number' => $invoice->invoice_number,
                    'customer' => $invoice->customer?->name,
                    'amount_paid' => $amountPaid,
                    'balance_remaining' => $newBalanceDue,
                    'status' => $newStatus,
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
     * Void / cancel a payment and restore invoice balances.
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

            // If payment was linked to an invoice, restore invoice balance
            if ($payment->monthly_invoice_id) {
                $invoice = MonthlyInvoice::find($payment->monthly_invoice_id);
                if ($invoice) {
                    $newAmountPaid = max(0, (float) ($invoice->amount_paid - $payment->amount_paid));
                    $newBalanceDue = max(0, (float) ($invoice->amount - $newAmountPaid));
                    $newStatus = ($newAmountPaid <= 0) ? 'unpaid' : (($newBalanceDue <= 0) ? 'paid' : 'partial');

                    $invoice->update([
                        'amount_paid' => $newAmountPaid,
                        'balance_due' => $newBalanceDue,
                        'status' => $newStatus,
                    ]);
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
