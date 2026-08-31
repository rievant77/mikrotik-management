<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\HotspotProfile;
use App\Models\HotspotUser;
use App\Support\FormatHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class FupManagementService
{
    protected RouterOsService $routerOs;

    public function __construct(?RouterOsService $routerOs = null)
    {
        $this->routerOs = $routerOs ?? new RouterOsService();
    }

    /**
     * Evaluate a user's data consumption against FUP limits.
     * Throttles bandwidth if FUP threshold is exceeded.
     */
    public function evaluateUserFup(
        HotspotUser $user,
        int $deltaBytes,
        ?RouterOsService $routerOsInstance = null
    ): bool {
        $routerOs = $routerOsInstance ?? $this->routerOs;
        $profile = $user->profile;

        // Check if FUP is configured
        $fupEnabled = $user->fup_custom ? true : ($profile?->fup_enabled ?? false);
        if (!$fupEnabled) {
            return false;
        }

        $fupLimitBytes = $user->fup_custom 
            ? (int) $user->fup_limit_bytes 
            : (int) ($profile?->fup_limit_bytes ?? 0);

        $fupRateLimit = $user->fup_custom 
            ? $user->fup_rate_limit 
            : ($profile?->fup_rate_limit ?? '1M/1M');

        if ($fupLimitBytes <= 0 || empty($fupRateLimit)) {
            return false;
        }

        // Increment current cycle usage
        $newUsage = (int) $user->fup_usage_bytes + max(0, $deltaBytes);
        $user->update(['fup_usage_bytes' => $newUsage]);

        // If threshold exceeded and not yet throttled
        if ($newUsage >= $fupLimitBytes && !$user->fup_active) {
            return $this->throttleUserToFup($user, $fupRateLimit, $routerOs);
        }

        return false;
    }

    /**
     * Throttle user bandwidth to FUP rate limit on MikroTik.
     */
    public function throttleUserToFup(
        HotspotUser $user,
        string $fupRateLimit,
        ?RouterOsService $routerOsInstance = null
    ): bool {
        $routerOs = $routerOsInstance ?? $this->routerOs;
        $username = $user->username;
        $now = Carbon::now();

        try {
            // Update simple queue on RouterOS if active
            $routerOs->updateSimpleQueueRateLimit($username, $fupRateLimit);
        } catch (\Throwable $e) {
            Log::warning("FUP throttle SimpleQueue error for {$username}: " . $e->getMessage());
        }

        $user->update([
            'fup_active' => true,
            'fup_triggered_at' => $now,
        ]);

        AuditLog::create([
            'user_id' => auth()->id() ?? null,
            'action' => 'user_fup_throttled',
            'entity_type' => 'HotspotUser',
            'entity_id' => $user->id,
            'details' => "Penggunaan data {$username} mencapai batas FUP (" . FormatHelper::formatBytes($user->fup_usage_bytes, 1) . "). Kecepatan diturunkan ke {$fupRateLimit}.",
            'created_at' => $now,
        ]);

        return true;
    }

    /**
     * Restore user bandwidth to normal profile rate limit on MikroTik.
     */
    public function restoreUserFromFup(
        HotspotUser $user,
        ?RouterOsService $routerOsInstance = null
    ): bool {
        $routerOs = $routerOsInstance ?? $this->routerOs;
        $username = $user->username;
        $normalRateLimit = $user->profile?->rate_limit ?: '10M/10M';

        try {
            $routerOs->updateSimpleQueueRateLimit($username, $normalRateLimit);
        } catch (\Throwable $e) {
            Log::warning("FUP restore SimpleQueue error for {$username}: " . $e->getMessage());
        }

        $user->update([
            'fup_active' => false,
            'fup_triggered_at' => null,
        ]);

        AuditLog::create([
            'user_id' => auth()->id() ?? null,
            'action' => 'user_fup_restored',
            'entity_type' => 'HotspotUser',
            'entity_id' => $user->id,
            'details' => "Kecepatan internet {$username} telah dikembalikan normal ke {$normalRateLimit}.",
            'created_at' => Carbon::now(),
        ]);

        return true;
    }

    /**
     * Manually reset FUP usage counter and restore speed for a specific user.
     */
    public function resetUserFupManual(
        HotspotUser $user,
        ?RouterOsService $routerOsInstance = null
    ): bool {
        $routerOs = $routerOsInstance ?? $this->routerOs;

        $user->update([
            'fup_usage_bytes' => 0,
            'fup_last_reset_at' => Carbon::now(),
        ]);

        if ($user->fup_active) {
            $this->restoreUserFromFup($user, $routerOs);
        }

        return true;
    }

    /**
     * Reset FUP cycle for all eligible users (e.g. daily at 00:00 or monthly).
     */
    public function resetFupCycle(
        string $cycle = 'daily',
        ?RouterOsService $routerOsInstance = null
    ): int {
        $routerOs = $routerOsInstance ?? $this->routerOs;
        $now = Carbon::now();
        $resetCount = 0;

        $users = HotspotUser::whereHas('profile', function ($q) use ($cycle) {
            $q->where('fup_enabled', true)
              ->where('fup_reset_cycle', $cycle);
        })->orWhere(function ($q) use ($cycle) {
            $q->where('fup_custom', true);
        })->get();

        foreach ($users as $u) {
            $u->update([
                'fup_usage_bytes' => 0,
                'fup_last_reset_at' => $now,
            ]);

            if ($u->fup_active) {
                $this->restoreUserFromFup($u, $routerOs);
            }

            $resetCount++;
        }

        return $resetCount;
    }

    /**
     * Get detailed FUP progress and status metrics for display.
     */
    public function getUserFupProgress(HotspotUser $user, int $liveSessionBytes = 0): array
    {
        $profile = $user->profile;
        $fupEnabled = $user->fup_custom ? true : ($profile?->fup_enabled ?? false);

        if (!$fupEnabled) {
            return [
                'fup_enabled' => false,
                'fup_active' => false,
                'normal_rate_limit' => $profile?->rate_limit ?: 'Unlimited',
                'fup_rate_limit' => null,
                'limit_bytes' => 0,
                'limit_formatted' => null,
                'usage_bytes' => 0,
                'usage_formatted' => '0 B',
                'usage_percent' => 0,
                'reset_cycle' => null,
                'triggered_at' => null,
            ];
        }

        $limitBytes = $user->fup_custom 
            ? (int) $user->fup_limit_bytes 
            : (int) ($profile?->fup_limit_bytes ?? 0);

        $fupRateLimit = $user->fup_custom 
            ? $user->fup_rate_limit 
            : ($profile?->fup_rate_limit ?? '1M/1M');

        $normalRateLimit = $profile?->rate_limit ?: '10M/10M';
        $resetCycle = $profile?->fup_reset_cycle ?? 'daily';

        $totalUsage = (int) $user->fup_usage_bytes + max(0, $liveSessionBytes);
        $percent = ($limitBytes > 0) ? (int) round(($totalUsage / $limitBytes) * 100) : 0;

        return [
            'fup_enabled' => true,
            'fup_active' => (bool) $user->fup_active,
            'normal_rate_limit' => $normalRateLimit,
            'fup_rate_limit' => $fupRateLimit,
            'limit_bytes' => $limitBytes,
            'limit_formatted' => FormatHelper::formatBytes($limitBytes, 1),
            'usage_bytes' => $totalUsage,
            'usage_formatted' => FormatHelper::formatBytes($totalUsage, 1),
            'usage_percent' => min(100, $percent),
            'reset_cycle' => $resetCycle === 'daily' ? 'Harian (00:00)' : ($resetCycle === 'monthly' ? 'Bulanan' : 'Per Siklus'),
            'triggered_at' => $user->fup_triggered_at?->format('d M Y H:i'),
        ];
    }
}
