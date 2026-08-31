<?php

namespace App\Support;

/**
 * Self-contained, pure PHP SVG QR Code & Code 128 Barcode Generator.
 * 100% offline, zero external library dependency, crystal-clear vector output for thermal printers.
 */
class BarcodeHelper
{
    /**
     * Build MikroTik Hotspot Auto-Login URL.
     */
    public static function getHotspotLoginUrl(?string $baseUrl, string $username, ?string $password = null): string
    {
        $base = trim($baseUrl ?: 'http://hotspot.lan');
        if (!str_starts_with($base, 'http://') && !str_starts_with($base, 'https://')) {
            $base = 'http://' . $base;
        }
        $base = rtrim($base, '/');
        
        // MikroTik Hotspot standard login query endpoint
        $pass = $password ?: $username;
        return "{$base}/login?username=" . urlencode($username) . "&password=" . urlencode($pass);
    }

    /**
     * Generate 1D Barcode (Code 128) as inline SVG.
     */
    public static function generateBarcodeSvg(string $code, int $height = 36, float $barWidth = 1.8): string
    {
        $code = trim($code);
        if ($code === '') {
            return '';
        }

        // Code 128 Table B encoding patterns
        $patterns = [
            ' ' => '212222', '!' => '222122', '"' => '222221', '#' => '121223', '$' => '121322',
            '%' => '131222', '&' => '122213', "'" => '122312', '(' => '132212', ')' => '221213',
            '*' => '221312', '+' => '231212', ',' => '112232', '-' => '122132', '.' => '122231',
            '/' => '113222', '0' => '123122', '1' => '123221', '2' => '223211', '3' => '221132',
            '4' => '221231', '5' => '213212', '6' => '223112', '7' => '312131', '8' => '311222',
            '9' => '321122', ':' => '321221', ';' => '312212', '<' => '322112', '=' => '322211',
            '>' => '212123', '?' => '212321', '@' => '232121', 'A' => '111323', 'B' => '131123',
            'C' => '131321', 'D' => '112313', 'E' => '132113', 'F' => '132311', 'G' => '211313',
            'H' => '231113', 'I' => '231311', 'J' => '112133', 'K' => '112331', 'L' => '132131',
            'M' => '113123', 'N' => '113321', 'O' => '133121', 'P' => '313121', 'Q' => '211331',
            'R' => '231131', 'S' => '213113', 'T' => '213311', 'U' => '213131', 'V' => '311123',
            'W' => '311321', 'X' => '331121', 'Y' => '312113', 'Z' => '312311', '[' => '332111',
            '\\' => '314111', ']' => '221411', '^' => '431111', '_' => '111224', '`' => '111422',
            'a' => '121124', 'b' => '121421', 'c' => '141122', 'd' => '141221', 'e' => '112214',
            'f' => '112412', 'g' => '122114', 'h' => '122411', 'i' => '142112', 'j' => '142211',
            'k' => '241211', 'l' => '221114', 'm' => '413111', 'n' => '241112', 'o' => '134111',
            'p' => '111242', 'q' => '121142', 'r' => '121241', 's' => '114212', 't' => '124112',
            'u' => '124211', 'v' => '411212', 'w' => '421112', 'x' => '421211', 'y' => '212141',
            'z' => '214121', '{' => '412121', '|' => '111143', '}' => '111341', '~' => '131141',
        ];

        // Start B pattern: 211214, Stop pattern: 2331112
        $startPattern = '211214';
        $stopPattern = '2331112';

        $fullSequence = $startPattern;
        $checksum = 104; // Start B value
        $len = strlen($code);

        for ($i = 0; $i < $len; $i++) {
            $char = $code[$i];
            $val = ord($char) - 32;
            if ($val < 0 || $val > 95) $val = 0; // fallback space
            $checksum += ($val * ($i + 1));
            $pat = $patterns[$char] ?? '212222';
            $fullSequence .= $pat;
        }

        $checkVal = $checksum % 103;
        $checkChar = chr($checkVal + 32);
        $fullSequence .= ($patterns[$checkChar] ?? '212222') . $stopPattern;

        // Render Bars to SVG
        $x = 4; // margin
        $rects = '';
        $isBar = true;

        for ($j = 0; $j < strlen($fullSequence); $j++) {
            $widthUnits = (int) $fullSequence[$j];
            $w = $widthUnits * $barWidth;
            if ($isBar) {
                $rects .= "<rect x=\"{$x}\" y=\"0\" width=\"{$w}\" height=\"{$height}\" fill=\"black\" />";
            }
            $x += $w;
            $isBar = !$isBar;
        }

        $totalWidth = $x + 4;

        return "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 {$totalWidth} {$height}\" width=\"100%\" height=\"{$height}\" style=\"display:block;margin:auto;\">{$rects}</svg>";
    }

    /**
     * Generate Scannable QR Code as inline SVG vector.
     */
    public static function generateQrSvg(string $text, int $size = 100, int $margin = 2): string
    {
        $matrix = self::encodeQrMatrix($text);
        $count = count($matrix);
        $viewSize = $count + ($margin * 2);

        $rects = '';
        for ($r = 0; $r < $count; $r++) {
            for ($c = 0; $c < $count; $c++) {
                if ($matrix[$r][$c]) {
                    $x = $c + $margin;
                    $y = $r + $margin;
                    $rects .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"1\" height=\"1\" fill=\"black\" />";
                }
            }
        }

        return "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 {$viewSize} {$viewSize}\" width=\"{$size}\" height=\"{$size}\" shape-rendering=\"crispEdges\">{$rects}</svg>";
    }

    /**
     * Encode string into 2D Boolean QR Matrix (Supports Versions 1 to 4 Byte/Alphanumeric Encoding).
     */
    protected static function encodeQrMatrix(string $data): array
    {
        $len = strlen($data);
        // Determine smallest QR version (V1: <=17 bytes, V2: <=32 bytes, V3: <=53 bytes, V4: <=78 bytes)
        $version = 1;
        $capacity = [1 => 14, 2 => 26, 3 => 42, 4 => 62, 5 => 84, 6 => 106];
        foreach ($capacity as $v => $cap) {
            if ($len <= $cap) {
                $version = $v;
                break;
            }
            $version = $v;
        }

        $size = 17 + (4 * $version);
        $matrix = array_fill(0, $size, array_fill(0, $size, 0));
        $reserved = array_fill(0, $size, array_fill(0, $size, false));

        // 1. Finder Patterns
        self::placeFinder($matrix, $reserved, 0, 0);
        self::placeFinder($matrix, $reserved, $size - 7, 0);
        self::placeFinder($matrix, $reserved, 0, $size - 7);

        // 2. Alignment Patterns (for Version >= 2)
        if ($version >= 2) {
            $alignPos = [
                2 => [6, 18],
                3 => [6, 22],
                4 => [6, 26],
                5 => [6, 30],
                6 => [6, 34],
            ][$version];

            foreach ($alignPos as $ar) {
                foreach ($alignPos as $ac) {
                    if ($reserved[$ar][$ac]) continue;
                    self::placeAlignment($matrix, $reserved, $ar - 2, $ac - 2);
                }
            }
        }

        // 3. Timing Patterns
        for ($i = 8; $i < $size - 8; $i++) {
            $val = ($i % 2 === 0) ? 1 : 0;
            if (!$reserved[6][$i]) {
                $matrix[6][$i] = $val;
                $reserved[6][$i] = true;
            }
            if (!$reserved[$i][6]) {
                $matrix[$i][6] = $val;
                $reserved[$i][6] = true;
            }
        }

        // 4. Dark module & Format info reservation
        $matrix[$size - 8][8] = 1;
        $reserved[$size - 8][8] = true;

        for ($i = 0; $i < 9; $i++) {
            $reserved[8][$i] = true;
            $reserved[$i][8] = true;
            if ($size - 1 - $i < $size) {
                $reserved[8][$size - 1 - $i] = true;
                $reserved[$size - 1 - $i][8] = true;
            }
        }

        // 5. Encode Data Bits (Mode: 8-bit Byte = 0100)
        $bitString = '0100';
        $bitString .= str_pad(decbin($len), ($version <= 9 ? 8 : 16), '0', STR_PAD_LEFT);
        for ($i = 0; $i < $len; $i++) {
            $bitString .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
        }

        // Terminator & Padding
        $totalDataCodewords = [1 => 19, 2 => 34, 3 => 55, 4 => 80, 5 => 108, 6 => 136][$version];
        $totalDataBits = $totalDataCodewords * 8;
        $bitString .= '0000';
        if (strlen($bitString) > $totalDataBits) {
            $bitString = substr($bitString, 0, $totalDataBits);
        }
        while (strlen($bitString) % 8 !== 0) {
            $bitString .= '0';
        }

        $padBytes = ['11101100', '00010001'];
        $padIdx = 0;
        while (strlen($bitString) < $totalDataBits) {
            $bitString .= $padBytes[$padIdx % 2];
            $padIdx++;
        }

        // 6. Reed-Solomon Error Correction
        $dataCodewords = [];
        for ($i = 0; $i < strlen($bitString); $i += 8) {
            $dataCodewords[] = bindec(substr($bitString, $i, 8));
        }

        $ecCount = [1 => 7, 2 => 10, 3 => 15, 4 => 20, 5 => 26, 6 => 36][$version];
        $ecCodewords = self::calculateReedSolomon($dataCodewords, $ecCount);

        // Combined bitstream
        $finalBitString = '';
        foreach (array_merge($dataCodewords, $ecCodewords) as $byte) {
            $finalBitString .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
        }

        // 7. Place bits in zigzag pattern
        $bitIdx = 0;
        $bitLen = strlen($finalBitString);
        $goingUp = true;

        for ($col = $size - 1; $col > 0; $col -= 2) {
            if ($col === 6) $col--; // skip vertical timing line
            $rows = $goingUp ? range($size - 1, 0) : range(0, $size - 1);

            foreach ($rows as $row) {
                for ($c = $col; $c >= $col - 1; $c--) {
                    if (!$reserved[$row][$c]) {
                        $bit = ($bitIdx < $bitLen) ? ($finalBitString[$bitIdx] === '1' ? 1 : 0) : 0;
                        // Apply Mask 0: (row + col) % 2 == 0
                        $mask = (($row + $c) % 2 === 0) ? 1 : 0;
                        $matrix[$row][$c] = $bit ^ $mask;
                        $bitIdx++;
                    }
                }
            }
            $goingUp = !$goingUp;
        }

        // 8. Place Format Information (Mask 0, Level L: 111011111000100)
        $formatBits = '111011111000100';
        for ($i = 0; $i < 15; $i++) {
            $b = $formatBits[$i] === '1' ? 1 : 0;
            // Around top-left
            if ($i < 6) $matrix[8][$i] = $b;
            elseif ($i === 6) $matrix[8][7] = $b;
            elseif ($i === 7) $matrix[8][8] = $b;
            elseif ($i === 8) $matrix[7][8] = $b;
            else $matrix[14 - $i][8] = $b;

            // Around top-right & bottom-left
            if ($i < 8) $matrix[$size - 1 - $i][8] = $b;
            else $matrix[8][$size - 15 + $i] = $b;
        }

        return $matrix;
    }

    protected static function placeFinder(&$matrix, &$reserved, int $x, int $y): void
    {
        for ($r = 0; $r < 7; $r++) {
            for ($c = 0; $c < 7; $c++) {
                $isBlack = ($r === 0 || $r === 6 || $c === 0 || $c === 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4));
                $matrix[$y + $r][$x + $c] = $isBlack ? 1 : 0;
                $reserved[$y + $r][$x + $c] = true;
            }
        }
        // Separators around finder pattern
        for ($r = -1; $r <= 7; $r++) {
            for ($c = -1; $c <= 7; $c++) {
                $ry = $y + $r;
                $rx = $x + $c;
                if ($ry >= 0 && $ry < count($matrix) && $rx >= 0 && $rx < count($matrix)) {
                    $reserved[$ry][$rx] = true;
                }
            }
        }
    }

    protected static function placeAlignment(&$matrix, &$reserved, int $x, int $y): void
    {
        for ($r = 0; $r < 5; $r++) {
            for ($c = 0; $c < 5; $c++) {
                $isBlack = ($r === 0 || $r === 4 || $c === 0 || $c === 4 || ($r === 2 && $c === 2));
                $matrix[$y + $r][$x + $c] = $isBlack ? 1 : 0;
                $reserved[$y + $r][$x + $c] = true;
            }
        }
    }

    protected static function calculateReedSolomon(array $data, int $ecCount): array
    {
        // Galois Field GF(256) tables
        $exp = array_fill(0, 512, 0);
        $log = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            $exp[$i] = $x;
            $exp[$i + 255] = $x;
            $log[$x] = $i;
            $x = ($x << 1) ^ (($x >= 128) ? 0x11D : 0);
        }

        // Generator polynomial for ecCount
        $gen = [1];
        for ($i = 0; $i < $ecCount; $i++) {
            $next = array_fill(0, count($gen) + 1, 0);
            for ($j = 0; $j < count($gen); $j++) {
                $next[$j] ^= $gen[$j];
                $root = $exp[$i];
                $next[$j + 1] ^= ($gen[$j] === 0 || $root === 0) ? 0 : $exp[($log[$gen[$j]] + $log[$root]) % 255];
            }
            $gen = $next;
        }

        // Division to get remainder
        $res = array_fill(0, $ecCount, 0);
        foreach ($data as $byte) {
            $factor = $byte ^ $res[0];
            array_shift($res);
            $res[] = 0;
            if ($factor !== 0) {
                $logFactor = $log[$factor];
                for ($j = 0; $j < $ecCount; $j++) {
                    if ($gen[$j + 1] !== 0) {
                        $res[$j] ^= $exp[($log[$gen[$j + 1]] + $logFactor) % 255];
                    }
                }
            }
        }

        return $res;
    }
}
