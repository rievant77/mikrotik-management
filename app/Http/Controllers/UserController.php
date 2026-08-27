<?php

namespace App\Http\Controllers;

use App\Models\DailyUserUsageSummary;
use App\Models\HotspotProfile;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use App\Services\CollectorService;
use App\Services\RouterOsService;
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

        // Preload user profiles for mapping
        $hotspotUsers = HotspotUser::with('profile')->get()->keyBy('username');

        $sessions = collect($rawSessions)->map(function ($raw, $idx) use ($hotspotUsers) {
            $username = $raw['user'] ?? '-';
            $userModel = $hotspotUsers->get($username);
            $bytesIn = (int) ($raw['bytes-in'] ?? 0);
            $bytesOut = (int) ($raw['bytes-out'] ?? 0);

            return [
                'id' => $raw['.id'] ?? ($username . '-' . $idx),
                'username' => $username,
                'ip_address' => $raw['address'] ?? '-',
                'mac_address' => $raw['mac-address'] ?? '-',
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

        return view('users.index', compact('sessions', 'profiles'));
    }

    /**
     * Dedicated Real-time Online Users JSON API endpoint
     */
    public function liveData(RouterOsService $routerOs): JsonResponse
    {
        $rawSessions = $routerOs->getActiveHotspotSessions();
        $hotspotUsers = HotspotUser::with('profile')->get()->keyBy('username');

        $sessions = collect($rawSessions)->map(function ($raw, $idx) use ($hotspotUsers) {
            $username = $raw['user'] ?? '-';
            $userModel = $hotspotUsers->get($username);
            $bytesIn = (int) ($raw['bytes-in'] ?? 0);
            $bytesOut = (int) ($raw['bytes-out'] ?? 0);

            return [
                'id' => $raw['.id'] ?? ($username . '-' . $idx),
                'username' => $username,
                'ip_address' => $raw['address'] ?? '-',
                'mac_address' => $raw['mac-address'] ?? '-',
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

        return response()->json([
            'sessions' => $sessions->values(),
            'count' => $sessions->count(),
            'timestamp' => now()->format('H:i:s'),
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }

    public function show(string $username, RouterOsService $routerOs): View
    {
        $user = HotspotUser::with('profile')->where('username', $username)->first();

        // 1. Fetch live active session from MikroTik RouterOS
        $liveSessions = [];
        try {
            $rawActive = $routerOs->getActiveHotspotSessions();
            $liveSessions = collect($rawActive)->filter(fn($s) => ($s['user'] ?? '') === $username)->values()->all();
        } catch (\Throwable $e) {
            $liveSessions = [];
        }

        $isOnline = !empty($liveSessions);
        $primaryLiveSession = $isOnline ? $liveSessions[0] : null;

        // 2. Fetch past sessions from database
        $pastSessions = HotspotSession::where('username', $username)
            ->latest('started_at')
            ->limit(15)
            ->get();

        // 3. Fetch daily summaries (Last 30 days) for charts & metrics
        $dailySummaries = DailyUserUsageSummary::where('username', $username)
            ->orderBy('usage_date', 'desc')
            ->limit(30)
            ->get();

        $totalBytesIn = $dailySummaries->sum('total_bytes_in');
        $totalBytesOut = $dailySummaries->sum('total_bytes_out');
        $totalUptimeSeconds = $dailySummaries->sum('total_uptime_seconds');
        $totalSessionsCount = max($dailySummaries->sum('session_count'), $pastSessions->count());

        // Factor in live session if active
        if ($isOnline && $primaryLiveSession) {
            $liveIn = (int) ($primaryLiveSession['bytes-in'] ?? 0);
            $liveOut = (int) ($primaryLiveSession['bytes-out'] ?? 0);
            $liveUptime = FormatHelper::parseUptime($primaryLiveSession['uptime'] ?? '0s');

            if ($totalBytesIn < $liveIn) $totalBytesIn += $liveIn;
            if ($totalBytesOut < $liveOut) $totalBytesOut += $liveOut;
            $totalUptimeSeconds += $liveUptime;
            if ($totalSessionsCount === 0) $totalSessionsCount = 1;
        }

        $totalBytesAllTime = $totalBytesIn + $totalBytesOut;

        // 4. Build chart data for the last 14 days
        $chartCategories = [];
        $chartRxSeries = [];
        $chartTxSeries = [];

        $dailyKeyed = $dailySummaries->keyBy(fn($d) => Carbon::parse($d->usage_date)->format('Y-m-d'));
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dKey = $date->format('Y-m-d');
            $chartCategories[] = $date->format('d M');

            $summary = $dailyKeyed->get($dKey);
            $rxMb = $summary ? round($summary->total_bytes_out / 1024 / 1024, 2) : 0;
            $txMb = $summary ? round($summary->total_bytes_in / 1024 / 1024, 2) : 0;

            // If today and online, incorporate live data
            if ($i === 0 && $isOnline && $primaryLiveSession) {
                $rxMb = max($rxMb, round(($primaryLiveSession['bytes-out'] ?? 0) / 1024 / 1024, 2));
                $txMb = max($txMb, round(($primaryLiveSession['bytes-in'] ?? 0) / 1024 / 1024, 2));
            }

            $chartRxSeries[] = $rxMb;
            $chartTxSeries[] = $txMb;
        }

        return view('users.show', compact(
            'user',
            'username',
            'isOnline',
            'primaryLiveSession',
            'liveSessions',
            'pastSessions',
            'dailySummaries',
            'totalBytesAllTime',
            'totalBytesIn',
            'totalBytesOut',
            'totalUptimeSeconds',
            'totalSessionsCount',
            'chartCategories',
            'chartRxSeries',
            'chartTxSeries'
        ));
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

        // 1. Fetch live active sessions to check online status & live counters
        $liveSessions = [];
        try {
            $liveSessions = $routerOs->getActiveHotspotSessions();
        } catch (\Throwable $e) {
            $liveSessions = [];
        }

        $onlineUserMap = collect($liveSessions)->keyBy('user');
        $coversToday = $endDate->gte($today) && $startDate->lte($today);

        // 2. Fetch database historical summaries
        $dbSummaries = DailyUserUsageSummary::whereBetween('usage_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('username, sum(total_bytes_in) as total_in, sum(total_bytes_out) as total_out, sum(total_bytes) as total_usage, sum(total_uptime_seconds) as total_uptime, sum(session_count) as total_sessions, max(usage_date) as last_seen_date')
            ->groupBy('username')
            ->get()
            ->keyBy('username');

        // Preload hotspot user profiles
        $hotspotUsers = HotspotUser::with('profile')->get()->keyBy('username');
        $profiles = HotspotProfile::all();

        // 3. Merge data across all users
        $allUsernames = $dbSummaries->keys()->merge($hotspotUsers->keys());
        if ($coversToday) {
            $allUsernames = $allUsernames->merge($onlineUserMap->keys());
        }
        $allUsernames = $allUsernames->unique()->filter();

        $summaries = $allUsernames->map(function ($username) use ($dbSummaries, $hotspotUsers, $onlineUserMap, $coversToday) {
            $db = $dbSummaries->get($username);
            $userModel = $hotspotUsers->get($username);
            $live = $onlineUserMap->get($username);

            $totalIn = $db ? (int) $db->total_in : 0;
            $totalOut = $db ? (int) $db->total_out : 0;
            $totalUptime = $db ? (int) $db->total_uptime : 0;
            $totalSessions = $db ? (int) $db->total_sessions : 0;

            // If user is currently online and period covers today, factor in live session delta if not in DB
            if ($coversToday && $live) {
                $liveIn = (int) ($live['bytes-in'] ?? 0);
                $liveOut = (int) ($live['bytes-out'] ?? 0);
                // Ensure live counters reflect minimum current session usage
                if ($totalIn < $liveIn) $totalIn += $liveIn;
                if ($totalOut < $liveOut) $totalOut += $liveOut;
                if ($totalSessions === 0) $totalSessions = 1;
            }

            $totalUsage = $totalIn + $totalOut;

            return [
                'username' => $username,
                'profile_name' => $userModel?->profile?->name ?? ($live['profile'] ?? '-'),
                'is_online' => $onlineUserMap->has($username),
                'total_sessions' => max(1, $totalSessions),
                'total_uptime' => $totalUptime,
                'total_uptime_formatted' => \App\Support\FormatHelper::formatUptime($totalUptime),
                'total_in' => $totalIn,
                'total_in_formatted' => \App\Support\FormatHelper::formatBytes($totalIn),
                'total_out' => $totalOut,
                'total_out_formatted' => \App\Support\FormatHelper::formatBytes($totalOut),
                'total_usage' => $totalUsage,
                'total_usage_formatted' => \App\Support\FormatHelper::formatBytes($totalUsage),
                'last_seen' => $onlineUserMap->has($username) ? 'Sedang Online' : ($db?->last_seen_date ? Carbon::parse($db->last_seen_date)->format('d M Y') : 'Hari ini'),
            ];
        })->filter(fn($s) => $s['total_usage'] > 0 || $s['is_online'])->sortByDesc('total_usage')->values();

        // 4. Daily Trend aggregation for chart
        $dailyTrendRaw = DailyUserUsageSummary::whereBetween('usage_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('usage_date, sum(total_bytes_in) as daily_in, sum(total_bytes_out) as daily_out, sum(total_bytes) as daily_total, count(distinct username) as active_users')
            ->groupBy('usage_date')
            ->orderBy('usage_date')
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->usage_date)->format('Y-m-d'));

        // Build continuous date range
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

            $chartRxSeries[] = $rxMb;
            $chartTxSeries[] = $txMb;
            $chartTotalSeries[] = $totalMb;

            $cursor->addDay();
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
            'chartTotalSeries'
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
}
