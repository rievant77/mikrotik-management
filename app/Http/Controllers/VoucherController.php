<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\HotspotProfile;
use App\Models\HotspotUser;
use App\Services\RouterOsService;
use App\Services\VoucherBatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    /**
     * Vouchers Inventory & Management View.
     */
    public function index(Request $request, RouterOsService $routerOs): View
    {
        // 1. Fetch live active sessions & reconcile disabled status with MikroTik
        $activeUserMap = [];
        if (!app()->environment('testing')) {
            try {
                $activeSessions = $routerOs->getActiveHotspotSessions();
                foreach ($activeSessions as $s) {
                    if (!empty($s['user'])) {
                        $activeUserMap[$s['user']] = $s;
                    }
                }

                $remoteUsers = $routerOs->getHotspotUsers();
                if (!empty($remoteUsers)) {
                    foreach ($remoteUsers as $u) {
                        $uname = $u['name'] ?? null;
                        if (!$uname || $uname === 'default-trial') continue;
                        $isDisabled = isset($u['disabled']) && in_array(strtolower((string)$u['disabled']), ['true', 'yes', '1'], true);
                        HotspotUser::where('username', $uname)->update(['is_active' => !$isDisabled]);
                    }
                }

                // Sweep and process expired users according to their profile expired_mode
                app(\App\Services\VoucherExpiryService::class)->sweepExpiredUsers($routerOs);
            } catch (\Throwable $e) {
                // Non-blocking
            }
        }

        $search = $request->get('search');
        $profileId = $request->get('profile_id');
        $batch = $request->get('batch');
        $status = $request->get('status');
        if (empty($status)) {
            $status = 'active'; // Default: hanya voucher aktif
        }

        $query = HotspotUser::with('profile');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('password', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        if ($profileId) {
            $query->where('profile_id', $profileId);
        }

        if ($batch) {
            $query->where('comment', 'like', "%{$batch}%");
        }

        if ($status === 'online') {
            $query->where('is_active', true);
            if (!empty($activeUserMap)) {
                $query->whereIn('username', array_keys($activeUserMap));
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($status === 'inactive' || $status === 'disabled') {
            $query->where('is_active', false);
        } elseif ($status === 'active') {
            $query->where('is_active', true);
        } // if $status === 'all', don't filter is_active

        $vouchers = $query->latest('id')->paginate(50)->withQueryString();

        // 2. Fetch KPI statistics
        $allUsers = HotspotUser::with('profile')->get();
        $totalVouchers = $allUsers->count();
        $onlineCount = count($activeUserMap);
        $totalBatches = $allUsers->pluck('comment')->filter()->unique()->count();
        $inventoryValue = $allUsers->sum(function ($u) {
            return $u->profile?->selling_price ?? 3000;
        });

        $stats = [
            'total' => $totalVouchers,
            'online' => $onlineCount,
            'batches' => $totalBatches,
            'inventory_value' => $inventoryValue,
        ];

        // 3. Profiles and Batches for filters
        $profiles = HotspotProfile::all();
        $batches = HotspotUser::whereNotNull('comment')
            ->where('comment', '!=', '')
            ->selectRaw('comment, MAX(id) as max_id')
            ->groupBy('comment')
            ->orderByDesc('max_id')
            ->limit(30)
            ->pluck('comment');

        return view('vouchers.index', compact(
            'vouchers',
            'stats',
            'profiles',
            'batches',
            'activeUserMap'
        ));
    }

    public function generateView(RouterOsService $routerOs): View
    {
        $profiles = HotspotProfile::all();
        $servers = [];
        try {
            $servers = $routerOs->getHotspotServers();
        } catch (\Throwable $e) {
            $servers = [];
        }

        return view('hotspot.generate', compact('profiles', 'servers'));
    }

    public function generateBatch(Request $request, VoucherBatchService $service): JsonResponse
    {
        $validated = $request->validate([
            'qty' => 'required|integer|min:1|max:1000',
            'server' => 'nullable|string|max:50',
            'credentialMode' => 'required|string|in:same,different',
            'length' => 'required|integer|min:3|max:12',
            'prefix' => 'nullable|string|max:20',
            'charSet' => 'required|string|in:numeric,lowercase,uppercase,mixed',
            'profile' => 'required|string',
            'timeLimit' => 'nullable|string|max:50',
            'dataLimit' => 'nullable|string|max:50',
            'comment' => 'nullable|string|max:100',
        ]);

        $result = $service->generate($validated);
        return response()->json($result);
    }

    /**
     * Batch delete selected vouchers from DB and RouterOS.
     */
    public function destroyBatch(Request $request, RouterOsService $routerOs): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required_without:users|array',
            'ids.*' => 'integer',
            'users' => 'required_without:ids|array',
            'users.*' => 'string',
        ]);

        $ids = $request->input('ids', []);
        $usernames = $request->input('users', []);

        $query = HotspotUser::query();
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        } elseif (!empty($usernames)) {
            $query->whereIn('username', $usernames);
        }

        $vouchers = $query->get();
        $count = 0;

        foreach ($vouchers as $v) {
            try {
                $routerOs->removeHotspotUser($v->username);
            } catch (\Throwable $e) {}

            $v->delete();
            $count++;
        }

        AuditLog::create([
            'user_id' => auth()->id() ?? null,
            'action' => 'vouchers_batch_deleted',
            'entity_type' => 'HotspotUser',
            'new_values' => ['deleted_count' => $count],
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} Voucher berhasil dihapus dari sistem & router MikroTik.",
            'deleted_count' => $count,
        ]);
    }

    public function printGrid(Request $request): View
    {
        $batch = $request->get('batch');
        $idsParam = $request->get('ids');
        $usersParam = $request->get('users');
        $limit = max(1, min(500, (int) $request->get('limit', 100)));
        $routerSetting = \App\Models\RouterSetting::where('is_active', true)->first();

        $query = HotspotUser::with('profile');

        if ($idsParam) {
            $ids = is_array($idsParam) ? $idsParam : explode(',', $idsParam);
            $query->whereIn('id', array_filter($ids));
        } elseif ($usersParam) {
            $users = is_array($usersParam) ? $usersParam : explode(',', $usersParam);
            $query->whereIn('username', array_filter($users));
        } elseif ($batch) {
            $query->where('comment', 'like', "%{$batch}%");
        }

        $vouchers = $query->latest()->limit($limit)->get();

        return view('vouchers.print-grid', compact('vouchers', 'batch', 'routerSetting'));
    }

    public function print58mm(Request $request): View
    {
        $username = $request->get('user');
        $idsParam = $request->get('ids');
        $usersParam = $request->get('users');
        $batch = $request->get('batch');
        $routerSetting = \App\Models\RouterSetting::where('is_active', true)->first();

        $query = HotspotUser::with('profile');

        if ($idsParam) {
            $ids = is_array($idsParam) ? $idsParam : explode(',', $idsParam);
            $query->whereIn('id', array_filter($ids));
            $vouchers = $query->latest()->get();
        } elseif ($usersParam) {
            $users = is_array($usersParam) ? $usersParam : explode(',', $usersParam);
            $query->whereIn('username', array_filter($users));
            $vouchers = $query->latest()->get();
        } elseif ($batch) {
            $query->where('comment', 'like', "%{$batch}%");
            $vouchers = $query->latest()->limit(100)->get();
        } elseif ($username) {
            $vouchers = $query->where('username', $username)->get();
        } else {
            $vouchers = $query->latest()->limit(1)->get();
        }

        return view('vouchers.print-58mm', compact('vouchers', 'routerSetting'));
    }

    public function print80mm(Request $request): View
    {
        $username = $request->get('user');
        $idsParam = $request->get('ids');
        $usersParam = $request->get('users');
        $batch = $request->get('batch');
        $routerSetting = \App\Models\RouterSetting::where('is_active', true)->first();

        $query = HotspotUser::with('profile');

        if ($idsParam) {
            $ids = is_array($idsParam) ? $idsParam : explode(',', $idsParam);
            $query->whereIn('id', array_filter($ids));
            $vouchers = $query->latest()->get();
        } elseif ($usersParam) {
            $users = is_array($usersParam) ? $usersParam : explode(',', $usersParam);
            $query->whereIn('username', array_filter($users));
            $vouchers = $query->latest()->get();
        } elseif ($batch) {
            $query->where('comment', 'like', "%{$batch}%");
            $vouchers = $query->latest()->limit(100)->get();
        } elseif ($username) {
            $vouchers = $query->where('username', $username)->get();
        } else {
            $vouchers = $query->latest()->limit(1)->get();
        }

        return view('vouchers.print-80mm', compact('vouchers', 'routerSetting'));
    }
}

