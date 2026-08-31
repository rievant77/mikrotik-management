<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\HotspotProfile;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use App\Models\VoucherSale;
use App\Support\FormatHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoucherExpiryService
{
    protected RouterOsService $routerOs;

    public function __construct(?RouterOsService $routerOs = null)
    {
        $this->routerOs = $routerOs ?? new RouterOsService();
    }

    /**
     * Sweep and process all expired users from both RouterOS and database.
     * 
     * @return array [ 'processed' => int, 'removed' => int, 'disabled' => int, 'errors' => array ]
     */
    public function sweepExpiredUsers(?RouterOsService $routerOsInstance = null): array
    {
        $routerOs = $routerOsInstance ?? $this->routerOs;
        $now = Carbon::now();
        $removedCount = 0;
        $disabledCount = 0;
        $processedCount = 0;
        $errors = [];

        try {
            $remoteUsers = $routerOs->getHotspotUsers();
        } catch (\Throwable $e) {
            $remoteUsers = [];
        }

        $remoteUserMap = [];
        foreach ($remoteUsers as $ru) {
            $uname = $ru['name'] ?? null;
            if ($uname) {
                $remoteUserMap[$uname] = $ru;
            }
        }

        // Fetch all local active/tracked users with profile
        $localUsers = HotspotUser::with('profile')->get();

        foreach ($localUsers as $user) {
            $username = $user->username;
            if ($username === 'default-trial') {
                continue;
            }

            $profile = $user->profile;
            $remoteUser = $remoteUserMap[$username] ?? null;

            // 1. Check if expired
            $isExpired = $this->checkIfUserIsExpired($user, $profile, $remoteUser, $now);

            if ($isExpired) {
                $processedCount++;
                try {
                    $mode = strtolower(trim((string) ($profile?->expired_mode ?? 'remove')));
                    $actionResult = $this->executeExpiredAction($user, $profile, $mode, $remoteUser, $routerOs, $now);

                    if ($actionResult === 'removed') {
                        $removedCount++;
                    } elseif ($actionResult === 'disabled') {
                        $disabledCount++;
                    }
                } catch (\Throwable $e) {
                    $errors[] = "Error expiring user {$username}: " . $e->getMessage();
                    Log::error("VoucherExpiryService error for user {$username}: " . $e->getMessage());
                }
            }
        }

        return [
            'processed' => $processedCount,
            'removed' => $removedCount,
            'disabled' => $disabledCount,
            'errors' => $errors,
        ];
    }

    /**
     * Determine if a user has expired based on Uptime Limit, Quota Bytes, or Validity Window.
     */
    public function checkIfUserIsExpired(HotspotUser $user, ?HotspotProfile $profile, ?array $remoteUser, Carbon $now): bool
    {
        // A. Check calendar validity expiration (expired_at timestamp in database)
        if ($user->expired_at && $now->greaterThanOrEqualTo($user->expired_at)) {
            return true;
        }

        // B. Check accumulated uptime vs limit-uptime from RouterOS
        if ($remoteUser) {
            $limitUptimeRaw = $remoteUser['limit-uptime'] ?? ($user->uptime_limit ?: ($profile?->validity ?: null));
            $limitUptimeSeconds = FormatHelper::parseUptime(FormatHelper::parseValidityToRouterTime($limitUptimeRaw));
            
            $usedUptimeSeconds = FormatHelper::parseUptime($remoteUser['uptime'] ?? '0s');

            if ($limitUptimeSeconds > 0 && $usedUptimeSeconds >= $limitUptimeSeconds) {
                return true;
            }

            // C. Check data limit bytes vs limit-bytes-total from RouterOS
            $limitBytesRaw = $remoteUser['limit-bytes-total'] ?? null;
            if ($limitBytesRaw) {
                $limitBytes = (int) $limitBytesRaw;
                $usedBytes = ((int) ($remoteUser['bytes-in'] ?? 0)) + ((int) ($remoteUser['bytes-out'] ?? 0));
                if ($limitBytes > 0 && $usedBytes >= $limitBytes) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Execute the configured expired mode action (Remove, Notice, Remove & Record).
     */
    protected function executeExpiredAction(
        HotspotUser $user,
        ?HotspotProfile $profile,
        string $mode,
        ?array $remoteUser,
        RouterOsService $routerOs,
        Carbon $now
    ): string {
        $username = $user->username;

        // Ensure sales revenue is completed & recorded before removal
        $this->ensureVoucherSaleRecorded($user, $profile, $now);

        if ($mode === 'notice') {
            // NOTICE MODE: Disable on router & local DB, disconnect active session
            try {
                $routerOs->setHotspotUserStatus($username, false);
                $routerOs->disconnectHotspotUser($username);
            } catch (\Throwable $e) {}

            $user->update(['is_active' => false]);

            HotspotSession::where('username', $username)->where('status', 'active')->update([
                'status' => 'ended',
                'ended_at' => $now,
                'current_rx_bps' => 0,
                'current_tx_bps' => 0,
            ]);

            AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'action' => 'voucher_auto_expired_notice',
                'entity_type' => 'HotspotUser',
                'entity_id' => $user->id,
                'details' => "Voucher {$username} telah kedaluwarsa dan dinonaktifkan (Notice Mode).",
                'created_at' => $now,
            ]);

            return 'disabled';
        }

        // REMOVE or REMOVE & RECORD MODE: Remove from RouterOS & Delete from DB
        try {
            $routerOs->removeHotspotUser($username);
            $routerOs->disconnectHotspotUser($username);
        } catch (\Throwable $e) {}

        HotspotSession::where('username', $username)->where('status', 'active')->update([
            'status' => 'ended',
            'ended_at' => $now,
            'current_rx_bps' => 0,
            'current_tx_bps' => 0,
        ]);

        $userId = $user->id;
        $user->delete();

        AuditLog::create([
            'user_id' => auth()->id() ?? null,
            'action' => 'voucher_auto_expired_removed',
            'entity_type' => 'HotspotUser',
            'entity_id' => $userId,
            'details' => "Voucher {$username} telah kedaluwarsa dan dihapus dari sistem (Remove Mode).",
            'created_at' => $now,
        ]);

        return 'removed';
    }

    /**
     * Ensure sales accounting record exists for this voucher before it is deleted.
     */
    protected function ensureVoucherSaleRecorded(HotspotUser $user, ?HotspotProfile $profile, Carbon $now): void
    {
        $username = $user->username;

        $exists = VoucherSale::where('username', $username)->exists();
        if ($exists) {
            return;
        }

        $cost = $profile ? (float) $profile->cost_price : 0;
        $selling = $profile ? (float) $profile->selling_price : 3000;
        $profit = max(0, $selling - $cost);

        VoucherSale::create([
            'hotspot_user_id' => $user->id,
            'username' => $username,
            'profile_name' => $profile ? $profile->name : 'Standard',
            'cost_price' => $cost,
            'selling_price' => $selling,
            'profit' => $profit,
            'activated_at' => $user->created_at ?? $now,
            'recorded_by_user_id' => null,
            'status' => 'completed',
        ]);
    }
}
