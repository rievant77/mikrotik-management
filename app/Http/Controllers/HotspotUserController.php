<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\HotspotProfile;
use App\Models\HotspotUser;
use App\Services\RouterOsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotspotUserController extends Controller
{
    public function index(Request $request, RouterOsService $routerOs): View
    {
        // Auto-reconcile with MikroTik router so any deletions / disables made in Winbox reflect immediately
        if (!app()->environment('testing')) {
            try {
                $remoteUsers = $routerOs->getHotspotUsers();
                if (!empty($remoteUsers)) {
                    $remoteUsernames = [];
                    foreach ($remoteUsers as $u) {
                        $uname = $u['name'] ?? null;
                        if (!$uname || $uname === 'default-trial') continue;
                        $remoteUsernames[] = $uname;

                        $isDisabled = isset($u['disabled']) && in_array(strtolower((string)$u['disabled']), ['true', 'yes', '1'], true);
                        HotspotUser::where('username', $uname)->update(['is_active' => !$isDisabled]);
                    }

                    if (!empty($remoteUsernames)) {
                        HotspotUser::whereNotIn('username', $remoteUsernames)->delete();
                    }
                }

                // Sweep and process expired users according to their profile expired_mode
                app(\App\Services\VoucherExpiryService::class)->sweepExpiredUsers($routerOs);
            } catch (\Throwable $e) {
                // Non-blocking if router connection fails
            }
        }

        $profiles = HotspotProfile::all();
        $status = $request->get('status');
        if (empty($status)) {
            $status = 'active'; // Default: hanya user aktif
        }

        $query = HotspotUser::with('profile')
            ->when($request->search, fn($q, $s) => $q->where('username', 'like', "%{$s}%"))
            ->when($request->profile_id, fn($q, $p) => $q->where('profile_id', $p));

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive' || $status === 'disabled') {
            $query->where('is_active', false);
        } // if $status === 'all', don't filter is_active

        $users = $query->latest('id')->paginate(50)->withQueryString();

        return view('hotspot.users', compact('users', 'profiles', 'status'));
    }

    public function store(Request $request, RouterOsService $routerOs): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:100|unique:hotspot_users,username',
            'password' => 'required|string|max:255',
            'profile_id' => 'nullable|exists:hotspot_profiles,id',
            'uptime_limit' => 'nullable|string|max:50',
            'comment' => 'nullable|string|max:255',
        ]);

        $user = HotspotUser::create($validated);
        $profile = $user->profile;
        $uptimeLimit = \App\Support\FormatHelper::parseValidityToRouterTime($user->uptime_limit);

        $routerOs->addHotspotUser(
            $user->username,
            $user->password,
            $profile?->name,
            $uptimeLimit,
            $user->comment
        );

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'hotspot_user_created',
            'entity_type' => 'HotspotUser',
            'entity_id' => $user->id,
            'new_values' => ['username' => $user->username],
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function update(Request $request, HotspotUser $user, RouterOsService $routerOs): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'nullable|string|max:255',
            'profile_id' => 'nullable|exists:hotspot_profiles,id',
            'uptime_limit' => 'nullable|string|max:50',
            'comment' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $user->update($validated);
        $profile = $user->profile;
        $uptimeLimit = \App\Support\FormatHelper::parseValidityToRouterTime($user->uptime_limit);

        // Update on MikroTik RouterOS
        $routerOs->updateHotspotUser($user->username, [
            'password' => $user->password,
            'profile' => $profile?->name,
            'uptime_limit' => $uptimeLimit,
            'comment' => $user->comment,
            'is_active' => $user->is_active,
        ]);

        return response()->json(['success' => true, 'user' => $user->load('profile')]);
    }

    public function toggleStatus(HotspotUser $user, RouterOsService $routerOs): JsonResponse
    {
        $user->is_active = !$user->is_active;
        $user->save();

        $routerOs->setHotspotUserStatus($user->username, $user->is_active);

        return response()->json([
            'success' => true,
            'is_active' => $user->is_active,
            'message' => "Status user {$user->username} berhasil diubah menjadi " . ($user->is_active ? 'Aktif' : 'Non-aktif')
        ]);
    }

    public function destroy(HotspotUser $user, Request $request, RouterOsService $routerOs): JsonResponse
    {
        $username = $user->username;
        $routerOs->removeHotspotUser($username);
        $user->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'hotspot_user_deleted',
            'entity_type' => 'HotspotUser',
            'old_values' => ['username' => $username],
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => "User {$username} berhasil dihapus"]);
    }

    public function syncFromRouter(RouterOsService $routerOs): JsonResponse
    {
        $remoteUsers = $routerOs->getHotspotUsers();
        $syncedCount = 0;
        $remoteUsernames = [];

        foreach ($remoteUsers as $u) {
            $username = $u['name'] ?? null;
            if (!$username || $username === 'default-trial') {
                continue;
            }

            $remoteUsernames[] = $username;
            $profileName = $u['profile'] ?? null;
            $profile = $profileName ? HotspotProfile::where('name', $profileName)->first() : null;
            $isDisabled = isset($u['disabled']) && in_array(strtolower((string)$u['disabled']), ['true', 'yes', '1'], true);

            HotspotUser::updateOrCreate(
                ['username' => $username],
                [
                    'password' => $u['password'] ?? $username,
                    'profile_id' => $profile?->id,
                    'uptime_limit' => $u['limit-uptime'] ?? null,
                    'comment' => $u['comment'] ?? null,
                    'is_active' => !$isDisabled,
                ]
            );
            $syncedCount++;
        }

        // Prune users that were deleted in MikroTik / Winbox
        if (!empty($remoteUsernames)) {
            HotspotUser::whereNotIn('username', $remoteUsernames)->delete();
        }

        $allUsers = HotspotUser::with('profile')->latest()->get();
        return response()->json([
            'success' => true,
            'synced_count' => $syncedCount,
            'users' => $allUsers,
            'message' => "{$syncedCount} user berhasil disinkronkan dari MikroTik"
        ]);
    }
}
