<?php

namespace App\Support;

class FormatHelper
{
    /**
     * Format bytes into human readable format without requiring ext-intl.
     */
    public static function formatBytes(float|int|null $bytes, int $precision = 1): string
    {
        $bytes = (float) ($bytes ?? 0);
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $pow = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $pow = max(0, $pow);
        
        $value = $bytes / pow(1024, $pow);

        return round($value, $precision) . ' ' . $units[$pow];
    }

    /**
     * Format rupiah currency without ext-intl.
     */
    public static function formatRupiah(float|int|null $amount): string
    {
        return 'Rp ' . number_format((float) ($amount ?? 0), 0, ',', '.');
    }

    /**
     * Format month year in Indonesian using Carbon native locale.
     */
    public static function formatMonthIndo(\Carbon\CarbonInterface $date): string
    {
        // ponytail: native Carbon translatedFormat replaces 20-line manual lookup table
        return $date->locale('id')->translatedFormat('F Y');
    }

    /**
     * Format seconds into human readable duration in Indonesian (e.g. 2 jam 15 mnt, 3 hari 4 jam).
     */
    public static function formatUptime(int|float|null $seconds): string
    {
        $seconds = (int) ($seconds ?? 0);
        if ($seconds < 60) {
            return max(1, $seconds) . ' dtk';
        }

        $days = (int) floor($seconds / 86400);
        $hours = (int) floor(($seconds % 86400) / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = $days . ' hari';
        }
        if ($hours > 0) {
            $parts[] = $hours . ' jam';
        }
        if ($minutes > 0 && $days === 0) {
            $parts[] = $minutes . ' mnt';
        }

        return implode(' ', $parts) ?: '1 mnt';
    }

    /**
     * Parse RouterOS uptime string (e.g. "1w2d3h4m5s", "4h12m30s", "48m10s", "01:30:00") into seconds.
     */
    public static function parseUptime(string|int|null $uptime): int
    {
        if (empty($uptime)) {
            return 0;
        }
        if (is_numeric($uptime)) {
            return (int) $uptime;
        }

        $uptime = trim((string) $uptime);
        $seconds = 0;

        // Check if there's a day prefix like "1d02:30:00"
        if (preg_match('/^(\d+)d\s*(.*)$/', $uptime, $dayMatch)) {
            $seconds += (int)$dayMatch[1] * 86400;
            $uptime = $dayMatch[2];
        }

        // Check standard colon-separated time "HH:MM:SS" or "MM:SS"
        if (preg_match('/^(\d+):(\d+):(\d+)$/', $uptime, $m)) {
            return $seconds + ((int)$m[1] * 3600) + ((int)$m[2] * 60) + (int)$m[3];
        }
        if (preg_match('/^(\d+):(\d+)$/', $uptime, $m)) {
            return $seconds + ((int)$m[1] * 60) + (int)$m[2];
        }

        // RouterOS w/d/h/m/s pattern
        if (preg_match('/(\d+)w/', $uptime, $m)) $seconds += (int)$m[1] * 7 * 86400;
        if (preg_match('/(\d+)d/', $uptime, $m)) $seconds += (int)$m[1] * 86400;
        if (preg_match('/(\d+)h/', $uptime, $m)) $seconds += (int)$m[1] * 3600;
        if (preg_match('/(\d+)m/', $uptime, $m)) $seconds += (int)$m[1] * 60;
        if (preg_match('/(\d+)s/', $uptime, $m)) $seconds += (int)$m[1];

        return $seconds;
    }

    /**
     * Calculate uptime progress and remaining time.
     * Returns array with:
     * - has_limit: bool
     * - used_seconds: int
     * - used_formatted: string
     * - limit_seconds: int
     * - limit_formatted: string
     * - remaining_seconds: int
     * - remaining_formatted: ?string
     * - remaining_percent: int (0 to 100)
     */
    public static function getUptimeProgress(string|int|null $uptime, ?string $limitUptime, ?string $sessionTimeLeft = null): array
    {
        $usedSeconds = self::parseUptime($uptime);

        // If session-time-left is directly provided by RouterOS active session
        if (!empty($sessionTimeLeft) && $sessionTimeLeft !== '0s') {
            $remainingSeconds = self::parseUptime($sessionTimeLeft);
            $totalLimitSeconds = $usedSeconds + $remainingSeconds;
            $remainingPercent = $totalLimitSeconds > 0 ? (int) round(($remainingSeconds / $totalLimitSeconds) * 100) : 0;

            return [
                'has_limit' => true,
                'used_seconds' => $usedSeconds,
                'used_formatted' => self::formatUptime($usedSeconds),
                'limit_seconds' => $totalLimitSeconds,
                'limit_formatted' => self::formatUptime($totalLimitSeconds),
                'remaining_seconds' => $remainingSeconds,
                'remaining_formatted' => self::formatUptime($remainingSeconds),
                'remaining_percent' => max(0, min(100, $remainingPercent)),
            ];
        }

        $parsedLimit = self::parseValidityToRouterTime($limitUptime);
        if (empty($parsedLimit)) {
            return [
                'has_limit' => false,
                'used_seconds' => $usedSeconds,
                'used_formatted' => self::formatUptime($usedSeconds),
                'limit_seconds' => 0,
                'limit_formatted' => 'Unlimited',
                'remaining_seconds' => 0,
                'remaining_formatted' => null,
                'remaining_percent' => 100,
            ];
        }

        $limitSeconds = self::parseUptime($parsedLimit);
        if ($limitSeconds <= 0) {
            return [
                'has_limit' => false,
                'used_seconds' => $usedSeconds,
                'used_formatted' => self::formatUptime($usedSeconds),
                'limit_seconds' => 0,
                'limit_formatted' => 'Unlimited',
                'remaining_seconds' => 0,
                'remaining_formatted' => null,
                'remaining_percent' => 100,
            ];
        }

        $remainingSeconds = max(0, $limitSeconds - $usedSeconds);
        $remainingPercent = (int) round(($remainingSeconds / $limitSeconds) * 100);

        return [
            'has_limit' => true,
            'used_seconds' => $usedSeconds,
            'used_formatted' => self::formatUptime($usedSeconds),
            'limit_seconds' => $limitSeconds,
            'limit_formatted' => self::formatUptime($limitSeconds),
            'remaining_seconds' => $remainingSeconds,
            'remaining_formatted' => self::formatUptime($remainingSeconds),
            'remaining_percent' => max(0, min(100, $remainingPercent)),
        ];
    }

    /**
     * Convert human validity string (e.g. "3 Jam", "1 Hari", "30 Hari", "30 Menit", "1 Bulan")
     * into standard RouterOS limit-uptime format (e.g. "3h", "1d", "30d", "30m").
     */
    public static function parseValidityToRouterTime(?string $validity): ?string
    {
        if (empty($validity)) {
            return null;
        }

        $v = trim(strtolower($validity));

        // Unlimited / Tanpa batas handling
        if ($v === '0' || $v === 'unlimited' || $v === 'tanpa batas' || $v === 'selamanya' || $v === 'none' || $v === 'unlimit') {
            return null;
        }

        // Direct RouterOS formats: 3h, 24h, 1d, 30d, 30m, 1w, 03:00:00
        if (preg_match('/^(\d+)([h|d|m|w|s])$/', $v)) {
            return $v;
        }
        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $v)) {
            return $v;
        }

        // Indonesian / English human patterns
        if (preg_match('/^(\d+)\s*(jam|hour|hours|hr|hrs)$/i', $v, $m)) {
            return $m[1] . 'h';
        }
        if (preg_match('/^(\d+)\s*(hari|day|days|d)$/i', $v, $m)) {
            return $m[1] . 'd';
        }
        if (preg_match('/^(\d+)\s*(menit|mnt|minute|minutes|min|mins)$/i', $v, $m)) {
            return $m[1] . 'm';
        }
        if (preg_match('/^(\d+)\s*(minggu|week|weeks|w)$/i', $v, $m)) {
            return $m[1] . 'w';
        }
        if (preg_match('/^(\d+)\s*(bulan|month|months)$/i', $v, $m)) {
            return ((int) $m[1] * 30) . 'd';
        }

        return $validity;
    }

    /**
     * Convert human data limit string (e.g. "500MB", "1GB", "2G", "500M")
     * into standard RouterOS limit-bytes-total format (e.g. "500M", "1G", "2G").
     */
    public static function parseBytesLimit(?string $dataLimit): ?string
    {
        if (empty($dataLimit)) {
            return null;
        }

        $v = trim(strtoupper($dataLimit));
        if ($v === '0' || $v === 'UNLIMITED' || $v === 'TANPA BATAS') {
            return null;
        }

        if (preg_match('/^(\d+)\s*(MB|M)$/i', $v, $m)) {
            return $m[1] . 'M';
        }
        if (preg_match('/^(\d+)\s*(GB|G)$/i', $v, $m)) {
            return $m[1] . 'G';
        }
        if (preg_match('/^(\d+)\s*(KB|K)$/i', $v, $m)) {
            return $m[1] . 'k';
        }
        if (preg_match('/^\d+$/', $v)) {
            return $v;
        }

        return $v;
    }

    /**
     * Parse human size string (e.g. "10 GB", "500 MB", "2G", "1024") into numeric bytes integer.
     */
    public static function parseBytes(string|int|float|null $input): int
    {
        if (empty($input)) {
            return 0;
        }
        if (is_numeric($input)) {
            return (int) $input;
        }

        $str = trim((string) $input);
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(TB|T)$/i', $str, $m)) {
            return (int) round((float) $m[1] * 1024 * 1024 * 1024 * 1024);
        }
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(GB|G)$/i', $str, $m)) {
            return (int) round((float) $m[1] * 1024 * 1024 * 1024);
        }
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(MB|M)$/i', $str, $m)) {
            return (int) round((float) $m[1] * 1024 * 1024);
        }
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(KB|K)$/i', $str, $m)) {
            return (int) round((float) $m[1] * 1024);
        }
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(B)?$/i', $str, $m)) {
            return (int) round((float) $m[1]);
        }

        return 0;
    }
}
