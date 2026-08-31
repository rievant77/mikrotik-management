<?php

namespace App\Http\Controllers;

use App\Models\CashierShift;
use App\Models\MonthlyCustomer;
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
        $overview = $this->posService->getOverview($request->month);
        $recentVouchers = VoucherSale::where('status', 'completed')->latest('activated_at')->limit(5)->get();
        $recentPayments = MonthlyPayment::with('customer')->where('status', '<>', 'void')->latest('paid_at')->limit(5)->get();

        return view('pos.index', compact('overview', 'recentVouchers', 'recentPayments'));
    }

    public function vouchers(Request $request): View
    {
        $month = $request->get('month');
        $targetMonth = $month ? \Carbon\Carbon::parse($month)->startOfMonth() : now()->startOfMonth();

        $salesQuery = VoucherSale::latest('activated_at');
        if ($month) {
            $salesQuery->whereBetween('activated_at', [$targetMonth, $targetMonth->copy()->endOfMonth()]);
        }

        $sales = $salesQuery->paginate(50)->withQueryString();

        $monthSales = VoucherSale::where('status', 'completed')
            ->whereBetween('activated_at', [$targetMonth, $targetMonth->copy()->endOfMonth()]);

        $metrics = [
            'period' => \App\Support\FormatHelper::formatMonthIndo($targetMonth),
            'total_count' => (clone $monthSales)->count(),
            'total_revenue' => (float) (clone $monthSales)->sum('selling_price'),
            'total_profit' => (float) (clone $monthSales)->sum('profit'),
        ];

        return view('pos.vouchers', compact('sales', 'metrics'));
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

        $customers = MonthlyCustomer::with(['hotspotUser.profile'])->orderBy('name')->get();
        $hotspotUsers = \App\Models\HotspotUser::with('profile')->orderBy('username')->get();

        return view('pos.monthly', compact('invoices', 'metrics', 'customers', 'hotspotUsers', 'selectedMonth'));
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
            'contact' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'billing_day' => 'nullable|integer|min:1|max:31',
        ]);

        $customer = MonthlyCustomer::create($validated);
        $customer->load('hotspotUser.profile');

        return response()->json(['success' => true, 'customer' => $customer]);
    }

    public function updateCustomer(Request $request, MonthlyCustomer $customer): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'hotspot_user_id' => 'nullable|exists:hotspot_users,id',
            'contact' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'billing_day' => 'nullable|integer|min:1|max:31',
        ]);

        $customer->update($validated);
        $customer->load('hotspotUser.profile');

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
