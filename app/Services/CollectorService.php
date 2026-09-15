<?php

namespace App\Services;

use App\Models\CashierShift;
use App\Models\DailyUserUsageSummary;
use App\Models\HotspotProfile;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use App\Models\RouterSetting;
use App\Models\UsageSnapshot;
use App\Models\VoucherSale;
use App\Support\FormatHelper;
use App\Support\DeviceHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CollectorService
{
    protected RouterOsService $routerOs;

    public function __construct(?RouterOsService $routerOs = null)
    {
        $this->routerOs = $routerOs ?? new RouterOsService();
    }

    /**
     * Execute a single collector accounting run.
     */
    public function collect(): array
    {
        $lock = Cache::lock('mikrotik_collector_lock', 10);
        if (!$lock->get()) {
            return ['status' => 'skipped', 'message' => 'Previous collector job is still running'];
        }

        $collectorRunId = (string) Str::uuid();
        $now = Carbon::now();
        $processedCount = 0;

        try {
            $rawSessions = $this->routerOs->getActiveHotspotSessions();
            try {
                $dhcpLeases = $this->routerOs->getDhcpLeases();
            } catch (\Throwable $e) {
                $dhcpLeases = [];
            }

            // Build map of MAC & IP to host-name from MikroTik DHCP Lease table
            $leaseHostnameMap = [];
            foreach ($dhcpLeases as $lease) {
                $lHost = $lease['host-name'] ?? ($lease['comment'] ?? null);
                if ($lHost) {
                    if (!empty($lease['mac-address'])) {
                        $leaseHostnameMap[strtoupper($lease['mac-address'])] = $lHost;
                    }
                    if (!empty($lease['active-mac-address'])) {
                        $leaseHostnameMap[strtoupper($lease['active-mac-address'])] = $lHost;
                    }
                    if (!empty($lease['address'])) {
                        $leaseHostnameMap[$lease['address']] = $lHost;
                    }
                }
            }

            $seenSessionIds = [];
            $activeShift = CashierShift::where('status', 'open')->latest()->first();

            DB::beginTransaction();

            foreach ($rawSessions as $raw) {
                $username = $raw['user'] ?? null;
                if (!$username) {
                    continue;
                }

                $mac = $raw['mac-address'] ?? null;
                $macKey = $mac ? strtoupper($mac) : null;
                $ip = $raw['address'] ?? null;

                // Lookup host-name from MikroTik DHCP lease or Hotspot host comment
                $hostname = $raw['host-name'] 
                    ?? ($leaseHostnameMap[$macKey] ?? null) 
                    ?? ($leaseHostnameMap[$ip] ?? null) 
                    ?? ($raw['comment'] ?? null);
                $deviceName = \App\Support\DeviceHelper::resolveDeviceName($hostname, $mac);

                $bytesIn = (int) ($raw['bytes-in'] ?? 0);
                $bytesOut = (int) ($raw['bytes-out'] ?? 0);
                $uptimeStr = $raw['uptime'] ?? '0s';
                $uptimeSeconds = FormatHelper::parseUptime($uptimeStr);

                // Find or link with HotspotUser
                $hotspotUser = HotspotUser::where('username', $username)->first();

                // Find active session for this user + mac + ip
                $session = HotspotSession::where('username', $username)
                    ->where('status', 'active')
                    ->when($mac, fn($q) => $q->where('mac_address', $mac))
                    ->latest()
                    ->first();

                $isNewSession = false;
                if (!$session) {
                    $isNewSession = true;
                    // New session started
                    $session = HotspotSession::create([
                        'hotspot_user_id' => $hotspotUser?->id,
                        'username' => $username,
                        'mac_address' => $mac,
                        'device_name' => $deviceName,
                        'hostname' => $hostname,
                        'ip_address' => $ip,
                        'interface' => 'hotspot',
                        'started_at' => $now,
                        'last_seen_at' => $now,
                        'status' => 'active',
                        'last_bytes_in' => $bytesIn,
                        'last_bytes_out' => $bytesOut,
                        'total_bytes_in' => $bytesIn,
                        'total_bytes_out' => $bytesOut,
                        'current_rx_bps' => 0,
                        'current_tx_bps' => 0,
                    ]);

                    // Trigger Voucher First-Activation Revenue if not yet recorded
                    $this->handleVoucherFirstActivation($hotspotUser, $username, $now, $activeShift);
                }

                $seenSessionIds[] = $session->id;

                // Calculate Deltas (handle router reboot or counter reset)
                if ($isNewSession) {
                    $deltaIn = $bytesIn;
                    $deltaOut = $bytesOut;
                    $timeDiff = max(1, $uptimeSeconds);
                } else {
                    $deltaIn = ($bytesIn >= $session->last_bytes_in) ? ($bytesIn - $session->last_bytes_in) : $bytesIn;
                    $deltaOut = ($bytesOut >= $session->last_bytes_out) ? ($bytesOut - $session->last_bytes_out) : $bytesOut;
                    $timeDiff = max(1, $now->diffInSeconds($session->last_seen_at));
                }

                $uploadBps = (int) (($deltaIn * 8) / max(1, $timeDiff));
                $downloadBps = (int) (($deltaOut * 8) / max(1, $timeDiff));

                // Record Snapshot (Idempotent per collector_run_id + session_id)
                UsageSnapshot::create([
                    'session_id' => $session->id,
                    'recorded_at' => $now,
                    'bytes_in' => $bytesIn,
                    'bytes_out' => $bytesOut,
                    'delta_bytes_in' => $deltaIn,
                    'delta_bytes_out' => $deltaOut,
                    'upload_bps' => $uploadBps,
                    'download_bps' => $downloadBps,
                    'uptime_seconds' => $uptimeSeconds,
                    'collector_run_id' => $collectorRunId,
                    'created_at' => $now,
                ]);

                // Update Session record with latest traffic & device name
                $sessionUpdate = [
                    'last_bytes_in' => $bytesIn,
                    'last_bytes_out' => $bytesOut,
                    'total_bytes_in' => $isNewSession ? $bytesIn : ($session->total_bytes_in + $deltaIn),
                    'total_bytes_out' => $isNewSession ? $bytesOut : ($session->total_bytes_out + $deltaOut),
                    'current_rx_bps' => $downloadBps,
                    'current_tx_bps' => $uploadBps,
                    'last_seen_at' => $now,
                ];

                if ($deviceName && (empty($session->device_name) || $session->device_name === 'Unknown Device')) {
                    $sessionUpdate['device_name'] = $deviceName;
                    $sessionUpdate['hostname'] = $hostname;
                }

                $session->update($sessionUpdate);

                // Accumulate Daily Summary
                $this->accumulateDailySummary($hotspotUser, $username, $now->toDateString(), $deltaIn, $deltaOut, $timeDiff);

                // Evaluate FUP (Fair Usage Policy) Bandwidth Management
                if ($hotspotUser) {
                    try {
                        app(FupManagementService::class)->evaluateUserFup($hotspotUser, ($deltaIn + $deltaOut), $this->routerOs);
                    } catch (\Throwable $e) {
                        Log::warning("FUP evaluation error for {$username}: " . $e->getMessage());
                    }
                }

                $processedCount++;
            }

            // Close sessions not seen in current polling run (Grace period: 30s)
            HotspotSession::where('status', 'active')
                ->whereNotIn('id', $seenSessionIds)
                ->where('last_seen_at', '<', $now->copy()->subSeconds(30))
                ->update([
                    'status' => 'ended',
                    'ended_at' => $now,
                    'current_rx_bps' => 0,
                    'current_tx_bps' => 0,
                ]);

            DB::commit();

            // Sweep and execute Expired Mode for finished vouchers/users
            try {
                app(VoucherExpiryService::class)->sweepExpiredUsers($this->routerOs);
            } catch (\Throwable $e) {
                Log::warning("Expired users sweep error: " . $e->getMessage());
            }

            // Record hourly traffic category snapshot if available
            try {
                (new TrafficAnalyticsService($this->routerOs))->recordHourlySnapshot();
            } catch (\Throwable $e) {
                // Non-blocking
            }

            // Update Router status
            RouterSetting::where('is_active', true)->update([
                'last_successful_poll_at' => $now,
                'last_error_at' => null,
            ]);

            return [
                'status' => 'success',
                'collector_run_id' => $collectorRunId,
                'sessions_processed' => $processedCount,
                'timestamp' => $now->toDateTimeString(),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("CollectorService error: " . $e->getMessage());

            RouterSetting::where('is_active', true)->update([
                'last_error_at' => $now,
                'last_error_message' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        } finally {
            $lock->release();
        }
    }

    /**
     * Trigger Voucher First-Activation Revenue & Compute Validity Expired Timestamp:
     * Only records transaction when a voucher is first activated on MikroTik (PRD Section 5.11.1).
     */
    protected function handleVoucherFirstActivation(?HotspotUser $user, string $username, Carbon $activatedAt, ?CashierShift $shift): void
    {
        $profile = $user?->profile;
        if (!$profile) {
            $profile = HotspotProfile::first();
        }

        // Set expired_at calendar timestamp if user/profile has validity limit
        if ($user && !$user->expired_at) {
            $validityRaw = $user->uptime_limit ?: ($profile?->validity ?: null);
            $validitySeconds = \App\Support\FormatHelper::parseUptime(\App\Support\FormatHelper::parseValidityToRouterTime($validityRaw));
            if ($validitySeconds > 0) {
                $user->update(['expired_at' => $activatedAt->copy()->addSeconds($validitySeconds)]);
            }
        }

        // Check if already recorded in sales
        $exists = VoucherSale::where('username', $username)
            ->where('status', 'completed')
            ->exists();

        if ($exists) {
            return;
        }

        $cost = $profile ? (float) $profile->cost_price : 1500;
        $selling = $profile ? (float) $profile->selling_price : 3000;
        $profit = max(0, $selling - $cost);

        VoucherSale::create([
            'hotspot_user_id' => $user?->id,
            'username' => $username,
            'profile_name' => $profile ? $profile->name : 'Standard',
            'cost_price' => $cost,
            'selling_price' => $selling,
            'profit' => $profit,
            'activated_at' => $activatedAt,
            'recorded_by_user_id' => null,
            'shift_id' => $shift?->id,
            'status' => 'completed',
        ]);
    }

    /**
     * Accumulate into daily_user_usage_summaries without double-counting.
     */
    protected function accumulateDailySummary(?HotspotUser $user, string $username, string $date, int $deltaIn, int $deltaOut, int $uptimeDelta): void
    {
        $formattedDate = Carbon::parse($date)->format('Y-m-d');

        $summary = DailyUserUsageSummary::where('username', $username)
            ->whereDate('usage_date', $formattedDate)
            ->first();

        if (!$summary) {
            $summary = DailyUserUsageSummary::create([
                'username' => $username,
                'usage_date' => $formattedDate,
                'hotspot_user_id' => $user?->id,
                'total_bytes_in' => 0,
                'total_bytes_out' => 0,
                'total_bytes' => 0,
                'total_uptime_seconds' => 0,
                'session_count' => 1,
            ]);
        }

        $summary->increment('total_bytes_in', $deltaIn);
        $summary->increment('total_bytes_out', $deltaOut);
        $summary->increment('total_bytes', $deltaIn + $deltaOut);
        $summary->increment('total_uptime_seconds', $uptimeDelta);
    }
}
