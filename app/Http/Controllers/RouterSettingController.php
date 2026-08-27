<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\RouterSetting;
use App\Services\RouterOsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RouterSettingController extends Controller
{
    public function index(): View
    {
        $setting = RouterSetting::firstOrCreate(
            ['is_active' => true],
            [
                'name' => 'CCR1009-Core',
                'host' => '192.168.88.1',
                'api_port' => 8728,
                'username' => 'api_admin',
                'password' => 'admin',
                'use_ssl' => false,
                'is_active' => true,
                'connection_timeout_ms' => 3000,
            ]
        );

        return view('settings.router', compact('setting'));
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'host' => 'required|string|max:100',
            'api_port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:100',
            'password' => 'nullable|string',
            'use_ssl' => 'boolean',
            'is_active' => 'boolean',
            'connection_timeout_ms' => 'nullable|integer',
        ]);

        $setting = RouterSetting::where('is_active', true)->first();
        if (!$setting) {
            $setting = new RouterSetting();
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $setting->fill($validated);
        $setting->save();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'router_setting_updated',
            'entity_type' => 'RouterSetting',
            'entity_id' => $setting->id,
            'new_values' => [
                'name' => $setting->name,
                'host' => $setting->host,
                'port' => $setting->api_port,
            ],
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'setting' => $setting]);
    }

    public function testConnection(Request $request, RouterOsService $routerOs): JsonResponse
    {
        $setting = RouterSetting::where('is_active', true)->first();
        if ($request->filled('host')) {
            $setting = new RouterSetting($request->all());
        }

        $result = $routerOs->testConnection($setting);
        return response()->json($result);
    }

    public function syncAll(RouterOsService $routerOs, \App\Services\CollectorService $collector): JsonResponse
    {
        $setting = RouterSetting::where('is_active', true)->first();
        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Konfigurasi router belum ada'], 422);
        }

        $conn = $routerOs->testConnection($setting);
        if (!$conn['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke router: ' . ($conn['message'] ?? 'Periksa IP/Port/User/Password')
            ], 422);
        }

        // 1. Sync Profiles
        $profiles = $routerOs->getHotspotProfiles();
        $profilesSynced = 0;
        foreach ($profiles as $p) {
            $name = $p['name'] ?? null;
            if (!$name || $name === 'default') continue;
            \App\Models\HotspotProfile::updateOrCreate(
                ['name' => $name],
                [
                    'rate_limit' => $p['rate-limit'] ?? null,
                    'shared_users' => isset($p['shared-users']) ? (int)$p['shared-users'] : 1,
                    'selling_price' => 5000,
                    'cost_price' => 2500,
                    'validity' => '3 Jam',
                    'expired_mode' => 'Remove',
                ]
            );
            $profilesSynced++;
        }

        // 2. Sync Users
        $users = $routerOs->getHotspotUsers();
        $usersSynced = 0;
        foreach ($users as $u) {
            $uname = $u['name'] ?? null;
            if (!$uname || $uname === 'default-trial') continue;
            $profileName = $u['profile'] ?? null;
            $prof = $profileName ? \App\Models\HotspotProfile::where('name', $profileName)->first() : null;

            \App\Models\HotspotUser::updateOrCreate(
                ['username' => $uname],
                [
                    'password' => $u['password'] ?? $uname,
                    'profile_id' => $prof?->id,
                    'uptime_limit' => $u['limit-uptime'] ?? null,
                    'comment' => $u['comment'] ?? null,
                    'is_active' => true,
                ]
            );
            $usersSynced++;
        }

        // 3. Poll Active Sessions & accounting
        $collectorResult = $collector->collect();

        $sessionCount = $collectorResult['sessions_processed'] ?? ($collectorResult['sessions_count'] ?? 0);

        return response()->json([
            'success' => true,
            'profiles_synced' => $profilesSynced,
            'users_synced' => $usersSynced,
            'collector' => $collectorResult,
            'router_info' => $conn,
            'message' => "Sinkronisasi berhasil: {$profilesSynced} profil, {$usersSynced} user, {$sessionCount} sesi aktif."
        ]);
    }
}
