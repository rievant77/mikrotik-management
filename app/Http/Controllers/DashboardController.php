<?php

namespace App\Http\Controllers;

use App\Models\DailyUserUsageSummary;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use App\Models\RouterSetting;
use App\Services\RouterOsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(RouterOsService $routerOs): View
    {
        $rawSessions = $routerOs->getActiveHotspotSessions();
        $onlineUsersCount = count($rawSessions);
        $activeSessionsCount = count($rawSessions);
        $totalHotspotUsers = HotspotUser::count();

        $today = Carbon::today()->toDateString();
        $todayUsage = DailyUserUsageSummary::where('usage_date', $today)->sum('total_bytes');

        $routerSetting = RouterSetting::where('is_active', true)->first();

        // Top bandwidth consumers from live sessions
        $topConsumers = collect($rawSessions)->sortByDesc(fn($s) => (int)($s['bytes-out'] ?? 0))->take(5)->map(function ($s) {
            $bytesIn = (int) ($s['bytes-in'] ?? 0);
            $bytesOut = (int) ($s['bytes-out'] ?? 0);
            return [
                'username' => $s['user'] ?? '-',
                'ip_address' => $s['address'] ?? '-',
                'mac_address' => $s['mac-address'] ?? '-',
                'current_rx_bps' => (int) ($s['rx-rate'] ?? 0),
                'current_tx_bps' => (int) ($s['tx-rate'] ?? 0),
                'download_speed' => \App\Support\FormatHelper::formatBytes((int)($s['rx-rate'] ?? 0) / 8) . '/s',
                'upload_speed' => \App\Support\FormatHelper::formatBytes((int)($s['tx-rate'] ?? 0) / 8) . '/s',
                'total_bytes' => \App\Support\FormatHelper::formatBytes($bytesIn + $bytesOut),
            ];
        });

        // Recent active sessions
        $recentSessions = collect($rawSessions)->take(5)->map(function ($s) {
            return [
                'username' => $s['user'] ?? '-',
                'ip_address' => $s['address'] ?? '-',
                'mac_address' => $s['mac-address'] ?? '-',
                'uptime' => $s['uptime'] ?? '0s',
                'current_rx_bps' => (int) ($s['rx-rate'] ?? 0),
                'current_tx_bps' => (int) ($s['tx-rate'] ?? 0),
            ];
        });

        return view('dashboard.index', compact(
            'onlineUsersCount',
            'activeSessionsCount',
            'totalHotspotUsers',
            'todayUsage',
            'routerSetting',
            'topConsumers',
            'recentSessions'
        ));
    }

    /**
     * API Endpoint for live traffic & metrics polling (Pure Direct Pass-Through)
     */
    public function liveData(RouterOsService $routerOs): JsonResponse
    {
        $rawSessions = $routerOs->getActiveHotspotSessions();
        $onlineUsersCount = count($rawSessions);
        $activeSessionsCount = count($rawSessions);

        $traffic = $routerOs->getInterfaceTraffic();
        $sessionRxSum = (int) collect($rawSessions)->sum(fn($s) => (int) ($s['rx-rate'] ?? 0));
        $sessionTxSum = (int) collect($rawSessions)->sum(fn($s) => (int) ($s['tx-rate'] ?? 0));

        $totalRxBps = max($traffic['rx_bps'], $sessionRxSum);
        $totalTxBps = max($traffic['tx_bps'], $sessionTxSum);

        $downloadMbps = round($totalRxBps / 1000000, 2);
        $uploadMbps = round($totalTxBps / 1000000, 2);

        // System resources from router
        $resource = $routerOs->getSystemResource();
        $cpuLoad = isset($resource['cpu-load']) ? (int) $resource['cpu-load'] : 0;
        $freeMemory = isset($resource['free-memory']) ? \App\Support\FormatHelper::formatBytes((int)$resource['free-memory']) : null;
        $uptime = $resource['uptime'] ?? null;

        // Top bandwidth consumers
        $topConsumers = collect($rawSessions)->sortByDesc(fn($s) => (int)($s['bytes-out'] ?? 0))->take(5)->map(function ($s, $idx) {
            $bytesIn = (int) ($s['bytes-in'] ?? 0);
            $bytesOut = (int) ($s['bytes-out'] ?? 0);
            return [
                'id' => $s['.id'] ?? ($s['user'] . '-' . $idx),
                'username' => $s['user'] ?? '-',
                'ip_address' => $s['address'] ?? '-',
                'mac_address' => $s['mac-address'] ?? '-',
                'current_rx_bps' => (int) ($s['rx-rate'] ?? 0),
                'current_tx_bps' => (int) ($s['tx-rate'] ?? 0),
                'download_speed' => \App\Support\FormatHelper::formatBytes(((int)($s['rx-rate'] ?? 0)) / 8) . '/s',
                'upload_speed' => \App\Support\FormatHelper::formatBytes(((int)($s['tx-rate'] ?? 0)) / 8) . '/s',
                'total_bytes' => \App\Support\FormatHelper::formatBytes($bytesIn + $bytesOut),
            ];
        })->values();

        // Recent active sessions
        $recentSessions = collect($rawSessions)->take(5)->map(function ($s, $idx) {
            return [
                'id' => $s['.id'] ?? ($s['user'] . '-' . $idx),
                'username' => $s['user'] ?? '-',
                'ip_address' => $s['address'] ?? '-',
                'mac_address' => $s['mac-address'] ?? '-',
                'uptime' => $s['uptime'] ?? '0s',
                'current_rx_bps' => (int) ($s['rx-rate'] ?? 0),
                'current_tx_bps' => (int) ($s['tx-rate'] ?? 0),
            ];
        })->values();

        $today = Carbon::today()->toDateString();
        $todayUsage = DailyUserUsageSummary::where('usage_date', $today)->sum('total_bytes');

        return response()->json([
            'online_users' => $onlineUsersCount,
            'active_devices' => $activeSessionsCount,
            'download_rate_mbps' => $downloadMbps,
            'upload_rate_mbps' => $uploadMbps,
            'download_rate_formatted' => $totalRxBps > 1000000 ? ($downloadMbps . ' Mbps') : (round($totalRxBps / 1000) . ' Kbps'),
            'upload_rate_formatted' => $totalTxBps > 1000000 ? ($uploadMbps . ' Mbps') : (round($totalTxBps / 1000) . ' Kbps'),
            'today_usage' => \App\Support\FormatHelper::formatBytes($todayUsage, 1),
            'cpu_load' => $cpuLoad,
            'free_memory' => $freeMemory,
            'uptime' => $uptime,
            'top_consumers' => $topConsumers,
            'recent_users' => $recentSessions,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }
}
