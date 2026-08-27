<?php

namespace App\Support;

class DeviceHelper
{
    /**
     * Common MAC OUI prefixes to manufacturer mappings.
     */
    protected static array $macVendors = [
        // Apple
        '00:03:93' => 'Apple', '00:05:02' => 'Apple', '00:0A:27' => 'Apple', '00:0A:95' => 'Apple',
        '00:0D:93' => 'Apple', '00:10:FA' => 'Apple', '00:11:24' => 'Apple', '00:14:51' => 'Apple',
        '00:16:CB' => 'Apple', '00:17:F2' => 'Apple', '00:19:E3' => 'Apple', '00:1B:63' => 'Apple',
        '00:1C:B3' => 'Apple', '00:1D:4F' => 'Apple', '00:1E:52' => 'Apple', '00:1E:C2' => 'Apple',
        '00:1F:5B' => 'Apple', '00:1F:F3' => 'Apple', '00:21:E9' => 'Apple', '00:22:41' => 'Apple',
        '00:23:12' => 'Apple', '00:23:32' => 'Apple', '00:23:6C' => 'Apple', '00:23:DF' => 'Apple',
        '00:24:36' => 'Apple', '00:25:00' => 'Apple', '00:25:4B' => 'Apple', '00:25:BC' => 'Apple',
        '00:26:08' => 'Apple', '00:26:4A' => 'Apple', '00:26:B0' => 'Apple', '00:26:BB' => 'Apple',
        'AC:DE:48' => 'Apple', 'B8:78:2E' => 'Apple', 'BC:54:36' => 'Apple', 'C8:69:CD' => 'Apple',
        'D0:03:4B' => 'Apple', 'D4:90:9C' => 'Apple', 'DC:2B:61' => 'Apple', 'E4:98:D6' => 'Apple',
        'F0:18:98' => 'Apple', 'F4:F9:51' => 'Apple', 'FC:18:3C' => 'Apple', 'FC:FC:48' => 'Apple',

        // Samsung
        '00:07:AB' => 'Samsung', '00:12:47' => 'Samsung', '00:15:99' => 'Samsung', '00:16:6C' => 'Samsung',
        '00:17:C9' => 'Samsung', '00:1D:25' => 'Samsung', '00:21:19' => 'Samsung', '00:23:D7' => 'Samsung',
        '08:37:3D' => 'Samsung', '14:49:E0' => 'Samsung', '18:3B:D2' => 'Samsung', '1C:66:AA' => 'Samsung',
        '24:4B:81' => 'Samsung', '2C:08:8C' => 'Samsung', '38:01:46' => 'Samsung', '48:44:F7' => 'Samsung',
        '50:C8:E5' => 'Samsung', '60:6C:66' => 'Samsung', '78:47:1D' => 'Samsung', '84:25:DB' => 'Samsung',
        '88:32:9B' => 'Samsung', '94:65:2D' => 'Samsung', 'A4:C3:F0' => 'Samsung', 'B4:07:F9' => 'Samsung',
        'C0:BD:D1' => 'Samsung', 'C8:14:79' => 'Samsung', 'D0:59:E4' => 'Samsung', 'E4:7C:F9' => 'Samsung',

        // Xiaomi
        '00:9E:C8' => 'Xiaomi', '04:B1:67' => 'Xiaomi', '0C:98:38' => 'Xiaomi', '14:F6:5A' => 'Xiaomi',
        '18:59:36' => 'Xiaomi', '28:6C:07' => 'Xiaomi', '34:80:B3' => 'Xiaomi', '3C:BD:3E' => 'Xiaomi',
        '50:8F:4C' => 'Xiaomi', '58:44:98' => 'Xiaomi', '64:09:80' => 'Xiaomi', '74:23:44' => 'Xiaomi',
        '7C:49:EB' => 'Xiaomi', '80:79:02' => 'Xiaomi', '8C:DE:52' => 'Xiaomi', '98:FA:9B' => 'Xiaomi',
        'A4:C4:94' => 'Xiaomi', 'AC:C1:EE' => 'Xiaomi', 'B0:E2:35' => 'Xiaomi', 'C4:0B:D3' => 'Xiaomi',
        'D4:97:0B' => 'Xiaomi', 'E0:CC:7A' => 'Xiaomi', 'EC:D0:9F' => 'Xiaomi', 'F4:F5:24' => 'Xiaomi',

        // Oppo / OnePlus / Realme
        '10:2A:B3' => 'Oppo', '14:7D:DA' => 'Oppo', '20:79:18' => 'Oppo', '38:71:DE' => 'Oppo',
        '40:4E:36' => 'Oppo', '48:2C:A0' => 'Oppo', '50:32:75' => 'Oppo', '70:8A:09' => 'Oppo',
        '80:EA:07' => 'Oppo', '88:40:3B' => 'Oppo', '90:B6:86' => 'Oppo', 'A8:96:75' => 'Oppo',
        'B8:37:65' => 'Oppo', 'C4:6E:7B' => 'Oppo', 'CC:08:FB' => 'Oppo', 'DC:72:9B' => 'Oppo',
        'E8:BB:A8' => 'Oppo', 'F8:E9:03' => 'Oppo', '94:87:E0' => 'Realme', 'D8:13:99' => 'Realme',

        // Vivo
        '04:D3:B0' => 'Vivo', '18:55:04' => 'Vivo', '20:3D:B2' => 'Vivo', '34:CE:00' => 'Vivo',
        '50:5B:C2' => 'Vivo', '60:92:17' => 'Vivo', '7C:1C:68' => 'Vivo', '8C:11:CB' => 'Vivo',
        'A0:86:C6' => 'Vivo', 'B4:E6:2A' => 'Vivo', 'C0:B6:58' => 'Vivo', 'D8:20:DC' => 'Vivo',

        // Transsion (Infinix, Tecno, Itel)
        '00:1E:E2' => 'Infinix/Tecno', '0C:1E:0A' => 'Infinix/Tecno', '14:96:E5' => 'Infinix/Tecno',
        '2C:59:8A' => 'Infinix/Tecno', '38:97:D6' => 'Infinix/Tecno', '48:38:64' => 'Infinix/Tecno',
        '70:3E:AC' => 'Infinix/Tecno', '98:D6:F7' => 'Infinix/Tecno', 'BC:14:01' => 'Infinix/Tecno',

        // Huawei / Honor
        '00:18:82' => 'Huawei', '00:1E:10' => 'Huawei', '00:25:9E' => 'Huawei', '04:25:4C' => 'Huawei',
        '04:F9:38' => 'Huawei', '08:19:A6' => 'Huawei', '08:70:45' => 'Huawei', '0C:37:DC' => 'Huawei',

        // PC / Laptop / Chipset Vendors
        '00:15:5D' => 'Microsoft Hyper-V', '00:50:56' => 'VMware', '08:00:27' => 'VirtualBox',
        '00:1E:67' => 'Intel', '00:21:6A' => 'Intel', '3C:F0:11' => 'Intel', '80:86:F2' => 'Intel',
        '00:26:18' => 'Asus', '04:D4:C4' => 'Asus', '10:7B:44' => 'Asus', '2C:4D:54' => 'Asus',
        '00:1E:37' => 'Dell', '18:03:73' => 'Dell', 'B8:AC:6F' => 'Dell', 'D4:81:D7' => 'Dell',
        '00:1E:0B' => 'HP', '3C:D9:2B' => 'HP', '9C:B6:54' => 'HP', 'B4:B5:2F' => 'HP',
        '00:59:07' => 'Lenovo', '08:D4:0C' => 'Lenovo', '54:E1:AD' => 'Lenovo', '80:FA:5B' => 'Lenovo',

        // Network / IoT
        '00:0C:42' => 'MikroTik', '2C:C8:1B' => 'MikroTik', '48:8F:5A' => 'MikroTik', '64:D1:54' => 'MikroTik',
        'B8:27:EB' => 'Raspberry Pi', 'DC:A6:32' => 'Raspberry Pi', 'E4:5F:01' => 'Raspberry Pi',
        '24:0A:C4' => 'Espressif (ESP32/ESP8266)', '30:AE:A4' => 'Espressif', '84:F3:EB' => 'Espressif',
        '00:1D:0F' => 'TP-Link', '14:CC:20' => 'TP-Link', '50:C7:BF' => 'TP-Link', 'E8:48:B8' => 'TP-Link',
    ];

    /**
     * Resolve device name from given hostname, comment, or MAC Address.
     * Prioritizes exact DHCP Hostname from MikroTik.
     */
    public static function resolveDeviceName(?string $hostname, ?string $macAddress, ?string $comment = null): string
    {
        // 1. If explicit hostname exists from MikroTik DHCP lease, prioritize it directly
        if (!empty($hostname) && $hostname !== '*' && strtolower($hostname) !== 'unknown') {
            return trim($hostname);
        }

        // 2. If comment contains readable name
        if (!empty($comment) && strlen($comment) > 2 && !str_starts_with($comment, '{')) {
            return trim($comment);
        }

        // 3. Resolve Vendor from MAC Address
        $vendor = self::getVendorByMac($macAddress);
        if ($vendor) {
            $shortMac = $macAddress ? substr(str_replace([':', '-'], '', $macAddress), -4) : '';
            return "{$vendor}" . ($shortMac ? " (..{$shortMac})" : ' Device');
        }

        // 4. Fallback
        return $macAddress ?: "Unknown Device";
    }

    /**
     * Get vendor name by MAC address.
     */
    public static function getVendorByMac(?string $macAddress): ?string
    {
        if (empty($macAddress)) {
            return null;
        }

        $cleanMac = strtoupper(str_replace(['-', '.', ' '], ':', trim($macAddress)));
        $prefix = substr($cleanMac, 0, 8); // e.g. "A4:C3:F0"

        if (isset(self::$macVendors[$prefix])) {
            return self::$macVendors[$prefix];
        }

        // Randomized MAC detection (Android 10+, iOS 14+ Private MAC Address)
        // Locally administered bit: if 2nd char of 1st byte is 2, 6, A, or E
        $firstByteSecondChar = substr($cleanMac, 1, 1);
        if (in_array($firstByteSecondChar, ['2', '6', 'A', 'E'])) {
            return 'Mobile (Private MAC)';
        }

        return null;
    }

    /**
     * Clean up and prettify raw hostnames (e.g. "DESKTOP-87AB12" -> "Desktop PC", "Galaxy-S21-5G" -> "Galaxy S21").
     */
    public static function cleanHostname(string $hostname): string
    {
        $h = trim($hostname);

        // Normalize dashes & underscores
        $clean = str_replace(['_', '-'], ' ', $h);

        // Specific Brand Detection
        if (stripos($h, 'iphone') !== false) {
            return 'Apple iPhone (' . $h . ')';
        }
        if (stripos($h, 'ipad') !== false) {
            return 'Apple iPad (' . $h . ')';
        }
        if (stripos($h, 'macbook') !== false) {
            return 'Apple MacBook';
        }
        if (stripos($h, 'galaxy') !== false) {
            return 'Samsung ' . ucwords($clean);
        }
        if (stripos($h, 'redmi') !== false || stripos($h, 'xiaomi') !== false || stripos($h, 'poco') !== false) {
            return 'Xiaomi ' . ucwords($clean);
        }
        if (stripos($h, 'oppo') !== false) {
            return 'OPPO ' . ucwords($clean);
        }
        if (stripos($h, 'vivo') !== false) {
            return 'Vivo ' . ucwords($clean);
        }
        if (stripos($h, 'infinix') !== false || stripos($h, 'tecno') !== false) {
            return 'Transsion ' . ucwords($clean);
        }
        if (stripos($h, 'desktop') !== false || stripos($h, 'laptop') !== false || stripos($h, 'pc') !== false) {
            return 'Windows PC (' . $h . ')';
        }
        if (stripos($h, 'android') !== false) {
            return 'Android Device (' . $h . ')';
        }

        return $h;
    }

    /**
     * Get Device Icon Type ('phone', 'laptop', 'desktop', 'tv', 'router', 'generic')
     */
    public static function getDeviceType(?string $deviceName): string
    {
        $name = strtolower($deviceName ?? '');

        if (str_contains($name, 'phone') || str_contains($name, 'android') || str_contains($name, 'galaxy') || str_contains($name, 'redmi') || str_contains($name, 'oppo') || str_contains($name, 'vivo') || str_contains($name, 'infinix')) {
            return 'phone';
        }
        if (str_contains($name, 'laptop') || str_contains($name, 'macbook') || str_contains($name, 'thinkpad')) {
            return 'laptop';
        }
        if (str_contains($name, 'desktop') || str_contains($name, 'pc') || str_contains($name, 'windows')) {
            return 'desktop';
        }
        if (str_contains($name, 'mikrotik') || str_contains($name, 'router') || str_contains($name, 'access-point')) {
            return 'router';
        }

        return 'generic';
    }
}
