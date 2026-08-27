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
    public function index(Request $request): View
    {
        $profiles = HotspotProfile::all();
        $users = HotspotUser::with('profile')
            ->when($request->search, fn($q, $s) => $q->where('username', 'like', "%{$s}%"))
            ->when($request->profile_id, fn($q, $p) => $q->where('profile_id', $p))
            ->latest()
            ->paginate(50);

        return view('hotspot.users', compact('users', 'profiles'));
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

        $routerOs->addHotspotUser(
            $user->username,
            $user->password,
            $profile?->name,
            $user->uptime_limit,
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

    public function update(Request $request, HotspotUser $user): JsonResponse
    {
        $validated = $request->validate([
            'password' => 'nullable|string|max:255',
            'profile_id' => 'nullable|exists:hotspot_profiles,id',
            'uptime_limit' => 'nullable|string|max:50',
            'comment' => 'nullable|string|max:255',
        ]);

        $user->update(array_filter($validated));

        return response()->json(['success' => true, 'user' => $user]);
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

        foreach ($remoteUsers as $u) {
            $username = $u['name'] ?? null;
            if (!$username || $username === 'default-trial') {
                continue;
            }

            $profileName = $u['profile'] ?? null;
            $profile = $profileName ? HotspotProfile::where('name', $profileName)->first() : null;

            HotspotUser::updateOrCreate(
                ['username' => $username],
                [
                    'password' => $u['password'] ?? $username,
                    'profile_id' => $profile?->id,
                    'uptime_limit' => $u['limit-uptime'] ?? null,
                    'comment' => $u['comment'] ?? null,
                    'is_active' => true,
                ]
            );
            $syncedCount++;
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
