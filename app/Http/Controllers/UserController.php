<?php

namespace App\Http\Controllers;

use App\Models\DailyUserUsageSummary;
use App\Models\HotspotProfile;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use App\Models\UsageSnapshot;
use App\Services\CollectorService;
use App\Services\RouterOsService;
use App\Support\DeviceHelper;
use App\Support\FormatHelper;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request, RouterOsService $routerOs): View
    {
        $profiles = HotspotProfile::all();

        // 1. Fetch live active sessions directly from MikroTik RouterOS API
        $rawSessions = $routerOs->getActiveHotspotSessions();
        $sessions = $this->mapActiveSessions($rawSessions, $routerOs);

        return view('users.index', compact('sessions', 'profiles'));
    }

    /**
     * Dedicated Real-time Online Users JSON API endpoint
     */
    public function liveData(RouterOsService $routerOs): JsonResponse
    {
        $rawSessions = $routerOs->getActiveHotspotSessions();
        $sessions = $this->mapActiveSessions($rawSessions, $routerOs);

        return response()->json([
            'sessions' => $sessions->values(),
            'count' => $sessions->count(),
            'timestamp' => now()->format('H:i:s'),
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }

    /**
     * Map raw RouterOS hotspot sessions with profiles and resolved device/host names.
     */
    protected function mapActiveSessions(array $rawSessions, RouterOsService $routerOs): \Illuminate\Support\Collection
    {
        $leaseMap = $this->getDhcpLeaseMap($routerOs);
        $hotspotUsers = HotspotUser::with('profile')->get()->keyBy('username');

        return collect($rawSessions)->map(function ($raw, $idx) use ($hotspotUsers, $leaseMap) {
            $username = $raw['user'] ?? '-';
            $userModel = $hotspotUsers->get($username);
            $bytesIn = (int) ($raw['bytes-in'] ?? 0);
            $bytesOut = (int) ($raw['bytes-out'] ?? 0);

            $mac = $raw['mac-address'] ?? '-';
            $macUpper = ($mac !== '-') ? strtoupper($mac) : null;
            $ip = $raw['address'] ?? '-';

            $hostname = $raw['host-name'] 
                ?? ($macUpper ? ($leaseMap[$macUpper] ?? null) : null) 
                ?? ($leaseMap[$ip] ?? null) 
                ?? ($raw['comment'] ?? null);

            $deviceName = DeviceHelper::resolveDeviceName($hostname, $mac);
            $deviceType = DeviceHelper::getDeviceType($hostname ?: $deviceName);

            return [
                'id' => $raw['.id'] ?? ($username . '-' . $idx),
                'username' => $username,
                'ip_address' => $ip,
                'mac_address' => $mac,
                'hostname' => $hostname,
                'device_name' => $deviceName,
                'device_display_name' => $hostname ?: $deviceName,
                'device_type' => $deviceType,
                'uptime' => $raw['uptime'] ?? '0s',
                'bytes_in' => $bytesIn,
                'bytes_out' => $bytesOut,
                'total_bytes_in' => $bytesIn,
                'total_bytes_out' => $bytesOut,
                'total_bytes' => $bytesIn + $bytesOut,
                'current_rx_bps' => (int) ($raw['rx-rate'] ?? 0),
                'current_tx_bps' => (int) ($raw['tx-rate'] ?? 0),
                'profile' => $userModel?->profile?->name ?? ($raw['profile'] ?? '-'),
                'interface' => 'hotspot',
                'status' => 'active',
            ];
        });
    }

    public function show(Request $request, string $username, RouterOsService $routerOs): View
    {
        $user = HotspotUser::with('profile')->where('username', $username)->first();
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $period = $request->get('period', '14days');
        $sessionSearch = $request->get('session_search', '');
        $sessionStatus = $request->get('session_status', 'all');

        $startDate = match ($period) {
            '7days' => $today->copy()->subDays(6),
            '14days' => $today->copy()->subDays(13),
            '30days' => $today->copy()->subDays(29),
            'this_month' => $today->copy()->startOfMonth(),
            'custom' => $request->filled('start_date') ? Carbon::parse($request->start_date) : $today->copy()->subDays(13),
            'all' => null,
            default => $today->copy()->subDays(13),
        };

        $endDate = match ($period) {
            'custom' => $request->filled('end_date') ? Carbon::parse($request->end_date) : $today->copy(),
            default => $today->copy(),
        };

        // 1. Fetch live active session from MikroTik RouterOS
        $liveSessions = [];
        try {
            $rawActive = $routerOs->getActiveHotspotSessions();
            $liveSessions = collect($rawActive)->filter(fn($s) => ($s['user'] ?? '') === $username)->values()->all();
        } catch (\Throwable $e) {
            $liveSessions = [];
        }

        $isOnline = !empty($liveSessions);

        // Resolve device name for live session if available
        $leaseMap = $this->getDhcpLeaseMap($routerOs);

        $liveSessions = collect($liveSessions)->map(function ($live, $idx) use ($username, $leaseMap) {
            $mac = $live['mac-address'] ?? '-';
            $macUpper = ($mac !== '-') ? strtoupper($mac) : null;
            $ip = $live['address'] ?? '-';

            $hostname = $live['host-name']
                ?? ($macUpper ? ($leaseMap[$macUpper] ?? null) : null)
                ?? ($leaseMap[$ip] ?? null)
                ?? ($live['comment'] ?? null);

            $deviceName = DeviceHelper::resolveDeviceName($hostname, $mac);
            $deviceType = DeviceHelper::getDeviceType($hostname ?: $deviceName);

            return array_merge($live, [
                'hostname' => $hostname,
                'device_name' => $deviceName,
                'device_display_name' => $hostname ?: $deviceName,
                'device_type' => $deviceType,
            ]);
        })->all();

        $primaryLiveSession = !empty($liveSessions) ? $liveSessions[0] : null;

        // 2. Fetch past sessions from database with filter
        $pastSessionsQuery = HotspotSession::where('username', $username);

        if ($startDate) {
            $pastSessionsQuery->where('started_at', '>=', $startDate->startOfDay());
        }
        if ($endDate) {
            $pastSessionsQuery->where('started_at', '<=', $endDate->endOfDay());
        }
        if ($sessionStatus === 'active') {
            $pastSessionsQuery->where('status', 'active');
        } elseif ($sessionStatus === 'ended') {
            $pastSessionsQuery->where('status', '!=', 'active');
        }
        if (!empty($sessionSearch)) {
            $pastSessionsQuery->where(function ($q) use ($sessionSearch) {
                $q->where('hostname', 'like', "%{$sessionSearch}%")
                  ->orWhere('device_name', 'like', "%{$sessionSearch}%")
                  ->orWhere('ip_address', 'like', "%{$sessionSearch}%")
                  ->orWhere('mac_address', 'like', "%{$sessionSearch}%");
            });
        }

        $pastSessions = $pastSessionsQuery->latest('started_at')->limit(30)->get();

        // 3. Fetch daily summaries for this user
        $allDailySummaries = DailyUserUsageSummary::where('username', $username)
            ->orderBy('usage_date', 'desc')
            ->get();

        $filteredDailySummaries = $allDailySummaries;
        if ($startDate) {
            $filteredDailySummaries = $filteredDailySummaries->filter(fn($d) => Carbon::parse($d->usage_date)->gte($startDate->startOfDay()));
        }
        if ($endDate) {
            $filteredDailySummaries = $filteredDailySummaries->filter(fn($d) => Carbon::parse($d->usage_date)->lte($endDate->endOfDay()));
        }

        // Today usage
        $todayDb = $allDailySummaries->firstWhere('usage_date', $today->toDateString());
        $todayBytesIn = $todayDb ? (int) $todayDb->total_bytes_in : 0;
        $todayBytesOut = $todayDb ? (int) $todayDb->total_bytes_out : 0;
        if ($isOnline && $primaryLiveSession) {
            $todayBytesIn = max($todayBytesIn, (int) ($primaryLiveSession['bytes-in'] ?? 0));
            $todayBytesOut = max($todayBytesOut, (int) ($primaryLiveSession['bytes-out'] ?? 0));
        }
        $todayUsage = $todayBytesIn + $todayBytesOut;

        // This Month usage
        $monthSummaries = $allDailySummaries->filter(fn($d) => Carbon::parse($d->usage_date)->gte($startOfMonth));
        $monthBytesIn = $monthSummaries->sum('total_bytes_in');
        $monthBytesOut = $monthSummaries->sum('total_bytes_out');
        if ($isOnline && $primaryLiveSession) {
            $monthBytesIn = max($monthBytesIn, (int) ($primaryLiveSession['bytes-in'] ?? 0));
            $monthBytesOut = max($monthBytesOut, (int) ($primaryLiveSession['bytes-out'] ?? 0));
        }
        $monthUsage = $monthBytesIn + $monthBytesOut;

        // All-Time usage
        $totalBytesIn = (int) $allDailySummaries->sum('total_bytes_in');
        $totalBytesOut = (int) $allDailySummaries->sum('total_bytes_out');
        $totalUptimeSeconds = (int) $allDailySummaries->sum('total_uptime_seconds');
        $totalSessionsCount = max((int) $allDailySummaries->sum('session_count'), $pastSessions->count());

        if ($isOnline && $primaryLiveSession) {
            $liveIn = (int) ($primaryLiveSession['bytes-in'] ?? 0);
            $liveOut = (int) ($primaryLiveSession['bytes-out'] ?? 0);
            $liveUptime = FormatHelper::parseUptime($primaryLiveSession['uptime'] ?? '0s');

            $totalBytesIn = max($totalBytesIn, $liveIn);
            $totalBytesOut = max($totalBytesOut, $liveOut);
            $totalUptimeSeconds = max($totalUptimeSeconds, $liveUptime);
            if ($totalSessionsCount === 0) $totalSessionsCount = 1;
        }

        // Also check if MikroTik router has all-time user counters (/ip/hotspot/user)
        try {
            $rUsers = $routerOs->getHotspotUsers();
            $rUser = collect($rUsers)->firstWhere('name', $username);
            if ($rUser) {
                $rIn = (int) ($rUser['bytes-in'] ?? 0);
                $rOut = (int) ($rUser['bytes-out'] ?? 0);
                $totalBytesIn = max($totalBytesIn, $rIn);
                $totalBytesOut = max($totalBytesOut, $rOut);
            }
        } catch (\Throwable $e) {}

        $totalBytesAllTime = $totalBytesIn + $totalBytesOut;

        // 4. Build chart data (24-Hour hourly breakdown if 1 single day, continuous daily range if multi-day)
        $isSingleDay = ($period === 'today' || $period === 'yesterday') 
            || ($startDate && $endDate && $startDate->isSameDay($endDate));

        if ($isSingleDay) {
            $targetDate = $startDate ?: ($period === 'yesterday' ? $today->copy()->subDay() : $today);
            $hourlyData = $this->buildHourlyChartData($username, $targetDate, $isOnline, $liveSessions);
            $chartCategories = $hourlyData['categories'];
            $chartRxSeries = $hourlyData['rx_series'];
            $chartTxSeries = $hourlyData['tx_series'];
            $isHourlyChart = true;
        } else {
            $isHourlyChart = false;
            $chartCategories = [];
            $chartRxSeries = [];
            $chartTxSeries = [];

            $chartStart = $startDate ?: ($allDailySummaries->isNotEmpty() ? Carbon::parse($allDailySummaries->last()->usage_date) : $today->copy()->subDays(13));
            $chartEnd = $endDate ?: $today->copy();
            $daysDiff = min(60, max(1, $chartStart->diffInDays($chartEnd) + 1));

            $dailyKeyed = $allDailySummaries->keyBy(fn($d) => Carbon::parse($d->usage_date)->format('Y-m-d'));
            for ($i = $daysDiff - 1; $i >= 0; $i--) {
                $date = $chartEnd->copy()->subDays($i);
                $dKey = $date->format('Y-m-d');
                $chartCategories[] = $date->format('d M');

                $summary = $dailyKeyed->get($dKey);
                $rxMb = $summary ? round($summary->total_bytes_out / 1024 / 1024, 2) : 0;
                $txMb = $summary ? round($summary->total_bytes_in / 1024 / 1024, 2) : 0;

                // If today and online, incorporate live session
                if ($date->isToday() && $isOnline && $primaryLiveSession) {
                    $rxMb = max($rxMb, round(($primaryLiveSession['bytes-out'] ?? 0) / 1024 / 1024, 2));
                    $txMb = max($txMb, round(($primaryLiveSession['bytes-in'] ?? 0) / 1024 / 1024, 2));
                }

                $chartRxSeries[] = $rxMb;
                $chartTxSeries[] = $txMb;
            }
        }

        $dailySummaries = $filteredDailySummaries;

        // 5. Calculate Uptime Progress & Remaining Time for Primary Live Session
        $uptimeLimit = $user?->uptime_limit ?: ($user?->profile?->validity ?: ($primaryLiveSession['limit-uptime'] ?? null));
        $sessionTimeLeft = $primaryLiveSession['session-time-left'] ?? null;
        $uptimeProgress = FormatHelper::getUptimeProgress(
            $primaryLiveSession['uptime'] ?? '0s',
            $uptimeLimit,
            $sessionTimeLeft
        );

        $fupProgress = $user ? app(\App\Services\FupManagementService::class)->getUserFupProgress($user, ($primaryLiveSession ? ((int)($primaryLiveSession['bytes-in'] ?? 0) + (int)($primaryLiveSession['bytes-out'] ?? 0)) : 0)) : null;

        return view('users.show', compact(
            'user',
            'username',
            'isOnline',
            'primaryLiveSession',
            'liveSessions',
            'uptimeProgress',
            'uptimeLimit',
            'fupProgress',
            'pastSessions',
            'dailySummaries',
            'todayUsage',
            'monthUsage',
            'totalBytesAllTime',
            'totalBytesIn',
            'totalBytesOut',
            'totalUptimeSeconds',
            'totalSessionsCount',
            'chartCategories',
            'chartRxSeries',
            'chartTxSeries',
            'isHourlyChart',
            'period',
            'startDate',
            'endDate',
            'sessionSearch',
            'sessionStatus'
        ));
    }

    /**
     * Build 24-hour (00:00 - 23:00) hourly breakdown series for a single day filter.
     */
    protected function buildHourlyChartData(?string $username, Carbon $targetDate, bool $coversLive, array $liveSessions = []): array
    {
        $categories = [];
        for ($h = 0; $h < 24; $h++) {
            $categories[] = sprintf('%02d:00', $h);
        }

        $hourlyRxBytes = array_fill(0, 24, 0);
        $hourlyTxBytes = array_fill(0, 24, 0);

        // 1. Fetch exact snapshots from UsageSnapshot table
        $snapshotQuery = UsageSnapshot::whereBetween('recorded_at', [
            $targetDate->copy()->startOfDay(),
            $targetDate->copy()->endOfDay()
        ]);

        if ($username) {
            $snapshotQuery->whereHas('session', fn($q) => $q->where('username', $username));
        }

        $snapshots = $snapshotQuery->get();

        if ($snapshots->isNotEmpty()) {
            foreach ($snapshots as $snap) {
                $hour = (int) $snap->recorded_at->format('H');
                if ($hour >= 0 && $hour < 24) {
                    $hourlyRxBytes[$hour] += (int) ($snap->delta_bytes_out ?? 0);
                    $hourlyTxBytes[$hour] += (int) ($snap->delta_bytes_in ?? 0);
                }
            }
        } else {
            // 2. Fallback: check HotspotSession records for that day
            $sessionQuery = HotspotSession::where(function ($q) use ($targetDate) {
                $q->whereBetween('started_at', [$targetDate->copy()->startOfDay(), $targetDate->copy()->endOfDay()])
                  ->orWhereBetween('last_seen_at', [$targetDate->copy()->startOfDay(), $targetDate->copy()->endOfDay()]);
            });

            if ($username) {
                $sessionQuery->where('username', $username);
            }

            $sessions = $sessionQuery->get();

            if ($sessions->isNotEmpty()) {
                foreach ($sessions as $s) {
                    $sHour = $s->started_at ? (int) Carbon::parse($s->started_at)->format('H') : 0;
                    $sHour = min(23, max(0, $sHour));
                    $hourlyRxBytes[$sHour] += (int) $s->total_bytes_out;
                    $hourlyTxBytes[$sHour] += (int) $s->total_bytes_in;
                }
            } else {
                // 3. Fallback to DailyUserUsageSummary if only daily aggregate exists
                $dailySummaryQuery = DailyUserUsageSummary::where('usage_date', $targetDate->toDateString());
                if ($username) {
                    $dailySummaryQuery->where('username', $username);
                }
                $dailySummaries = $dailySummaryQuery->get();
                if ($dailySummaries->isNotEmpty()) {
                    $totalIn = $dailySummaries->sum('total_bytes_in');
                    $totalOut = $dailySummaries->sum('total_bytes_out');
                    $targetHour = $targetDate->isToday() ? (int) now()->format('H') : 12;
                    $hourlyRxBytes[$targetHour] += (int) $totalOut;
                    $hourlyTxBytes[$targetHour] += (int) $totalIn;
                }
            }
        }

        // 4. If period is today and live sessions exist, ensure current hour has active uncollected counters
        if ($targetDate->isToday() && $coversLive && !empty($liveSessions)) {
            $nowHour = (int) now()->format('H');
            $relevantLive = $liveSessions;
            if ($username) {
                $relevantLive = collect($liveSessions)->filter(fn($s) => ($s['user'] ?? '') === $username)->all();
            }

            $liveRx = collect($relevantLive)->sum(fn($s) => (int) ($s['bytes-out'] ?? 0));
            $liveTx = collect($relevantLive)->sum(fn($s) => (int) ($s['bytes-in'] ?? 0));

            $hourlyRxBytes[$nowHour] = max($hourlyRxBytes[$nowHour], $liveRx);
            $hourlyTxBytes[$nowHour] = max($hourlyTxBytes[$nowHour], $liveTx);
        }

        // Convert bytes to MB for series
        $rxSeries = array_map(fn($b) => round($b / 1024 / 1024, 2), $hourlyRxBytes);
        $txSeries = array_map(fn($b) => round($b / 1024 / 1024, 2), $hourlyTxBytes);

        return [
            'categories' => $categories,
            'rx_series' => $rxSeries,
            'tx_series' => $txSeries,
            'is_hourly' => true,
        ];
    }

    public function historical(Request $request, RouterOsService $routerOs): View|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $period = $request->get('period', '7days');
        $today = Carbon::today();

        $startDate = match ($period) {
            'today' => $today->copy(),
            'yesterday' => $today->copy()->subDay(),
            '30days' => $today->copy()->subDays(29),
            'this_month' => $today->copy()->startOfMonth(),
            'custom' => $request->filled('start_date') ? Carbon::parse($request->start_date) : $today->copy()->subDays(6),
            default => $today->copy()->subDays(6),
        };

        $endDate = match ($period) {
            'yesterday' => $today->copy()->subDay(),
            'custom' => $request->filled('end_date') ? Carbon::parse($request->end_date) : $today->copy(),
            default => $today->copy(),
        };

        $coversToday = $endDate->gte($today) && $startDate->lte($today);

        // 1. Run quick collector synchronization if filter covers today
        if ($coversToday) {
            try {
                (new CollectorService($routerOs))->collect();
            } catch (\Throwable $e) {
                // Non-blocking
            }
        }

        // 2. Fetch live active sessions to check online status
        $liveSessions = [];
        try {
            $liveSessions = $routerOs->getActiveHotspotSessions();
        } catch (\Throwable $e) {
            $liveSessions = [];
        }

        $onlineUserMap = collect($liveSessions)->keyBy('user');

        // 3. Fetch database historical summaries STRICTLY within the date range
        $dbSummaries = DailyUserUsageSummary::whereBetween('usage_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('username, sum(total_bytes_in) as total_in, sum(total_bytes_out) as total_out, sum(total_bytes) as total_usage, sum(total_uptime_seconds) as total_uptime, sum(session_count) as total_sessions, max(usage_date) as last_seen_date')
            ->groupBy('username')
            ->get()
            ->keyBy('username');

        // Preload hotspot user profiles
        $hotspotUsers = HotspotUser::with('profile')->get()->keyBy('username');
        $profiles = HotspotProfile::all();

        // 4. Merge data across users active in THIS specific period
        $activeUsernames = $dbSummaries->keys();
        if ($coversToday) {
            $activeUsernames = $activeUsernames->merge($onlineUserMap->keys());
        }
        $activeUsernames = $activeUsernames->unique()->filter();

        $summaries = $activeUsernames->map(function ($username) use ($dbSummaries, $hotspotUsers, $onlineUserMap, $coversToday) {
            $db = $dbSummaries->get($username);
            $userModel = $hotspotUsers->get($username);
            $live = $onlineUserMap->get($username);

            $totalIn = $db ? (int) $db->total_in : 0;
            $totalOut = $db ? (int) $db->total_out : 0;
            $totalUptime = $db ? (int) $db->total_uptime : 0;
            $totalSessions = $db ? (int) $db->total_sessions : 0;

            // Factor in live session counters from MikroTik ONLY IF period covers today
            if ($coversToday && $live) {
                $liveIn = (int) ($live['bytes-in'] ?? 0);
                $liveOut = (int) ($live['bytes-out'] ?? 0);
                $liveUptime = FormatHelper::parseUptime($live['uptime'] ?? '0s');

                $totalIn = max($totalIn, $liveIn);
                $totalOut = max($totalOut, $liveOut);
                $totalUptime = max($totalUptime, $liveUptime);
                if ($totalSessions === 0) $totalSessions = 1;
            }

            $totalUsage = $totalIn + $totalOut;

            return [
                'username' => $username,
                'profile_name' => $userModel?->profile?->name ?? ($live['profile'] ?? '-'),
                'is_online' => $onlineUserMap->has($username),
                'total_sessions' => max(1, $totalSessions),
                'total_uptime' => $totalUptime,
                'total_uptime_formatted' => FormatHelper::formatUptime($totalUptime),
                'total_in' => $totalIn,
                'total_in_formatted' => FormatHelper::formatBytes($totalIn),
                'total_out' => $totalOut,
                'total_out_formatted' => FormatHelper::formatBytes($totalOut),
                'total_usage' => $totalUsage,
                'total_usage_formatted' => FormatHelper::formatBytes($totalUsage),
                'last_seen' => $onlineUserMap->has($username) ? 'Sedang Online' : ($db?->last_seen_date ? Carbon::parse($db->last_seen_date)->format('d M Y') : 'Hari ini'),
            ];
        })->filter(fn($s) => $s['total_usage'] > 0 || $s['is_online'])->sortByDesc('total_usage')->values();

        // 5. Chart aggregation (24-Hour hourly breakdown if 1 single day, continuous daily range if multi-day)
        $isSingleDay = ($period === 'today' || $period === 'yesterday') 
            || ($startDate && $endDate && $startDate->isSameDay($endDate));

        if ($isSingleDay) {
            $targetDate = $startDate ?: ($period === 'yesterday' ? $today->copy()->subDay() : $today);
            $hourlyData = $this->buildHourlyChartData(null, $targetDate, $coversToday, $liveSessions);
            $chartCategories = $hourlyData['categories'];
            $chartRxSeries = $hourlyData['rx_series'];
            $chartTxSeries = $hourlyData['tx_series'];
            $chartTotalSeries = array_map(fn($rx, $tx) => round($rx + $tx, 2), $chartRxSeries, $chartTxSeries);
            $isHourlyChart = true;
        } else {
            $isHourlyChart = false;
            $dailyTrendRaw = DailyUserUsageSummary::whereBetween('usage_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->selectRaw('usage_date, sum(total_bytes_in) as daily_in, sum(total_bytes_out) as daily_out, sum(total_bytes) as daily_total, count(distinct username) as active_users')
                ->groupBy('usage_date')
                ->orderBy('usage_date')
                ->get()
                ->keyBy(fn($item) => Carbon::parse($item->usage_date)->format('Y-m-d'));

            $chartCategories = [];
            $chartRxSeries = [];
            $chartTxSeries = [];
            $chartTotalSeries = [];

            $cursor = $startDate->copy();
            while ($cursor->lte($endDate)) {
                $dKey = $cursor->format('Y-m-d');
                $label = $cursor->format('d M');
                $chartCategories[] = $label;

                $dData = $dailyTrendRaw->get($dKey);
                $rxMb = $dData ? round($dData->daily_out / 1024 / 1024, 2) : 0;
                $txMb = $dData ? round($dData->daily_in / 1024 / 1024, 2) : 0;
                $totalMb = $dData ? round($dData->daily_total / 1024 / 1024, 2) : 0;

                // If today and period covers today, factor in live active sessions to chart
                if ($cursor->isToday() && $coversToday && !empty($liveSessions)) {
                    $liveInMb = round(collect($liveSessions)->sum(fn($s) => (int)($s['bytes-in'] ?? 0)) / 1024 / 1024, 2);
                    $liveOutMb = round(collect($liveSessions)->sum(fn($s) => (int)($s['bytes-out'] ?? 0)) / 1024 / 1024, 2);
                    $rxMb = max($rxMb, $liveOutMb);
                    $txMb = max($txMb, $liveInMb);
                    $totalMb = max($totalMb, $rxMb + $txMb);
                }

                $chartRxSeries[] = $rxMb;
                $chartTxSeries[] = $txMb;
                $chartTotalSeries[] = $totalMb;

                $cursor->addDay();
            }
        }

        $totalDataPeriod = $summaries->sum('total_usage');
        $totalSessionsPeriod = $summaries->sum('total_sessions');
        $uniqueUsersCount = $summaries->count();

        // 5. CSV Export Feature
        if ($request->get('export') === 'csv') {
            $filename = "historical-usage-{$period}-" . now()->format('Ymd-His') . ".csv";
            return response()->streamDownload(function () use ($summaries, $startDate, $endDate) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Laporan Histori Pemakaian Kuota & Bandwidth MikroTik']);
                fputcsv($handle, ['Periode', $startDate->format('d M Y') . ' s/d ' . $endDate->format('d M Y')]);
                fputcsv($handle, ['Generated At', now()->toDateTimeString()]);
                fputcsv($handle, []);
                fputcsv($handle, ['Username', 'Profile', 'Status', 'Sesi', 'Total Uptime', 'Upload (Bytes)', 'Download (Bytes)', 'Total Pemakaian (Bytes)', 'Total Pemakaian (Readable)']);

                foreach ($summaries as $s) {
                    fputcsv($handle, [
                        $s['username'],
                        $s['profile_name'],
                        $s['is_online'] ? 'Online' : 'Offline',
                        $s['total_sessions'],
                        $s['total_uptime_formatted'],
                        $s['total_in'],
                        $s['total_out'],
                        $s['total_usage'],
                        $s['total_usage_formatted'],
                    ]);
                }
                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]);
        }

        return view('users.historical', compact(
            'summaries',
            'period',
            'startDate',
            'endDate',
            'totalDataPeriod',
            'totalSessionsPeriod',
            'uniqueUsersCount',
            'profiles',
            'chartCategories',
            'chartRxSeries',
            'chartTxSeries',
            'chartTotalSeries',
            'isHourlyChart'
        ));
    }

    public function disconnect(string $username, RouterOsService $routerOs): JsonResponse
    {
        $routerOs->disconnectHotspotUser($username);

        HotspotSession::where('username', $username)->where('status', 'active')->update([
            'status' => 'ended',
            'ended_at' => now(),
            'current_rx_bps' => 0,
            'current_tx_bps' => 0,
        ]);

        return response()->json(['success' => true, 'message' => "User {$username} berhasil diputus"]);
    }

    public function userLiveData(string $username, RouterOsService $routerOs): JsonResponse
    {
        $rawActive = $routerOs->getActiveHotspotSessions();
        $userSession = collect($rawActive)->firstWhere('user', $username);

        $rxBps = (int) ($userSession['rx-rate'] ?? 0);
        $txBps = (int) ($userSession['tx-rate'] ?? 0);
        $bytesIn = (int) ($userSession['bytes-in'] ?? 0);
        $bytesOut = (int) ($userSession['bytes-out'] ?? 0);
        $uptime = $userSession['uptime'] ?? '0s';

        $user = HotspotUser::with('profile')->where('username', $username)->first();
        $uptimeLimit = $user?->uptime_limit ?: ($user?->profile?->validity ?: ($userSession['limit-uptime'] ?? null));
        $sessionTimeLeft = $userSession['session-time-left'] ?? null;

        $progress = FormatHelper::getUptimeProgress($uptime, $uptimeLimit, $sessionTimeLeft);
        $fupProgress = $user ? app(\App\Services\FupManagementService::class)->getUserFupProgress($user, ($bytesIn + $bytesOut)) : null;

        return response()->json([
            'is_online' => !empty($userSession),
            'rx_bps' => $rxBps,
            'tx_bps' => $txBps,
            'download_speed' => FormatHelper::formatBytes($rxBps / 8, 1) . '/s',
            'upload_speed' => FormatHelper::formatBytes($txBps / 8, 1) . '/s',
            'bytes_in' => $bytesIn,
            'bytes_out' => $bytesOut,
            'uptime' => $uptime,
            'uptime_formatted' => $progress['used_formatted'],
            'has_limit' => $progress['has_limit'],
            'limit_uptime' => $progress['limit_formatted'],
            'remaining_uptime' => $progress['remaining_formatted'],
            'remaining_seconds' => $progress['remaining_seconds'],
            'remaining_percentage' => $progress['remaining_percent'],
            'total_bytes_formatted' => FormatHelper::formatBytes($bytesIn + $bytesOut, 2),
            'fup' => $fupProgress,
        ]);
    }

    /**
     * Manually reset FUP status and quota for a user.
     */
    public function resetFup(string $username, RouterOsService $routerOs): JsonResponse
    {
        $user = HotspotUser::where('username', $username)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => "User {$username} tidak ditemukan."], 404);
        }

        app(\App\Services\FupManagementService::class)->resetUserFupManual($user, $routerOs);

        return response()->json([
            'success' => true,
            'message' => "Kuota FUP dan kecepatan untuk {$username} berhasil di-reset normal kembali.",
        ]);
    }

    /**
     * Build map of MAC & IP to host-name from MikroTik DHCP leases.
     */
    protected function getDhcpLeaseMap(RouterOsService $routerOs): array
    {
        // ponytail: consolidated DHCP lease hostname mapping
        $leaseMap = [];
        try {
            $dhcpLeases = $routerOs->getDhcpLeases();
            foreach ($dhcpLeases as $lease) {
                $lHost = $lease['host-name'] ?? ($lease['comment'] ?? null);
                $lMac = !empty($lease['mac-address']) ? strtoupper($lease['mac-address']) : (!empty($lease['active-mac-address']) ? strtoupper($lease['active-mac-address']) : null);
                $lIp = $lease['address'] ?? ($lease['active-address'] ?? null);
                if ($lMac && $lHost) $leaseMap[$lMac] = $lHost;
                if ($lIp && $lHost) $leaseMap[$lIp] = $lHost;
            }
        } catch (\Throwable $e) {}

        return $leaseMap;
    }
}
