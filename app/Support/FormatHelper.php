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
     * Format month year in Indonesian without ext-intl.
     */
    public static function formatMonthIndo(\Carbon\CarbonInterface $date): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $m = (int) $date->format('n');
        $monthName = $months[$m] ?? $date->format('F');

        return $monthName . ' ' . $date->format('Y');
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
     * Parse RouterOS uptime string (e.g. "1w2d3h4m5s", "4h12m30s", "48m10s") into seconds.
     */
    public static function parseUptime(string $uptime): int
    {
        $seconds = 0;
        if (preg_match('/(\d+)w/', $uptime, $m)) $seconds += (int)$m[1] * 7 * 86400;
        if (preg_match('/(\d+)d/', $uptime, $m)) $seconds += (int)$m[1] * 86400;
        if (preg_match('/(\d+)h/', $uptime, $m)) $seconds += (int)$m[1] * 3600;
        if (preg_match('/(\d+)m/', $uptime, $m)) $seconds += (int)$m[1] * 60;
        if (preg_match('/(\d+)s/', $uptime, $m)) $seconds += (int)$m[1];
        return $seconds;
    }
}
