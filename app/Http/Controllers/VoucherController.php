<?php

namespace App\Http\Controllers;

use App\Models\HotspotProfile;
use App\Models\HotspotUser;
use App\Services\VoucherBatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function generateView(): View
    {
        $profiles = HotspotProfile::all();
        return view('hotspot.generate', compact('profiles'));
    }

    public function generateBatch(Request $request, VoucherBatchService $service): JsonResponse
    {
        $validated = $request->validate([
            'profile' => 'required|string',
            'qty' => 'required|integer|min:1|max:1000',
            'credentialMode' => 'required|string|in:same,different',
            'charSet' => 'required|string|in:numeric,lowercase,uppercase,mixed',
            'prefix' => 'nullable|string|max:20',
            'length' => 'required|integer|min:3|max:12',
        ]);

        $result = $service->generate($validated);
        return response()->json($result);
    }

    public function printGrid(Request $request): View
    {
        $vouchers = HotspotUser::with('profile')
            ->where('comment', 'like', 'Batch %')
            ->latest()
            ->limit(24)
            ->get();

        return view('vouchers.print-grid', compact('vouchers'));
    }

    public function print58mm(Request $request): View
    {
        $voucher = HotspotUser::with('profile')->latest()->first();
        return view('vouchers.print-58mm', compact('voucher'));
    }

    public function print80mm(Request $request): View
    {
        $voucher = HotspotUser::with('profile')->latest()->first();
        return view('vouchers.print-80mm', compact('voucher'));
    }
}
