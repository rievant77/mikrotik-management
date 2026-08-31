<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\HotspotProfile;
use App\Services\RouterOsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotspotProfileController extends Controller
{
    public function index(RouterOsService $routerOs): View
    {
        // Auto-reconcile with MikroTik router so any changes in Winbox reflect safely
        if (!app()->environment('testing')) {
            try {
                $remoteProfiles = $routerOs->getHotspotProfiles();
                if (!empty($remoteProfiles)) {
                    $remoteProfileNames = [];
                    foreach ($remoteProfiles as $p) {
                        $name = $p['name'] ?? null;
                        if (!$name || $name === 'default') continue;
                        $remoteProfileNames[] = $name;

                        $rateLimit = $p['rate-limit'] ?? null;
                        $rawShared = $p['shared-users'] ?? 1;
                        $sharedUsers = ($rawShared === 'unlimited' || $rawShared === '0' || (int)$rawShared === 0) ? 0 : (int)$rawShared;

                        $existing = HotspotProfile::where('name', $name)->first();
                        if ($existing) {
                            // Update technical router parameters ONLY; preserve configured selling_price, cost_price, validity, expired_mode
                            $existing->update([
                                'rate_limit' => $rateLimit,
                                'shared_users' => $sharedUsers,
                            ]);
                        } else {
                            HotspotProfile::create([
                                'name' => $name,
                                'rate_limit' => $rateLimit,
                                'shared_users' => $sharedUsers,
                                'selling_price' => 0,
                                'cost_price' => 0,
                                'validity' => 'Unlimited',
                                'expired_mode' => 'Remove',
                                'is_active' => true,
                            ]);
                        }
                    }

                    if (!empty($remoteProfileNames)) {
                        HotspotProfile::whereNotIn('name', $remoteProfileNames)->delete();
                    }
                }
            } catch (\Throwable $e) {
                // Non-blocking if router connection fails
            }
        }

        $profiles = HotspotProfile::all();
        return view('hotspot.profiles', compact('profiles'));
    }

    public function store(Request $request, RouterOsService $routerOs): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:hotspot_profiles,name',
            'rate_limit' => 'nullable|string|max:100',
            'shared_users' => 'nullable|integer|min:0',
            'validity' => 'nullable|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'expired_mode' => 'nullable|string|max:50',
            'fup_enabled' => 'nullable|boolean',
            'fup_limit_display' => 'nullable|string|max:50',
            'fup_rate_limit' => 'nullable|string|max:50',
            'fup_reset_cycle' => 'nullable|string|max:30',
        ]);

        $validated['rate_limit'] = !empty($validated['rate_limit']) ? trim($validated['rate_limit']) : null;
        $validated['selling_price'] = $validated['selling_price'] ?? 0;
        $validated['cost_price'] = $validated['cost_price'] ?? 0;
        $validated['shared_users'] = isset($validated['shared_users']) && $validated['shared_users'] !== '' ? (int)$validated['shared_users'] : 0;
        $validated['fup_enabled'] = filter_var($request->input('fup_enabled', false), FILTER_VALIDATE_BOOLEAN);
        $validated['fup_limit_display'] = !empty($validated['fup_limit_display']) ? trim($validated['fup_limit_display']) : null;
        $validated['fup_limit_bytes'] = $validated['fup_limit_display'] ? \App\Support\FormatHelper::parseBytes($validated['fup_limit_display']) : null;
        $validated['fup_rate_limit'] = !empty($validated['fup_rate_limit']) ? trim($validated['fup_rate_limit']) : null;
        $validated['fup_reset_cycle'] = !empty($validated['fup_reset_cycle']) ? trim($validated['fup_reset_cycle']) : 'daily';

        $profile = HotspotProfile::create($validated);

        // Sync to RouterOS
        $routerOs->addHotspotProfile(
            $profile->name,
            $profile->rate_limit,
            $profile->shared_users
        );

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'profile_created',
            'entity_type' => 'HotspotProfile',
            'entity_id' => $profile->id,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'profile' => $profile]);
    }

    public function update(Request $request, HotspotProfile $profile, RouterOsService $routerOs): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:hotspot_profiles,name,' . $profile->id,
            'rate_limit' => 'nullable|string|max:100',
            'shared_users' => 'nullable|integer|min:0',
            'validity' => 'nullable|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'expired_mode' => 'nullable|string|max:50',
            'fup_enabled' => 'nullable|boolean',
            'fup_limit_display' => 'nullable|string|max:50',
            'fup_rate_limit' => 'nullable|string|max:50',
            'fup_reset_cycle' => 'nullable|string|max:30',
        ]);

        $validated['rate_limit'] = !empty($validated['rate_limit']) ? trim($validated['rate_limit']) : null;
        $validated['selling_price'] = $validated['selling_price'] ?? 0;
        $validated['cost_price'] = $validated['cost_price'] ?? 0;
        $validated['shared_users'] = isset($validated['shared_users']) && $validated['shared_users'] !== '' ? (int)$validated['shared_users'] : 0;
        $validated['fup_enabled'] = filter_var($request->input('fup_enabled', false), FILTER_VALIDATE_BOOLEAN);
        $validated['fup_limit_display'] = !empty($validated['fup_limit_display']) ? trim($validated['fup_limit_display']) : null;
        $validated['fup_limit_bytes'] = $validated['fup_limit_display'] ? \App\Support\FormatHelper::parseBytes($validated['fup_limit_display']) : null;
        $validated['fup_rate_limit'] = !empty($validated['fup_rate_limit']) ? trim($validated['fup_rate_limit']) : null;
        $validated['fup_reset_cycle'] = !empty($validated['fup_reset_cycle']) ? trim($validated['fup_reset_cycle']) : 'daily';

        $old = $profile->toArray();
        $profile->update($validated);

        // Sync to RouterOS
        $routerOs->updateHotspotProfile(
            $profile->name,
            [
                'rate_limit' => $profile->rate_limit,
                'shared_users' => $profile->shared_users
            ]
        );

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'profile_updated',
            'entity_type' => 'HotspotProfile',
            'entity_id' => $profile->id,
            'old_values' => $old,
            'new_values' => $validated,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'profile' => $profile]);
    }

    public function toggleStatus(HotspotProfile $profile): JsonResponse
    {
        $profile->is_active = !$profile->is_active;
        $profile->save();

        return response()->json([
            'success' => true,
            'is_active' => $profile->is_active,
            'message' => "Status profile {$profile->name} berhasil diubah menjadi " . ($profile->is_active ? 'Aktif' : 'Non-aktif')
        ]);
    }

    public function destroy(HotspotProfile $profile, Request $request, RouterOsService $routerOs): JsonResponse
    {
        $name = $profile->name;
        $routerOs->removeHotspotProfile($name);
        $profile->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'profile_deleted',
            'entity_type' => 'HotspotProfile',
            'old_values' => ['name' => $name],
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => "Profile {$name} berhasil dihapus"]);
    }

    public function syncFromRouter(RouterOsService $routerOs): JsonResponse
    {
        $remoteProfiles = $routerOs->getHotspotProfiles();
        $syncedCount = 0;
        $remoteProfileNames = [];

        foreach ($remoteProfiles as $p) {
            $name = $p['name'] ?? null;
            if (!$name || $name === 'default') {
                continue;
            }

            $remoteProfileNames[] = $name;
            $rateLimit = $p['rate-limit'] ?? null;
            $rawShared = $p['shared-users'] ?? 1;
            $sharedUsers = ($rawShared === 'unlimited' || $rawShared === '0' || (int)$rawShared === 0) ? 0 : (int)$rawShared;

            $existing = HotspotProfile::where('name', $name)->first();
            if ($existing) {
                // Update technical router parameters ONLY; preserve configured selling_price, cost_price, validity
                $existing->update([
                    'rate_limit' => $rateLimit,
                    'shared_users' => $sharedUsers,
                ]);
            } else {
                HotspotProfile::create([
                    'name' => $name,
                    'rate_limit' => $rateLimit,
                    'shared_users' => $sharedUsers,
                    'selling_price' => 0,
                    'cost_price' => 0,
                    'validity' => 'Unlimited',
                    'expired_mode' => 'Remove',
                ]);
            }
            $syncedCount++;
        }

        // Prune profiles that were deleted in MikroTik / Winbox
        if (!empty($remoteProfileNames)) {
            HotspotProfile::whereNotIn('name', $remoteProfileNames)->delete();
        }

        $allProfiles = HotspotProfile::all();
        return response()->json([
            'success' => true,
            'synced_count' => $syncedCount,
            'profiles' => $allProfiles,
            'message' => "{$syncedCount} profile berhasil disinkronkan dari MikroTik"
        ]);
    }
}
