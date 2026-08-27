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
        $sales = VoucherSale::with('shift')
            ->latest('activated_at')
            ->paginate(50);

        return view('pos.vouchers', compact('sales'));
    }

    public function monthly(Request $request): View
    {
        $customers = MonthlyCustomer::with(['payments' => function ($q) {
            $q->whereDate('billing_month', now()->startOfMonth()->toDateString());
        }])->get();

        return view('pos.monthly', compact('customers'));
    }

    public function storeCustomer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'username' => 'nullable|string|max:100',
            'contact' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'billing_day' => 'nullable|integer|min:1|max:31',
        ]);

        $customer = MonthlyCustomer::create($validated);
        return response()->json(['success' => true, 'customer' => $customer]);
    }

    public function updateCustomer(Request $request, MonthlyCustomer $customer): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'username' => 'nullable|string|max:100',
            'contact' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'billing_day' => 'nullable|integer|min:1|max:31',
        ]);

        $customer->update($validated);
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
