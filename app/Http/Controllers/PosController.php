<?php

namespace App\Http\Controllers;

use App\Models\CashierShift;
use App\Models\DailyExpense;
use App\Models\HotspotProfile;
use App\Models\MonthlyCustomer;
use App\Models\MonthlyExpenseEntry;
use App\Models\MonthlyExpenseTemplate;
use App\Models\MonthlyPayment;
use App\Models\VoucherSale;
use App\Services\PosManagerService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    protected PosManagerService $posService;

    public function __construct(PosManagerService $posService)
    {
        $this->posService = $posService;
    }

    public function index(Request $request): View
    {
        $overview = $this->posService->getDetailedDashboardAnalytics($request->month);
        $recentVouchers = VoucherSale::where('status', 'completed')->latest('activated_at')->limit(5)->get();
        $recentPayments = MonthlyPayment::with('customer')->where('status', '<>', 'void')->latest('paid_at')->limit(5)->get();
        $currentShift = CashierShift::with('user')->where('status', 'open')->latest()->first();
        if ($currentShift) {
            $this->posService->recalculateShiftExpectedCash($currentShift);
        }

        return view('pos.index', compact('overview', 'recentVouchers', 'recentPayments', 'currentShift'));
    }

    public function vouchers(Request $request): View
    {
        $month = $request->get('month', now()->format('Y-m'));
        $search = $request->get('search');
        $profileName = $request->get('profile');
        $category = $request->get('category', 'all');

        $salesQuery = VoucherSale::with('profile')->latest('activated_at');

        if ($month && $month !== 'all') {
            $targetMonth = \Carbon\Carbon::parse($month)->startOfMonth();
            $salesQuery->whereBetween('activated_at', [$targetMonth, $targetMonth->copy()->endOfMonth()]);
        } else {
            $targetMonth = now()->startOfMonth();
        }

        if ($search) {
            $salesQuery->where('username', 'like', "%{$search}%");
        }

        if ($profileName && $profileName !== 'all') {
            $salesQuery->where('profile_name', $profileName);
        }

        if ($category === 'profit') {
            $salesQuery->forRevenue();
        } elseif ($category === 'pribadi') {
            $salesQuery->whereHas('profile', function ($q) {
                $q->where('category', 'pribadi');
            });
        }

        $sales = $salesQuery->paginate(50)->withQueryString();

        // Calculate metrics for commercial revenue
        $monthSalesQuery = VoucherSale::where('status', 'completed')->forRevenue();

        if ($month && $month !== 'all') {
            $monthSalesQuery->whereBetween('activated_at', [$targetMonth, $targetMonth->copy()->endOfMonth()]);
        }

        $totalCount = (int) (clone $monthSalesQuery)->count();
        $totalRevenue = (float) (clone $monthSalesQuery)->sum('selling_price');
        $totalProfit = (float) (clone $monthSalesQuery)->sum('profit');
        $totalCost = (float) (clone $monthSalesQuery)->sum('cost_price');
        $margin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0;
        $avgRevenue = $totalCount > 0 ? round($totalRevenue / $totalCount) : 0;

        $metrics = [
            'period' => ($month === 'all') ? 'Semua Waktu' : \App\Support\FormatHelper::formatMonthIndo($targetMonth),
            'month_value' => ($month === 'all') ? 'all' : $targetMonth->format('Y-m'),
            'total_count' => $totalCount,
            'total_revenue' => $totalRevenue,
            'total_profit' => $totalProfit,
            'total_cost' => $totalCost,
            'margin' => $margin,
            'avg_revenue' => $avgRevenue,
        ];

        $currentShift = CashierShift::with('user')->where('status', 'open')->latest()->first();
        if ($currentShift) {
            $this->posService->recalculateShiftExpectedCash($currentShift);
        }

        $profiles = HotspotProfile::orderBy('name')->get();

        return view('pos.vouchers', compact('sales', 'metrics', 'profiles', 'currentShift', 'month', 'search', 'profileName', 'category'));
    }

    public function monthly(Request $request): View
    {
        $selectedMonth = $request->get('month', now()->format('Y-m'));
        $targetMonth = \Carbon\Carbon::parse($selectedMonth)->startOfMonth();

        $invoicesQuery = \App\Models\MonthlyInvoice::with([
            'customer.hotspotUser.profile',
            'payments' => function ($q) {
                $q->where('status', '<>', 'void')->latest('paid_at');
            }
        ])->whereDate('billing_month', $targetMonth->toDateString());

        if ($request->filled('status') && $request->status !== 'all') {
            $invoicesQuery->where('status', $request->status);
        }

        $invoices = $invoicesQuery->orderBy('invoice_number')->get();

        $allMonthInvoices = \App\Models\MonthlyInvoice::whereDate('billing_month', $targetMonth->toDateString())
            ->where('status', '<>', 'cancelled')
            ->get();

        $metrics = [
            'period' => \App\Support\FormatHelper::formatMonthIndo($targetMonth),
            'month_value' => $targetMonth->format('Y-m'),
            'total_count' => $allMonthInvoices->count(),
            'total_amount' => (float) $allMonthInvoices->sum('amount'),
            'total_paid' => (float) $allMonthInvoices->sum('amount_paid'),
            'total_unpaid' => (float) $allMonthInvoices->sum('balance_due'),
            'paid_count' => $allMonthInvoices->where('status', 'paid')->count(),
            'partial_count' => $allMonthInvoices->where('status', 'partial')->count(),
            'unpaid_count' => $allMonthInvoices->where('status', 'unpaid')->count(),
        ];

        $customers = MonthlyCustomer::with(['profile', 'hotspotUser.profile'])->orderBy('name')->get();
        $hotspotUsers = \App\Models\HotspotUser::with('profile')->orderBy('username')->get();
        $monthlyProfiles = HotspotProfile::where('is_active', true)->where('category', 'bulanan')->get();

        $currentShift = CashierShift::with('user')->where('status', 'open')->latest()->first();
        if ($currentShift) {
            $this->posService->recalculateShiftExpectedCash($currentShift);
        }

        return view('pos.monthly', compact('invoices', 'metrics', 'customers', 'hotspotUsers', 'monthlyProfiles', 'selectedMonth', 'currentShift'));
    }

    public function generateInvoices(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'billing_month' => 'required|string',
            'customer_ids' => 'nullable|array',
            'customer_ids.*' => 'exists:monthly_customers,id',
        ]);

        try {
            $result = $this->posService->generateMonthlyInvoices($validated['billing_month'], $validated['customer_ids'] ?? null);
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function payInvoice(\App\Models\MonthlyInvoice $invoice, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:1',
            'paid_at' => 'required|date',
            'payment_method' => 'required|string|in:cash,transfer,qris',
            'notes' => 'nullable|string',
        ]);

        try {
            $payment = $this->posService->recordInvoicePayment($invoice, $validated);
            $invoice->refresh()->load([
                'customer.hotspotUser.profile',
                'payments' => function ($q) {
                    $q->where('status', '<>', 'void')->latest('paid_at');
                }
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dicatat',
                'payment' => $payment,
                'invoice' => $invoice,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function invoicePayments(\App\Models\MonthlyInvoice $invoice): JsonResponse
    {
        $payments = $invoice->payments()->latest('paid_at')->get();
        return response()->json(['success' => true, 'payments' => $payments]);
    }

    public function storeCustomer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'hotspot_user_id' => 'nullable|exists:hotspot_users,id',
            'profile_id' => 'nullable|exists:hotspot_profiles,id',
            'contact' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'billing_day' => 'nullable|integer|min:1|max:31',
        ]);

        $customer = MonthlyCustomer::create($validated);
        $customer->load(['profile', 'hotspotUser.profile']);

        return response()->json(['success' => true, 'customer' => $customer]);
    }

    public function updateCustomer(Request $request, MonthlyCustomer $customer): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'hotspot_user_id' => 'nullable|exists:hotspot_users,id',
            'profile_id' => 'nullable|exists:hotspot_profiles,id',
            'contact' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'billing_day' => 'nullable|integer|min:1|max:31',
        ]);

        $customer->update($validated);
        $customer->load(['profile', 'hotspotUser.profile']);

        return response()->json(['success' => true, 'customer' => $customer]);
    }

    public function deleteCustomer(MonthlyCustomer $customer): JsonResponse
    {
        $customer->delete();
        return response()->json(['success' => true, 'message' => 'Customer berhasil dihapus']);
    }

    public function storePayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:monthly_customers,id',
            'billing_month' => 'required|string',
            'amount_paid' => 'required|numeric|min:0',
            'paid_at' => 'required|date',
            'payment_method' => 'required|string|in:cash,transfer,qris',
            'notes' => 'nullable|string',
        ]);

        try {
            $payment = $this->posService->recordMonthlyPayment($validated);
            return response()->json(['success' => true, 'payment' => $payment]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function voidPayment(MonthlyPayment $payment, Request $request): JsonResponse
    {
        try {
            $this->posService->voidPayment($payment, $request->get('reason', 'Pembatalan kasir'));
            return response()->json(['success' => true, 'message' => 'Pembayaran berhasil dibatalkan (VOID)']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function expenses(Request $request): View
    {
        $selectedMonth = $request->get('month', now()->format('Y-m'));
        $expenseData = $this->posService->getExpensesForMonth($selectedMonth);
        $templates = MonthlyExpenseTemplate::orderBy('name')->get();

        return view('pos.expenses', compact('expenseData', 'selectedMonth', 'templates'));
    }

    public function storeDailyExpense(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
        ]);

        $expense = DailyExpense::create([
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'expense' => $expense]);
    }

    public function updateDailyExpense(Request $request, DailyExpense $dailyExpense): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
        ]);

        $dailyExpense->update($validated);

        return response()->json(['success' => true, 'expense' => $dailyExpense]);
    }

    public function deleteDailyExpense(DailyExpense $dailyExpense): JsonResponse
    {
        $dailyExpense->delete();
        return response()->json(['success' => true]);
    }

    public function storeExpenseTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $template = MonthlyExpenseTemplate::create([
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json(['success' => true, 'template' => $template]);
    }

    public function updateExpenseTemplate(Request $request, MonthlyExpenseTemplate $template): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        return response()->json(['success' => true, 'template' => $template]);
    }

    public function deleteExpenseTemplate(MonthlyExpenseTemplate $template): JsonResponse
    {
        $template->delete();
        return response()->json(['success' => true]);
    }

    public function updateMonthlyExpenseEntry(Request $request, MonthlyExpenseEntry $entry): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $entry->update($validated);

        return response()->json(['success' => true, 'entry' => $entry]);
    }

    public function shifts(): View
    {
        $currentShift = CashierShift::where('status', 'open')->latest()->first();
        if ($currentShift) {
            $this->posService->recalculateShiftExpectedCash($currentShift);
        }
        $pastShifts = CashierShift::where('status', 'closed')->latest('closed_at')->limit(15)->get();

        return view('pos.shifts', compact('currentShift', 'pastShifts'));
    }

    public function openShift(Request $request): JsonResponse
    {
        $opening = (float) $request->get('opening_cash', 0);
        $shift = $this->posService->openShift($opening);
        return response()->json(['success' => true, 'shift' => $shift]);
    }

    public function closeShift(Request $request): JsonResponse
    {
        $shift = CashierShift::where('status', 'open')->latest()->firstOrFail();
        $actualCash = (float) $request->get('actual_cash', 0);
        $closed = $this->posService->closeShift($shift, $actualCash);

        return response()->json(['success' => true, 'shift' => $closed]);
    }
}
