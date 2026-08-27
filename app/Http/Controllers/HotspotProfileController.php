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
    public function index(): View
    {
        $profiles = HotspotProfile::all();
        return view('hotspot.profiles', compact('profiles'));
    }

    public function store(Request $request, RouterOsService $routerOs): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:hotspot_profiles,name',
            'rate_limit' => 'nullable|string|max:100',
            'shared_users' => 'nullable|integer|min:1',
            'validity' => 'nullable|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'expired_mode' => 'nullable|string|max:50',
        ]);

        $profile = HotspotProfile::create($validated);

        // Sync to RouterOS
        $routerOs->addHotspotProfile(
            $profile->name,
            $profile->rate_limit,
            $profile->shared_users ?? 1
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
            'shared_users' => 'nullable|integer|min:1',
            'validity' => 'nullable|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'expired_mode' => 'nullable|string|max:50',
        ]);

        $old = $profile->toArray();
        $profile->update($validated);

        // Sync to RouterOS
        $routerOs->addHotspotProfile(
            $profile->name,
            $profile->rate_limit,
            $profile->shared_users ?? 1
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

        foreach ($remoteProfiles as $p) {
            $name = $p['name'] ?? null;
            if (!$name || $name === 'default') {
                continue;
            }

            $rateLimit = $p['rate-limit'] ?? null;
            $sharedUsers = isset($p['shared-users']) ? (int)$p['shared-users'] : 1;

            HotspotProfile::updateOrCreate(
                ['name' => $name],
                [
                    'rate_limit' => $rateLimit,
                    'shared_users' => $sharedUsers,
                    'selling_price' => 5000,
                    'cost_price' => 2500,
                    'validity' => '3 Jam',
                    'expired_mode' => 'Remove',
                ]
            );
            $syncedCount++;
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
