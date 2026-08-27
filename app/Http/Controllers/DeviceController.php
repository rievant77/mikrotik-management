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

        // 2. Fetch live active hotspot sessions to match active username and rates
        $activeSessions = $routerOs->getActiveHotspotSessions();
        $activeUserMap = [];
        $activeRateMap = [];

        foreach ($activeSessions as $s) {
            $mac = !empty($s['mac-address']) ? strtoupper($s['mac-address']) : null;
            $ip = $s['address'] ?? null;
            $user = $s['user'] ?? '-';
            $rx = (int) ($s['rx-rate'] ?? 0);
            $tx = (int) ($s['tx-rate'] ?? 0);

            if ($mac) {
                $activeUserMap[$mac] = $user;
                $activeRateMap[$mac] = ['rx' => $rx, 'tx' => $tx];
            }
            if ($ip) {
                $activeUserMap[$ip] = $user;
                $activeRateMap[$ip] = ['rx' => $rx, 'tx' => $tx];
            }
        }

        // 3. Map leases to clean device view models
        $devices = collect($leases)->map(function ($l, $idx) use ($activeUserMap, $activeRateMap) {
            $mac = $l['mac-address'] ?? ($l['active-mac-address'] ?? '-');
            $macUpper = strtoupper($mac);
            $ip = $l['address'] ?? ($l['active-address'] ?? '-');
            $hostname = $l['host-name'] ?? ($l['comment'] ?? null);

            $deviceName = DeviceHelper::resolveDeviceName($hostname, $mac);
            $deviceType = DeviceHelper::getDeviceType($hostname ?: $deviceName);

            $matchedUser = $activeUserMap[$macUpper] ?? ($activeUserMap[$ip] ?? ($l['comment'] ?? '-'));
            $rates = $activeRateMap[$macUpper] ?? ($activeRateMap[$ip] ?? ['rx' => 0, 'tx' => 0]);

            return [
                'id' => $l['.id'] ?? ($mac . '-' . $idx),
                'mac_address' => $mac,
                'ip_address' => $ip,
                'hostname' => $hostname,
                'device_name' => $deviceName,
                'device_display_name' => $hostname ?: $deviceName,
                'device_type' => $deviceType,
                'username' => $matchedUser,
                'status' => $l['status'] ?? 'bound',
                'interface' => $l['server'] ?? 'hotspot',
                'current_rx_bps' => $rates['rx'],
                'current_tx_bps' => $rates['tx'],
            ];
        });

        return view('devices.index', compact('devices'));
    }

    /**
     * Dedicated Real-time Live Devices JSON API endpoint
     */
    public function liveData(RouterOsService $routerOs): \Illuminate\Http\JsonResponse
    {
        $leases = $routerOs->getDhcpLeases();
        $activeSessions = $routerOs->getActiveHotspotSessions();
        $activeUserMap = [];
        $activeRateMap = [];

        foreach ($activeSessions as $s) {
            $mac = !empty($s['mac-address']) ? strtoupper($s['mac-address']) : null;
            $ip = $s['address'] ?? null;
            $user = $s['user'] ?? '-';
            $rx = (int) ($s['rx-rate'] ?? 0);
            $tx = (int) ($s['tx-rate'] ?? 0);

            if ($mac) {
                $activeUserMap[$mac] = $user;
                $activeRateMap[$mac] = ['rx' => $rx, 'tx' => $tx];
            }
            if ($ip) {
                $activeUserMap[$ip] = $user;
                $activeRateMap[$ip] = ['rx' => $rx, 'tx' => $tx];
            }
        }

        $devices = collect($leases)->map(function ($l, $idx) use ($activeUserMap, $activeRateMap) {
            $mac = $l['mac-address'] ?? ($l['active-mac-address'] ?? '-');
            $macUpper = strtoupper($mac);
            $ip = $l['address'] ?? ($l['active-address'] ?? '-');
            $hostname = $l['host-name'] ?? ($l['comment'] ?? null);

            $deviceName = DeviceHelper::resolveDeviceName($hostname, $mac);
            $deviceType = DeviceHelper::getDeviceType($hostname ?: $deviceName);

            $matchedUser = $activeUserMap[$macUpper] ?? ($activeUserMap[$ip] ?? ($l['comment'] ?? '-'));
            $rates = $activeRateMap[$macUpper] ?? ($activeRateMap[$ip] ?? ['rx' => 0, 'tx' => 0]);

            return [
                'id' => $l['.id'] ?? ($mac . '-' . $idx),
                'mac_address' => $mac,
                'ip_address' => $ip,
                'hostname' => $hostname,
                'device_name' => $deviceName,
                'device_display_name' => $hostname ?: $deviceName,
                'device_type' => $deviceType,
                'username' => $matchedUser,
                'status' => $l['status'] ?? 'bound',
                'interface' => $l['server'] ?? 'hotspot',
                'current_rx_bps' => $rates['rx'],
                'current_tx_bps' => $rates['tx'],
            ];
        });

        return response()->json([
            'devices' => $devices->values(),
            'count' => $devices->count(),
            'timestamp' => now()->format('H:i:s'),
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }
}
