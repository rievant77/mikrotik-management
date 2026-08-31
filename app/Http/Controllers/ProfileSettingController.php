<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\RouterSetting;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileSettingController extends Controller
{
    /**
     * Display the profile and app branding settings page.
     */
    public function index(): View
    {
        $user = Auth::user();
        $setting = RouterSetting::first();

        return view('settings.profile', compact('user', 'setting'));
    }

    /**
     * Update administrator profile details (Name, Email, Avatar).
     */
    public function updateProfile(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ]);

        $avatarPath = $user->avatar;

        if ($request->boolean('remove_avatar') && $avatarPath) {
            $fullPath = public_path($avatarPath);
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
            $avatarPath = null;
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $uploadDir = public_path('uploads/avatars');
            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            // Remove previous avatar if exists
            if ($avatarPath && File::exists(public_path($avatarPath))) {
                File::delete(public_path($avatarPath));
            }

            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $avatarPath = 'uploads/avatars/' . $filename;
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'avatar' => $avatarPath,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'update_admin_profile',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'new_values' => ['name' => $user->name, 'email' => $user->email, 'avatar' => $avatarPath],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui!',
                'user' => $user,
            ]);
        }

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Update administrator account password.
     */
    public function updatePassword(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password saat ini (lama) tidak sesuai.',
                ], 422);
            }
            return back()->withErrors(['current_password' => 'Password saat ini (lama) tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'update_admin_password',
            'entity_type' => 'User',
            'entity_id' => $user->id,
            'new_values' => ['status' => 'password_changed'],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah!',
            ]);
        }

        return back()->with('success', 'Password berhasil diubah!');
    }

    /**
     * Update application identity, branding, logo, and favicon.
     */
    public function updateBranding(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'app_favicon' => 'nullable|file|mimes:ico,png,svg|max:1024',
            'remove_logo' => 'nullable|boolean',
            'remove_favicon' => 'nullable|boolean',
        ]);

        $setting = RouterSetting::first();
        if (!$setting) {
            $setting = RouterSetting::create([
                'name' => 'Default Router',
                'host' => '192.168.88.1',
                'api_port' => 8728,
                'username' => 'admin',
                'password' => '',
            ]);
        }

        $logoPath = $setting->app_logo;
        $faviconPath = $setting->app_favicon;

        $uploadDir = public_path('uploads/branding');
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        // Handle Remove Logo
        if ($request->boolean('remove_logo') && $logoPath) {
            if (File::exists(public_path($logoPath))) {
                File::delete(public_path($logoPath));
            }
            $logoPath = null;
        }

        // Handle Upload Logo
        if ($request->hasFile('app_logo')) {
            $file = $request->file('app_logo');
            if ($logoPath && File::exists(public_path($logoPath))) {
                File::delete(public_path($logoPath));
            }
            $filename = 'app_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $logoPath = 'uploads/branding/' . $filename;
        }

        // Handle Remove Favicon
        if ($request->boolean('remove_favicon') && $faviconPath) {
            if (File::exists(public_path($faviconPath))) {
                File::delete(public_path($faviconPath));
            }
            $faviconPath = null;
        }

        // Handle Upload Favicon
        if ($request->hasFile('app_favicon')) {
            $file = $request->file('app_favicon');
            if ($faviconPath && File::exists(public_path($faviconPath))) {
                File::delete(public_path($faviconPath));
            }
            $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $faviconPath = 'uploads/branding/' . $filename;
        }

        $setting->update([
            'app_name' => $validated['app_name'],
            'tagline' => $validated['tagline'] ?? 'Hotspot & Bandwidth Management',
            'contact_phone' => $validated['contact_phone'] ?? null,
            'app_logo' => $logoPath,
            'app_favicon' => $faviconPath,
        ]);

        AuditLog::create([
            'user_id' => Auth::id() ?? null,
            'action' => 'update_app_branding',
            'entity_type' => 'RouterSetting',
            'entity_id' => $setting->id,
            'new_values' => [
                'app_name' => $setting->app_name,
                'tagline' => $setting->tagline,
                'app_logo' => $logoPath,
                'app_favicon' => $faviconPath,
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Identitas & branding aplikasi berhasil diperbarui!',
                'setting' => $setting,
            ]);
        }

        return back()->with('success', 'Identitas & branding aplikasi berhasil diperbarui!');
    }
}
