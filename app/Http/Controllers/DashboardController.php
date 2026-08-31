<?php

namespace App\Http\Controllers;

use App\Models\DailyUserUsageSummary;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use App\Models\RouterSetting;
use App\Models\VoucherSale;
use App\Services\CollectorService;
use App\Services\RouterOsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(RouterOsService $routerOs): View
    {
        try {
            (new CollectorService($routerOs))->collect();
        } catch (\Throwable $e) {
            // Non-blocking
        }

        $rawSessions = $routerOs->getActiveHotspotSessions();
        $onlineUsersCount = count($rawSessions);
        $activeSessionsCount = count($rawSessions);
        $totalHotspotUsers = HotspotUser::count();

        $todayUsage = $this->calculateTodayUsage($rawSessions);

        $todaySalesQuery = VoucherSale::where('status', 'completed')->whereDate('activated_at', Carbon::today());
        $todayRevenue = (float) $todaySalesQuery->sum('selling_price');
        $todayVouchersSold = $todaySalesQuery->count();

        $routerSetting = RouterSetting::where('is_active', true)->first();
        $resource = $routerOs->getSystemResource();
        $routerInfo = $this->formatRouterInfo($routerSetting, $resource);

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
            'todayRevenue',
            'todayVouchersSold',
            'routerSetting',
            'routerInfo',
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

        $routerSetting = RouterSetting::where('is_active', true)->first();
        $resource = $routerOs->getSystemResource();
        $routerInfo = $this->formatRouterInfo($routerSetting, $resource);

        $cpuLoad = $routerInfo['cpu_load'];
        $freeMemory = $routerInfo['free_memory'];
        $uptime = $routerInfo['uptime'];

        $todaySalesQuery = VoucherSale::where('status', 'completed')->whereDate('activated_at', Carbon::today());
        $todayRevenue = (float) $todaySalesQuery->sum('selling_price');
        $todayVouchersSold = $todaySalesQuery->count();

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

        $todayUsage = $this->calculateTodayUsage($rawSessions);

        return response()->json([
            'online_users' => $onlineUsersCount,
            'active_devices' => $activeSessionsCount,
            'download_rate_mbps' => $downloadMbps,
            'upload_rate_mbps' => $uploadMbps,
            'download_rate_formatted' => $totalRxBps > 1000000 ? ($downloadMbps . ' Mbps') : (round($totalRxBps / 1000) . ' Kbps'),
            'upload_rate_formatted' => $totalTxBps > 1000000 ? ($uploadMbps . ' Mbps') : (round($totalTxBps / 1000) . ' Kbps'),
            'today_usage' => \App\Support\FormatHelper::formatBytes($todayUsage, 1),
            'today_revenue' => $todayRevenue,
            'today_revenue_formatted' => \App\Support\FormatHelper::formatRupiah($todayRevenue),
            'today_vouchers_sold' => $todayVouchersSold,
            'cpu_load' => $cpuLoad,
            'free_memory' => $freeMemory,
            'uptime' => $uptime,
            'router_info' => $routerInfo,
            'top_consumers' => $topConsumers,
            'recent_users' => $recentSessions,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Format router resource and identity data for dashboard health monitoring.
     */
    protected function formatRouterInfo(?RouterSetting $routerSetting, array $resource): array
    {
        $isOnline = !empty($resource);
        $totalMemory = isset($resource['total-memory']) ? (int) $resource['total-memory'] : 0;
        $freeMemory = isset($resource['free-memory']) ? (int) $resource['free-memory'] : 0;
        $usedMemory = max(0, $totalMemory - $freeMemory);
        $memPercent = $totalMemory > 0 ? round(($usedMemory / $totalMemory) * 100, 1) : 0;

        $totalHdd = isset($resource['total-hdd-space']) ? (int) $resource['total-hdd-space'] : 0;
        $freeHdd = isset($resource['free-hdd-space']) ? (int) $resource['free-hdd-space'] : 0;
        $usedHdd = max(0, $totalHdd - $freeHdd);
        $hddPercent = $totalHdd > 0 ? round(($usedHdd / $totalHdd) * 100, 1) : 0;

        $uptimeStr = $resource['uptime'] ?? null;
        $uptimeSeconds = $uptimeStr ? \App\Support\FormatHelper::parseUptime($uptimeStr) : 0;
        $uptimeFormatted = $uptimeStr ? \App\Support\FormatHelper::formatUptime($uptimeSeconds) : '-';

        return [
            'is_online' => $isOnline,
            'name' => $routerSetting->name ?? 'MikroTik Router',
            'board_name' => $resource['board-name'] ?? ($routerSetting->name ?? 'RouterBoard'),
            'version' => $resource['version'] ?? ($isOnline ? 'RouterOS' : '-'),
            'architecture' => $resource['architecture-name'] ?? null,
            'cpu_model' => $resource['cpu'] ?? null,
            'cpu_count' => isset($resource['cpu-count']) ? (int)$resource['cpu-count'] : 1,
            'cpu_freq' => isset($resource['cpu-frequency']) ? ($resource['cpu-frequency'] . ' MHz') : null,
            'cpu_load' => isset($resource['cpu-load']) ? (int) $resource['cpu-load'] : 0,
            'free_memory' => $freeMemory > 0 ? \App\Support\FormatHelper::formatBytes($freeMemory) : '0 B',
            'total_memory' => $totalMemory > 0 ? \App\Support\FormatHelper::formatBytes($totalMemory) : '0 B',
            'used_memory' => $usedMemory > 0 ? \App\Support\FormatHelper::formatBytes($usedMemory) : '0 B',
            'memory_percent' => $memPercent,
            'free_hdd' => $freeHdd > 0 ? \App\Support\FormatHelper::formatBytes($freeHdd) : '0 B',
            'total_hdd' => $totalHdd > 0 ? \App\Support\FormatHelper::formatBytes($totalHdd) : '0 B',
            'used_hdd' => $usedHdd > 0 ? \App\Support\FormatHelper::formatBytes($usedHdd) : '0 B',
            'hdd_percent' => $hddPercent,
            'uptime' => $uptimeStr ?? '-',
            'uptime_formatted' => $uptimeFormatted,
            'host' => $routerSetting->host ?? 'Belum diatur',
            'api_port' => $routerSetting->api_port ?? 8728,
            'use_ssl' => (bool) ($routerSetting->use_ssl ?? false),
            'last_poll' => $routerSetting?->last_successful_poll_at?->diffForHumans() ?? 'Belum pernah',
        ];
    }

    /**
     * Calculate true today usage comparing DB summaries and currently active sessions per user.
     */
    protected function calculateTodayUsage(array $rawSessions): int
    {
        $today = Carbon::today()->toDateString();

        $dbSummaries = DailyUserUsageSummary::where('usage_date', $today)
            ->selectRaw('username, sum(total_bytes_in) as total_in, sum(total_bytes_out) as total_out')
            ->groupBy('username')
            ->get()
            ->keyBy('username');

        $onlineUserMap = collect($rawSessions)->keyBy('user');
        $activeUsernames = $dbSummaries->keys()->merge($onlineUserMap->keys())->unique()->filter();

        return (int) $activeUsernames->sum(function ($username) use ($dbSummaries, $onlineUserMap) {
            $db = $dbSummaries->get($username);
            $live = $onlineUserMap->get($username);

            $totalIn = $db ? (int) $db->total_in : 0;
            $totalOut = $db ? (int) $db->total_out : 0;

            if ($live) {
                $liveIn = (int) ($live['bytes-in'] ?? 0);
                $liveOut = (int) ($live['bytes-out'] ?? 0);
                $totalIn = max($totalIn, $liveIn);
                $totalOut = max($totalOut, $liveOut);
            }

            return $totalIn + $totalOut;
        });
    }
}
