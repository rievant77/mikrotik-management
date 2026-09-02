<?php

namespace App\Http\Controllers;

use App\Services\RouterOsService;
use App\Support\DeviceHelper;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(Request $request, RouterOsService $routerOs): View
    {
        // 1. Fetch live DHCP leases directly from MikroTik
        $leases = $routerOs->getDhcpLeases();

        // 2. Fetch live active hotspot sessions to match active username
        $activeSessions = $routerOs->getActiveHotspotSessions();
        $activeUserMap = [];

        foreach ($activeSessions as $s) {
            $mac = !empty($s['mac-address']) ? strtoupper($s['mac-address']) : null;
            $ip = $s['address'] ?? null;
            $user = $s['user'] ?? null;

            if ($mac && $user) {
                $activeUserMap[$mac] = $user;
            }
            if ($ip && $user) {
                $activeUserMap[$ip] = $user;
            }
        }

        // 3. Map leases to clean device view models (without Rx/Tx clutter)
        $devices = collect($leases)->map(function ($l, $idx) use ($activeUserMap) {
            $mac = $l['mac-address'] ?? ($l['active-mac-address'] ?? '-');
            $macUpper = strtoupper($mac);
            $ip = $l['address'] ?? ($l['active-address'] ?? '-');
            $hostname = $l['host-name'] ?? ($l['comment'] ?? null);

            $vendor = DeviceHelper::getVendorByMac($mac);
            $deviceName = DeviceHelper::resolveDeviceName($hostname, $mac);
            $deviceType = DeviceHelper::getDeviceType($hostname ?: $deviceName);

            $matchedUser = $activeUserMap[$macUpper] ?? ($activeUserMap[$ip] ?? null);
            $isHotspotActive = !empty($matchedUser);
            $leaseStatus = strtolower($l['status'] ?? 'bound');

            if ($isHotspotActive) {
                $status = 'online';
            } elseif ($leaseStatus === 'bound') {
                $status = 'standby';
            } else {
                $status = 'offline';
            }

            return [
                'id' => $l['.id'] ?? ($mac . '-' . $idx),
                'mac_address' => $mac,
                'ip_address' => $ip,
                'hostname' => $hostname,
                'vendor' => $vendor ?: 'Unknown Vendor',
                'device_name' => $deviceName,
                'device_display_name' => $hostname ?: $deviceName,
                'device_type' => $deviceType,
                'username' => $matchedUser,
                'is_hotspot_active' => $isHotspotActive,
                'status' => $status,
                'interface' => $l['server'] ?? 'hotspot',
                'expires_after' => $l['expires-after'] ?? null,
                'last_seen' => $l['last-seen'] ?? null,
                'dynamic' => $l['dynamic'] ?? true,
            ];
        });

        $stats = [
            'total' => $devices->count(),
            'online' => $devices->where('status', 'online')->count(),
            'standby' => $devices->where('status', 'standby')->count(),
            'offline' => $devices->where('status', 'offline')->count(),
        ];

        return view('devices.index', compact('devices', 'stats'));
    }

    /**
     * Dedicated Real-time Live Devices JSON API endpoint
     */
    public function liveData(RouterOsService $routerOs): \Illuminate\Http\JsonResponse
    {
        $leases = $routerOs->getDhcpLeases();
        $activeSessions = $routerOs->getActiveHotspotSessions();
        $activeUserMap = [];

        foreach ($activeSessions as $s) {
            $mac = !empty($s['mac-address']) ? strtoupper($s['mac-address']) : null;
            $ip = $s['address'] ?? null;
            $user = $s['user'] ?? null;

            if ($mac && $user) {
                $activeUserMap[$mac] = $user;
            }
            if ($ip && $user) {
                $activeUserMap[$ip] = $user;
            }
        }

        $devices = collect($leases)->map(function ($l, $idx) use ($activeUserMap) {
            $mac = $l['mac-address'] ?? ($l['active-mac-address'] ?? '-');
            $macUpper = strtoupper($mac);
            $ip = $l['address'] ?? ($l['active-address'] ?? '-');
            $hostname = $l['host-name'] ?? ($l['comment'] ?? null);

            $vendor = DeviceHelper::getVendorByMac($mac);
            $deviceName = DeviceHelper::resolveDeviceName($hostname, $mac);
            $deviceType = DeviceHelper::getDeviceType($hostname ?: $deviceName);

            $matchedUser = $activeUserMap[$macUpper] ?? ($activeUserMap[$ip] ?? null);
            $isHotspotActive = !empty($matchedUser);
            $leaseStatus = strtolower($l['status'] ?? 'bound');

            if ($isHotspotActive) {
                $status = 'online';
            } elseif ($leaseStatus === 'bound') {
                $status = 'standby';
            } else {
                $status = 'offline';
            }

            return [
                'id' => $l['.id'] ?? ($mac . '-' . $idx),
                'mac_address' => $mac,
                'ip_address' => $ip,
                'hostname' => $hostname,
                'vendor' => $vendor ?: 'Unknown Vendor',
                'device_name' => $deviceName,
                'device_display_name' => $hostname ?: $deviceName,
                'device_type' => $deviceType,
                'username' => $matchedUser,
                'is_hotspot_active' => $isHotspotActive,
                'status' => $status,
                'interface' => $l['server'] ?? 'hotspot',
                'expires_after' => $l['expires-after'] ?? null,
                'last_seen' => $l['last-seen'] ?? null,
                'dynamic' => $l['dynamic'] ?? true,
            ];
        });

        $stats = [
            'total' => $devices->count(),
            'online' => $devices->where('status', 'online')->count(),
            'standby' => $devices->where('status', 'standby')->count(),
            'offline' => $devices->where('status', 'offline')->count(),
        ];

        return response()->json([
            'devices' => $devices->values(),
            'stats' => $stats,
            'count' => $devices->count(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Fetch comprehensive detail of a device (MAC address) including multi-user history and quota.
     */
    public function detail(Request $request, string $mac, RouterOsService $routerOs): \Illuminate\Http\JsonResponse
    {
        $cleanMac = strtoupper(trim($mac));

        // 1. Get historical sessions & aggregated usage from database
        $sessions = \App\Models\HotspotSession::where('mac_address', $cleanMac)
            ->orWhere('mac_address', strtolower($cleanMac))
            ->orderBy('started_at', 'desc')
            ->limit(50)
            ->get();

        $totalBytesIn = $sessions->sum('total_bytes_in') ?: $sessions->sum('last_bytes_in');
        $totalBytesOut = $sessions->sum('total_bytes_out') ?: $sessions->sum('last_bytes_out');
        $totalBytes = $totalBytesIn + $totalBytesOut;

        $uniqueUsers = $sessions->pluck('username')->filter()->unique()->values();

        $sessionHistory = $sessions->map(function ($s) {
            $in = (int) ($s->total_bytes_in ?: $s->last_bytes_in);
            $out = (int) ($s->total_bytes_out ?: $s->last_bytes_out);
            $total = $in + $out;

            $duration = '-';
            if ($s->started_at && $s->ended_at) {
                $diff = $s->started_at->diff($s->ended_at);
                $duration = ($diff->h > 0 ? $diff->h . 'j ' : '') . $diff->i . 'm ' . $diff->s . 'd';
            } elseif ($s->started_at) {
                $diff = $s->started_at->diff(now());
                $duration = ($diff->h > 0 ? $diff->h . 'j ' : '') . $diff->i . 'm (aktif)';
            }

            return [
                'id' => $s->id,
                'username' => $s->username,
                'ip_address' => $s->ip_address,
                'started_at' => $s->started_at ? $s->started_at->format('d M Y, H:i') : '-',
                'ended_at' => $s->ended_at ? $s->ended_at->format('d M Y, H:i') : ($s->status === 'active' ? 'Aktif' : '-'),
                'duration' => $duration,
                'bytes_in_formatted' => \App\Support\FormatHelper::formatBytes($in),
                'bytes_out_formatted' => \App\Support\FormatHelper::formatBytes($out),
                'total_bytes_formatted' => \App\Support\FormatHelper::formatBytes($total),
                'status' => $s->status,
            ];
        });

        // 2. Query Live DHCP Lease, Active Hotspot Session, & IP Binding from MikroTik
        $ipBinding = $routerOs->getHotspotIpBinding($cleanMac);
        $leases = $routerOs->getDhcpLeases();
        $matchedLease = collect($leases)->first(function ($l) use ($cleanMac) {
            $lMac = strtoupper($l['mac-address'] ?? ($l['active-mac-address'] ?? ''));
            return $lMac === $cleanMac;
        });

        $activeSessions = $routerOs->getActiveHotspotSessions();
        $matchedActive = collect($activeSessions)->first(function ($s) use ($cleanMac) {
            $sMac = strtoupper($s['mac-address'] ?? '');
            return $sMac === $cleanMac;
        });

        return response()->json([
            'success' => true,
            'mac_address' => $cleanMac,
            'vendor' => DeviceHelper::getVendorByMac($cleanMac),
            'hostname' => $matchedLease['host-name'] ?? ($matchedLease['comment'] ?? null),
            'ip_address' => $matchedLease['address'] ?? ($matchedLease['active-address'] ?? ($matchedActive['address'] ?? null)),
            'is_dynamic' => ($matchedLease['dynamic'] ?? '') === 'true' || ($matchedLease['dynamic'] ?? false) === true,
            'comment' => $matchedLease['comment'] ?? null,
            'active_username' => $matchedActive['user'] ?? null,
            'active_uptime' => $matchedActive['uptime'] ?? null,
            'ip_binding' => $ipBinding ? [
                'type' => $ipBinding['type'] ?? 'regular',
                'comment' => $ipBinding['comment'] ?? null,
                'disabled' => ($ipBinding['disabled'] ?? '') === 'true',
            ] : null,
            'quota_stats' => [
                'total_bytes' => $totalBytes,
                'total_bytes_formatted' => \App\Support\FormatHelper::formatBytes($totalBytes),
                'download_formatted' => \App\Support\FormatHelper::formatBytes($totalBytesOut),
                'upload_formatted' => \App\Support\FormatHelper::formatBytes($totalBytesIn),
                'session_count' => $sessions->count(),
                'unique_users_count' => $uniqueUsers->count(),
                'unique_users' => $uniqueUsers,
            ],
            'session_history' => $sessionHistory,
        ]);
    }

    /**
     * Action: Make DHCP lease static.
     */
    public function actionMakeStatic(Request $request, RouterOsService $routerOs): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'mac_address' => 'required|string',
            'ip_address' => 'nullable|string',
            'comment' => 'nullable|string',
        ]);

        $mac = $request->input('mac_address');
        $ip = $request->input('ip_address');
        $comment = $request->input('comment');

        $success = $routerOs->makeDhcpLeaseStatic($mac, $ip, $comment);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Lease DHCP berhasil diubah menjadi Static.' : 'Gagal mengubah lease DHCP menjadi Static.',
        ]);
    }

    /**
     * Action: Set or Remove Hotspot IP Binding (bypassed, blocked, regular, remove).
     */
    public function actionSetIpBinding(Request $request, RouterOsService $routerOs): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'mac_address' => 'required|string',
            'type' => 'required|in:bypassed,blocked,regular,remove',
            'comment' => 'nullable|string',
        ]);

        $mac = $request->input('mac_address');
        $type = $request->input('type');
        $comment = $request->input('comment');

        if ($type === 'remove') {
            $success = $routerOs->removeHotspotIpBinding($mac);
            $message = $success ? 'IP Binding dihapus.' : 'Gagal menghapus IP Binding.';
        } else {
            $success = $routerOs->setHotspotIpBinding($mac, $type, $comment);
            $message = $success ? "IP Binding berhasil disetel sebagai {$type}." : 'Gagal mengatur IP Binding.';
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Action: Kick active hotspot session for device.
     */
    public function actionKick(Request $request, RouterOsService $routerOs): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'username' => 'nullable|string',
            'mac_address' => 'nullable|string',
        ]);

        $username = $request->input('username');
        $mac = $request->input('mac_address');

        if ($username) {
            $success = $routerOs->disconnectHotspotUser($username);
        } elseif ($mac) {
            $activeSessions = $routerOs->getActiveHotspotSessions();
            $matched = collect($activeSessions)->first(function ($s) use ($mac) {
                return strtoupper($s['mac-address'] ?? '') === strtoupper($mac);
            });
            if ($matched && !empty($matched['user'])) {
                $success = $routerOs->disconnectHotspotUser($matched['user']);
            } else {
                $success = false;
            }
        } else {
            $success = false;
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Sesi hotspot berhasil diputuskan.' : 'Gagal memutuskan sesi hotspot.',
        ]);
    }

    /**
     * Action: Set alias / comment on DHCP lease.
     */
    public function actionSetComment(Request $request, RouterOsService $routerOs): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'mac_address' => 'required|string',
            'comment' => 'required|string|max:100',
        ]);

        $mac = $request->input('mac_address');
        $comment = $request->input('comment');

        $success = $routerOs->setDhcpLeaseComment($mac, $comment);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Nama / Catatan perangkat berhasil disimpan.' : 'Gagal menyimpan catatan perangkat.',
        ]);
    }

    /**
     * Get live and historical web activity for a specific device.
     */
    public function deviceWebActivity(Request $request, string $mac, \App\Services\WebHistoryService $webHistoryService): \Illuminate\Http\JsonResponse
    {
        $ip = $request->input('ip');
        $username = $request->input('username');

        $data = $webHistoryService->getDeviceWebActivity($mac, $ip, $username);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
