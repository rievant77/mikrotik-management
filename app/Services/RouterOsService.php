<?php

namespace App\Services;

use App\Models\RouterSetting;
use Exception;
use Illuminate\Support\Facades\Log;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

class RouterOsService
{
    protected ?Client $client = null;
    protected ?RouterSetting $setting = null;

    public function __construct(?RouterSetting $setting = null)
    {
        $this->setting = $setting ?? RouterSetting::where('is_active', true)->first();
    }

    public function getClient(): ?Client
    {
        if ($this->client) {
            return $this->client;
        }

        if (!$this->setting || !$this->setting->host) {
            return null;
        }

        try {
            $config = new Config([
                'host' => $this->setting->host,
                'user' => $this->setting->username,
                'pass' => $this->setting->password,
                'port' => (int) ($this->setting->api_port ?: 8728),
                'ssl' => (bool) $this->setting->use_ssl,
                'timeout' => (int) (($this->setting->connection_timeout_ms ?: 3000) / 1000),
            ]);

            $this->client = new Client($config);
            return $this->client;
        } catch (Exception $e) {
            Log::warning("RouterOS Connection Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Test connection and retrieve identity and system resource info.
     */
    public function testConnection(?RouterSetting $customSetting = null): array
    {
        $setting = $customSetting ?? $this->setting;
        if (!$setting) {
            return [
                'success' => false,
                'message' => 'Konfigurasi router belum disimpan.',
                'latency_ms' => 0,
            ];
        }

        $startTime = microtime(true);

        try {
            $config = new Config([
                'host' => $setting->host,
                'user' => $setting->username,
                'pass' => $setting->password,
                'port' => (int) ($setting->api_port ?: 8728),
                'ssl' => (bool) $setting->use_ssl,
                'timeout' => (int) (($setting->connection_timeout_ms ?: 3000) / 1000),
            ]);

            $client = new Client($config);

            // Read identity
            $identityQuery = new Query('/system/identity/print');
            $identityRes = $client->query($identityQuery)->read();
            $identity = $identityRes[0]['name'] ?? 'MikroTik';

            // Read resource
            $resourceQuery = new Query('/system/resource/print');
            $resourceRes = $client->query($resourceQuery)->read();
            $resource = $resourceRes[0] ?? [];

            $latency = round((microtime(true) - $startTime) * 1000, 1);

            // Update router status in DB
            $setting->update([
                'last_successful_poll_at' => now(),
                'last_error_at' => null,
                'last_error_message' => null,
            ]);

            return [
                'success' => true,
                'identity' => $identity,
                'version' => $resource['version'] ?? '7.x',
                'board_name' => $resource['board-name'] ?? 'RouterBoard',
                'cpu_load' => $resource['cpu-load'] ?? 0,
                'free_memory' => isset($resource['free-memory']) ? round($resource['free-memory'] / 1024 / 1024, 1) . ' MB' : '0 MB',
                'uptime' => $resource['uptime'] ?? '0s',
                'latency_ms' => $latency,
            ];
        } catch (Exception $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 1);

            $setting->update([
                'last_error_at' => now(),
                'last_error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke router: ' . $e->getMessage(),
                'latency_ms' => $latency,
            ];
        }
    }

    /**
     * Get list of simple queues from MikroTik.
     */
    public function getSimpleQueues(): array
    {
        $client = $this->getClient();
        if (!$client) {
            return [];
        }

        try {
            $query = new Query('/queue/simple/print');
            return $client->query($query)->read();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get list of active hotspot sessions from MikroTik with live calculated transfer rates.
     */
    public function getActiveHotspotSessions(): array
    {
        $client = $this->getClient();
        if (!$client) {
            return [];
        }

        try {
            $query = new Query('/ip/hotspot/active/print');
            $sessions = $client->query($query)->read();

            // Fetch simple queues if any to get instant kernel rates
            $queues = $this->getSimpleQueues();
            $queueRateMap = [];
            foreach ($queues as $q) {
                $targetIp = explode('/', $q['target'] ?? '')[0];
                $rateStr = $q['rate'] ?? '0/0';
                $rates = explode('/', $rateStr);
                $txBps = (int) ($rates[0] ?? 0); // Upload
                $rxBps = (int) ($rates[1] ?? 0); // Download

                if ($targetIp) {
                    $queueRateMap[$targetIp] = ['rx' => $rxBps, 'tx' => $txBps];
                }
                if (!empty($q['name'])) {
                    $cleanName = trim($q['name'], '<>');
                    $cleanName = str_replace('hotspot-', '', $cleanName);
                    $queueRateMap[$cleanName] = ['rx' => $rxBps, 'tx' => $txBps];
                }
            }

            $now = microtime(true);
            $cacheKey = 'routeros_active_sessions_rates';
            $previous = \Illuminate\Support\Facades\Cache::get($cacheKey, []);
            $currentRates = [];

            foreach ($sessions as &$s) {
                $user = $s['user'] ?? '';
                $mac = str_replace(':', '', strtolower($s['mac-address'] ?? ''));
                $ip = $s['address'] ?? '';
                $key = $user . '_' . $mac . '_' . $ip;

                $bytesIn = (int) ($s['bytes-in'] ?? 0);
                $bytesOut = (int) ($s['bytes-out'] ?? 0);

                // 1. Check if direct simple queue rate exists for this user/ip
                $qRate = $queueRateMap[$ip] ?? ($queueRateMap[$user] ?? null);
                $kernelRx = $qRate ? $qRate['rx'] : 0;
                $kernelTx = $qRate ? $qRate['tx'] : 0;

                // 2. Compute delta rate from byte counters
                $deltaRx = 0;
                $deltaTx = 0;
                if (isset($previous[$key])) {
                    $prev = $previous[$key];
                    $timeDelta = $now - $prev['time'];

                    if ($timeDelta >= 0.2 && $timeDelta <= 15.0) {
                        $deltaIn = max(0, $bytesIn - $prev['bytes_in']);
                        $deltaOut = max(0, $bytesOut - $prev['bytes_out']);

                        $deltaRx = (int) round(($deltaOut * 8) / $timeDelta);
                        $deltaTx = (int) round(($deltaIn * 8) / $timeDelta);
                    }
                }

                // Choose maximum of kernel queue rate or delta calculation
                $s['rx-rate'] = max($kernelRx, $deltaRx);
                $s['tx-rate'] = max($kernelTx, $deltaTx);

                $currentRates[$key] = [
                    'bytes_in' => $bytesIn,
                    'bytes_out' => $bytesOut,
                    'time' => $now,
                ];
            }

            \Illuminate\Support\Facades\Cache::put($cacheKey, $currentRates, 30);

            return $sessions;
        } catch (Exception $e) {
            Log::error("RouterOS getActiveHotspotSessions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get live interface monitor-traffic (WAN/LAN bandwidth throughput).
     */
    public function getInterfaceTraffic(?string $interface = null): array
    {
        $client = $this->getClient();
        if (!$client) {
            return ['rx_bps' => 0, 'tx_bps' => 0, 'rx_mbps' => 0, 'tx_mbps' => 0];
        }

        try {
            // If interface not specified, find the first running WAN or ether interface
            if (!$interface) {
                $ifQuery = new Query('/interface/print');
                $interfaces = $client->query($ifQuery)->read();
                $firstIf = collect($interfaces)->firstWhere('running', 'true') ?? ($interfaces[0] ?? null);
                $interface = $firstIf['name'] ?? 'ether1';
            }

            $query = (new Query('/interface/monitor-traffic'))
                ->equal('interface', $interface)
                ->equal('once', '');

            $res = $client->query($query)->read();
            $traffic = $res[0] ?? [];

            $rxBps = (int) ($traffic['rx-bits-per-second'] ?? 0);
            $txBps = (int) ($traffic['tx-bits-per-second'] ?? 0);

            return [
                'interface' => $interface,
                'rx_bps' => $rxBps,
                'tx_bps' => $txBps,
                'rx_mbps' => round($rxBps / 1000000, 2),
                'tx_mbps' => round($txBps / 1000000, 2),
                'rx_formatted' => \App\Support\FormatHelper::formatBytes($rxBps / 8) . '/s',
                'tx_formatted' => \App\Support\FormatHelper::formatBytes($txBps / 8) . '/s',
            ];
        } catch (Exception $e) {
            Log::warning("RouterOS getInterfaceTraffic error: " . $e->getMessage());
            return ['rx_bps' => 0, 'tx_bps' => 0, 'rx_mbps' => 0, 'tx_mbps' => 0];
        }
    }

    /**
     * Get live router resource stats (CPU, Memory, Uptime, etc.)
     */
    public function getSystemResource(): array
    {
        $client = $this->getClient();
        if (!$client) {
            return [];
        }

        try {
            $query = new Query('/system/resource/print');
            $res = $client->query($query)->read();
            return $res[0] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get list of DHCP leases from MikroTik (contains host-name, MAC, IP, comment).
     */
    public function getDhcpLeases(): array
    {
        $client = $this->getClient();
        if (!$client) {
            return [];
        }

        try {
            $query = new Query('/ip/dhcp-server/lease/print');
            return $client->query($query)->read();
        } catch (Exception $e) {
            Log::warning("RouterOS getDhcpLeases error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get list of Hotspot Hosts from MikroTik (contains host table).
     */
    public function getHotspotHosts(): array
    {
        $client = $this->getClient();
        if (!$client) {
            return [];
        }

        try {
            $query = new Query('/ip/hotspot/host/print');
            return $client->query($query)->read();
        } catch (Exception $e) {
            Log::warning("RouterOS getHotspotHosts error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get list of registered hotspot users from MikroTik.
     */
    public function getHotspotUsers(): array
    {
        $client = $this->getClient();
        if (!$client) {
            return [];
        }

        try {
            $query = new Query('/ip/hotspot/user/print');
            return $client->query($query)->read();
        } catch (Exception $e) {
            Log::error("RouterOS getHotspotUsers error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get list of hotspot profiles from MikroTik.
     */
    public function getHotspotProfiles(): array
    {
        $client = $this->getClient();
        if (!$client) {
            return [];
        }

        try {
            $query = new Query('/ip/hotspot/user/profile/print');
            return $client->query($query)->read();
        } catch (Exception $e) {
            Log::error("RouterOS getHotspotProfiles error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Remove / Disconnect active hotspot session by username or ID.
     */
    public function disconnectHotspotUser(string $username): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $findQuery = (new Query('/ip/hotspot/active/print'))->where('user', $username);
            $actives = $client->query($findQuery)->read();

            foreach ($actives as $session) {
                if (isset($session['.id'])) {
                    $removeQuery = (new Query('/ip/hotspot/active/remove'))->equal('.id', $session['.id']);
                    $client->query($removeQuery)->read();
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error("RouterOS disconnectHotspotUser error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get list of Hotspot Servers from MikroTik (/ip/hotspot/print).
     */
    public function getHotspotServers(): array
    {
        $client = $this->getClient();
        if (!$client) {
            return [];
        }

        try {
            $query = new Query('/ip/hotspot/print');
            return $client->query($query)->read();
        } catch (Exception $e) {
            Log::warning("RouterOS getHotspotServers error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Add user to MikroTik Hotspot.
     */
    public function addHotspotUser(
        string $username,
        string $password,
        ?string $profile = null,
        ?string $limitUptime = null,
        ?string $comment = null,
        ?string $server = null,
        ?string $limitBytesTotal = null
    ): bool {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $query = (new Query('/ip/hotspot/user/add'))
                ->equal('name', $username)
                ->equal('password', $password);

            if ($server && $server !== 'all' && $server !== 'ALL') {
                $query->equal('server', $server);
            }
            if ($profile) {
                $query->equal('profile', $profile);
            }
            if ($limitUptime) {
                $query->equal('limit-uptime', $limitUptime);
            }
            if ($limitBytesTotal) {
                $query->equal('limit-bytes-total', $limitBytesTotal);
            }
            if ($comment) {
                $query->equal('comment', $comment);
            }

            $client->query($query)->read();
            return true;
        } catch (Exception $e) {
            Log::error("RouterOS addHotspotUser error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add profile to MikroTik Hotspot.
     */
    public function addHotspotProfile(string $name, ?string $rateLimit = null, int|string|null $sharedUsers = 1): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $sharedVal = (empty($sharedUsers) || $sharedUsers === 'unlimited' || (int)$sharedUsers === 0) ? 'unlimited' : (string)$sharedUsers;

            $query = (new Query('/ip/hotspot/user/profile/add'))
                ->equal('name', $name)
                ->equal('shared-users', $sharedVal);

            if ($rateLimit) {
                $query->equal('rate-limit', $rateLimit);
            }

            $client->query($query)->read();
            return true;
        } catch (Exception $e) {
            Log::error("RouterOS addHotspotProfile error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove user from MikroTik Hotspot.
     */
    public function removeHotspotUser(string $username): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            // Also disconnect active session if currently online
            $this->disconnectHotspotUser($username);

            $findQuery = (new Query('/ip/hotspot/user/print'))->where('name', $username);
            $users = $client->query($findQuery)->read();

            foreach ($users as $u) {
                if (isset($u['.id'])) {
                    $removeQuery = (new Query('/ip/hotspot/user/remove'))->equal('.id', $u['.id']);
                    $client->query($removeQuery)->read();
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error("RouterOS removeHotspotUser error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable or Disable user on MikroTik Hotspot.
     */
    public function setHotspotUserStatus(string $username, bool $enabled): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            if (!$enabled) {
                // Kick out active session immediately if disabled
                $this->disconnectHotspotUser($username);
            }

            $findQuery = (new Query('/ip/hotspot/user/print'))->where('name', $username);
            $users = $client->query($findQuery)->read();

            foreach ($users as $u) {
                if (isset($u['.id'])) {
                    $setQuery = (new Query('/ip/hotspot/user/set'))
                        ->equal('.id', $u['.id'])
                        ->equal('disabled', $enabled ? 'no' : 'yes');
                    $client->query($setQuery)->read();
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error("RouterOS setHotspotUserStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user on MikroTik Hotspot.
     */
    public function updateHotspotUser(string $username, array $params): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $findQuery = (new Query('/ip/hotspot/user/print'))->where('name', $username);
            $users = $client->query($findQuery)->read();

            foreach ($users as $u) {
                if (isset($u['.id'])) {
                    $setQuery = (new Query('/ip/hotspot/user/set'))->equal('.id', $u['.id']);

                    if (isset($params['password'])) {
                        $setQuery->equal('password', $params['password']);
                    }
                    if (isset($params['profile'])) {
                        $setQuery->equal('profile', $params['profile']);
                    }
                    if (isset($params['uptime_limit'])) {
                        $setQuery->equal('limit-uptime', $params['uptime_limit'] ?: '0s');
                    }
                    if (isset($params['comment'])) {
                        $setQuery->equal('comment', $params['comment']);
                    }
                    if (isset($params['is_active'])) {
                        $setQuery->equal('disabled', $params['is_active'] ? 'no' : 'yes');
                    }

                    $client->query($setQuery)->read();
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error("RouterOS updateHotspotUser error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update simple queue rate limit for dynamic FUP throttling on MikroTik.
     */
    public function updateSimpleQueueRateLimit(string $targetName, string $rateLimit): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $queues = $client->query(new Query('/queue/simple/print'))->read();
            $targetUpper = strtoupper($targetName);

            foreach ($queues as $q) {
                $qName = $q['name'] ?? '';
                $qTarget = $q['target'] ?? '';
                $isMatch = ($qName === $targetName)
                    || (str_contains(strtoupper($qName), $targetUpper))
                    || ($qTarget === $targetName);

                if ($isMatch && isset($q['.id'])) {
                    $setQuery = (new Query('/queue/simple/set'))
                        ->equal('.id', $q['.id'])
                        ->equal('max-limit', $rateLimit);
                    $client->query($setQuery)->read();
                    return true;
                }
            }

            return false;
        } catch (Exception $e) {
            Log::error("RouterOS updateSimpleQueueRateLimit error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update profile on MikroTik Hotspot.
     */
    public function updateHotspotProfile(string $name, array $params): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $findQuery = (new Query('/ip/hotspot/user/profile/print'))->where('name', $name);
            $profiles = $client->query($findQuery)->read();

            foreach ($profiles as $p) {
                if (isset($p['.id'])) {
                    $setQuery = (new Query('/ip/hotspot/user/profile/set'))->equal('.id', $p['.id']);

                    if (array_key_exists('rate_limit', $params)) {
                        $setQuery->equal('rate-limit', $params['rate_limit'] ?: '');
                    }
                    if (array_key_exists('shared_users', $params)) {
                        $sUsers = $params['shared_users'];
                        $sharedVal = (empty($sUsers) || $sUsers === 'unlimited' || (int)$sUsers === 0) ? 'unlimited' : (string)$sUsers;
                        $setQuery->equal('shared-users', $sharedVal);
                    }

                    $client->query($setQuery)->read();
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error("RouterOS updateHotspotProfile error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove profile from MikroTik Hotspot.
     */
    public function removeHotspotProfile(string $name): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $findQuery = (new Query('/ip/hotspot/user/profile/print'))->where('name', $name);
            $profiles = $client->query($findQuery)->read();

            foreach ($profiles as $p) {
                if (isset($p['.id'])) {
                    $removeQuery = (new Query('/ip/hotspot/user/profile/remove'))->equal('.id', $p['.id']);
                    $client->query($removeQuery)->read();
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error("RouterOS removeHotspotProfile error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Make a DHCP lease static by MAC address.
     */
    public function makeDhcpLeaseStatic(string $macAddress, ?string $ip = null, ?string $comment = null): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $cleanMac = strtoupper(trim($macAddress));
            $leases = $this->getDhcpLeases();

            foreach ($leases as $l) {
                $leaseMac = strtoupper($l['mac-address'] ?? ($l['active-mac-address'] ?? ''));
                if ($leaseMac === $cleanMac && isset($l['.id'])) {
                    // If dynamic, make static
                    if (($l['dynamic'] ?? '') === 'true' || ($l['dynamic'] ?? false) === true) {
                        $makeStaticQuery = (new Query('/ip/dhcp-server/lease/make-static'))->equal('.id', $l['.id']);
                        $client->query($makeStaticQuery)->read();
                    }

                    if ($comment !== null) {
                        $setQuery = (new Query('/ip/dhcp-server/lease/set'))
                            ->equal('.id', $l['.id'])
                            ->equal('comment', $comment);
                        $client->query($setQuery)->read();
                    }

                    return true;
                }
            }

            // If lease does not exist, add as static lease if IP is provided
            if ($ip) {
                $addQuery = (new Query('/ip/dhcp-server/lease/add'))
                    ->equal('mac-address', $cleanMac)
                    ->equal('address', $ip)
                    ->equal('comment', $comment ?: 'Static Device');
                $client->query($addQuery)->read();
                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::error("RouterOS makeDhcpLeaseStatic error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get IP binding status for a MAC address.
     */
    public function getHotspotIpBinding(string $macAddress): ?array
    {
        $client = $this->getClient();
        if (!$client) {
            return null;
        }

        try {
            $cleanMac = strtoupper(trim($macAddress));
            $query = new Query('/ip/hotspot/ip-binding/print');
            $bindings = $client->query($query)->read();

            foreach ($bindings as $b) {
                $bMac = strtoupper($b['mac-address'] ?? '');
                if ($bMac === $cleanMac) {
                    return $b;
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error("RouterOS getHotspotIpBinding error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Set or toggle Hotspot IP Binding (bypassed, blocked, regular).
     */
    public function setHotspotIpBinding(string $macAddress, string $type = 'bypassed', ?string $comment = null): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $cleanMac = strtoupper(trim($macAddress));
            $existing = $this->getHotspotIpBinding($cleanMac);

            if ($existing && isset($existing['.id'])) {
                $setQuery = (new Query('/ip/hotspot/ip-binding/set'))
                    ->equal('.id', $existing['.id'])
                    ->equal('type', $type);

                if ($comment !== null) {
                    $setQuery->equal('comment', $comment);
                }

                $client->query($setQuery)->read();
            } else {
                $addQuery = (new Query('/ip/hotspot/ip-binding/add'))
                    ->equal('mac-address', $cleanMac)
                    ->equal('type', $type);

                if ($comment !== null) {
                    $addQuery->equal('comment', $comment);
                }

                $client->query($addQuery)->read();
            }

            return true;
        } catch (Exception $e) {
            Log::error("RouterOS setHotspotIpBinding error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove Hotspot IP Binding for a MAC address.
     */
    public function removeHotspotIpBinding(string $macAddress): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $cleanMac = strtoupper(trim($macAddress));
            $existing = $this->getHotspotIpBinding($cleanMac);

            if ($existing && isset($existing['.id'])) {
                $removeQuery = (new Query('/ip/hotspot/ip-binding/remove'))->equal('.id', $existing['.id']);
                $client->query($removeQuery)->read();
                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::error("RouterOS removeHotspotIpBinding error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update comment / alias on a DHCP Lease.
     */
    public function setDhcpLeaseComment(string $macAddress, string $comment): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $cleanMac = strtoupper(trim($macAddress));
            $leases = $this->getDhcpLeases();

            foreach ($leases as $l) {
                $leaseMac = strtoupper($l['mac-address'] ?? ($l['active-mac-address'] ?? ''));
                if ($leaseMac === $cleanMac && isset($l['.id'])) {
                    $setQuery = (new Query('/ip/dhcp-server/lease/set'))
                        ->equal('.id', $l['.id'])
                        ->equal('comment', $comment);
                    $client->query($setQuery)->read();
                    return true;
                }
            }

            return false;
        } catch (Exception $e) {
            Log::error("RouterOS setDhcpLeaseComment error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reset traffic & uptime counters on MikroTik Hotspot.
     */
    public function resetHotspotUserCounters(?string $username = null): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $query = new Query('/ip/hotspot/user/print');
            if ($username) {
                $query->where('name', $username);
            }
            $users = $client->query($query)->read();

            foreach ($users as $u) {
                if (isset($u['.id'])) {
                    $resetQuery = (new Query('/ip/hotspot/user/reset-counters'))->equal('.id', $u['.id']);
                    $client->query($resetQuery)->read();
                }
            }

            // If user active, kick from active sessions so counter resets cleanly
            $activeQuery = new Query('/ip/hotspot/active/print');
            if ($username) {
                $activeQuery->where('user', $username);
            }
            $actives = $client->query($activeQuery)->read();
            foreach ($actives as $act) {
                if (isset($act['.id'])) {
                    $kickQuery = (new Query('/ip/hotspot/active/remove'))->equal('.id', $act['.id']);
                    $client->query($kickQuery)->read();
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error("RouterOS resetHotspotUserCounters error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove hotspot users in bulk from MikroTik.
     */
    public function removeAllHotspotUsers(array $excludeUsernames = ['default']): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $users = $client->query(new Query('/ip/hotspot/user/print'))->read();

            foreach ($users as $u) {
                $name = $u['name'] ?? '';
                if (!in_array($name, $excludeUsernames) && isset($u['.id'])) {
                    $delQuery = (new Query('/ip/hotspot/user/remove'))->equal('.id', $u['.id']);
                    $client->query($delQuery)->read();
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error("RouterOS removeAllHotspotUsers error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get live traffic category stats from MikroTik Mangle Rules.
     */
    public function getTrafficCategoryMangles(): array
    {
        $client = $this->getClient();
        if (!$client) {
            return [];
        }

        try {
            $rules = $client->query(new Query('/ip/firewall/mangle/print'))->read();
            $results = [];

            foreach ($rules as $r) {
                $comment = $r['comment'] ?? '';
                if (str_starts_with($comment, '[AGY-TRAFFIC-')) {
                    $results[] = [
                        'id' => $r['.id'] ?? '',
                        'comment' => $comment,
                        'bytes' => (int) ($r['bytes'] ?? 0),
                        'packets' => (int) ($r['packets'] ?? 0),
                        'disabled' => ($r['disabled'] ?? 'false') === 'true',
                    ];
                }
            }

            return $results;
        } catch (Exception $e) {
            Log::warning("RouterOS getTrafficCategoryMangles error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Deploy non-intrusive traffic monitoring mangle rules to MikroTik using Connection Marking, QUIC UDP 443 & Address-Lists.
     */
    public function deployTrafficMangleRules(): array
    {
        $client = $this->getClient();
        if (!$client) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke router MikroTik.',
                'deployed_count' => 0,
            ];
        }

        $definitions = [
            // 1. Video Streaming
            [
                'mark' => 'agy_cm_youtube',
                'comment' => '[AGY-TRAFFIC-VIDEO] YouTube & Google Video',
                'category' => 'video',
                'address_list' => 'agy_list_youtube',
                'fqdn_domains' => ['youtube.com', 'googlevideo.com', 'ytimg.com', 'youtu.be', 'ggpht.com', 'youtubei.googleapis.com'],
                'tls_host' => '*googlevideo.com,*youtube.com,*ytimg.com,*youtu.be,*ggpht.com,*gvt1.com,*youtubei.googleapis.com',
                'quic' => true,
            ],
            [
                'mark' => 'agy_cm_tiktok',
                'comment' => '[AGY-TRAFFIC-VIDEO] TikTok Video',
                'category' => 'video',
                'address_list' => 'agy_list_tiktok',
                'fqdn_domains' => ['tiktok.com', 'tiktokcdn.com', 'byteoversea.com', 'ibytedtos.com', 'musical.ly'],
                'tls_host' => '*tiktokcdn.com,*tiktokv.com,*byteoversea.com,*musical.ly,*tiktok.com,*ibytedtos.com',
                'quic' => true,
            ],
            [
                'mark' => 'agy_cm_netflix',
                'comment' => '[AGY-TRAFFIC-VIDEO] Netflix',
                'category' => 'video',
                'address_list' => 'agy_list_netflix',
                'fqdn_domains' => ['netflix.com', 'nflxvideo.net', 'nflxext.com', 'nflximg.net'],
                'tls_host' => '*netflix.com,*nflxvideo.net,*nflxext.com,*nflximg.net',
            ],
            [
                'mark' => 'agy_cm_vidio',
                'comment' => '[AGY-TRAFFIC-VIDEO] Vidio & Local OTT',
                'category' => 'video',
                'address_list' => 'agy_list_vidio',
                'fqdn_domains' => ['vidio.com', 'rctiplus.com', 'visionplus.id', 'maxstream.tv', 'hotstar.com'],
                'tls_host' => '*vidio.com,*rctiplus.com,*visionplus.id,*maxstream.tv,*hotstar.com',
            ],

            // 2. Social Media & Messaging
            [
                'mark' => 'agy_cm_whatsapp',
                'comment' => '[AGY-TRAFFIC-SOSMED] WhatsApp & Call',
                'category' => 'social_media',
                'address_list' => 'agy_list_whatsapp',
                'fqdn_domains' => ['whatsapp.com', 'whatsapp.net'],
                'tls_host' => '*whatsapp.net,*whatsapp.com',
                'tcp_port' => '5222,5223,5228,4244',
                'udp_port' => '3478,5349',
            ],
            [
                'mark' => 'agy_cm_meta',
                'comment' => '[AGY-TRAFFIC-SOSMED] Instagram & Facebook',
                'category' => 'social_media',
                'address_list' => 'agy_list_meta',
                'fqdn_domains' => ['instagram.com', 'cdninstagram.com', 'facebook.com', 'fbcdn.net'],
                'tls_host' => '*instagram.com,*cdninstagram.com,*fbcdn.net,*facebook.com,*facebook.net',
                'quic' => true,
            ],
            [
                'mark' => 'agy_cm_telegram',
                'comment' => '[AGY-TRAFFIC-SOSMED] Telegram',
                'category' => 'social_media',
                'address_list' => 'agy_list_telegram',
                'fqdn_domains' => ['telegram.org', 't.me', 'telegram.me'],
                'tls_host' => '*telegram.org,*t.me,*telegram.me',
                'tcp_port' => '443,80,5222',
            ],
            [
                'mark' => 'agy_cm_twitter',
                'comment' => '[AGY-TRAFFIC-SOSMED] Twitter / X & Threads',
                'category' => 'social_media',
                'address_list' => 'agy_list_twitter',
                'fqdn_domains' => ['twitter.com', 'x.com', 'twimg.com', 'threads.net'],
                'tls_host' => '*twimg.com,*twitter.com,*x.com,*threads.net',
            ],

            // 3. Online Games
            [
                'mark' => 'agy_cm_mlbb',
                'comment' => '[AGY-TRAFFIC-GAMING] Mobile Legends: Bang Bang',
                'category' => 'gaming',
                'address_list' => 'agy_list_mlbb',
                'fqdn_domains' => ['mobilelegends.com', 'moonton.com'],
                'tls_host' => '*mobilelegends.com,*moonton.com,*youngjoygame.com',
                'udp_port' => '5000-5200,5500-5700,30000-30200,10003',
                'tcp_port' => '5000-5200,5500-5700,30000-30200',
            ],
            [
                'mark' => 'agy_cm_freefire',
                'comment' => '[AGY-TRAFFIC-GAMING] Free Fire & Garena',
                'category' => 'gaming',
                'address_list' => 'agy_list_freefire',
                'fqdn_domains' => ['freefiremobile.com', 'garena.com'],
                'tls_host' => '*freefiremobile.com,*garena.com,*garenanow.com',
                'udp_port' => '10000-10010,7006,39003',
                'tcp_port' => '10000-10010,7006',
            ],
            [
                'mark' => 'agy_cm_pubg',
                'comment' => '[AGY-TRAFFIC-GAMING] PUBG Mobile',
                'category' => 'gaming',
                'address_list' => 'agy_list_pubg',
                'fqdn_domains' => ['pubgmobile.com', 'proximabeta.com'],
                'tls_host' => '*pubgmobile.com,*proximabeta.com',
                'udp_port' => '17500,18081,10012,20000-20002',
                'tcp_port' => '17500,18081',
            ],
            [
                'mark' => 'agy_cm_roblox_steam',
                'comment' => '[AGY-TRAFFIC-GAMING] Roblox & Steam',
                'category' => 'gaming',
                'address_list' => 'agy_list_roblox_steam',
                'fqdn_domains' => ['roblox.com', 'steampowered.com', 'steamcommunity.com'],
                'tls_host' => '*roblox.com,*rbxcdn.com,*steampowered.com,*steamcommunity.com',
                'udp_port' => '27015-27030,27036-27037',
            ],

            // 4. Cloud & Work / Meet
            [
                'mark' => 'agy_cm_zoom_meet',
                'comment' => '[AGY-TRAFFIC-CLOUD] Zoom & Google Meet',
                'category' => 'cloud_work',
                'address_list' => 'agy_list_zoom_meet',
                'fqdn_domains' => ['zoom.us', 'zoom.com', 'meet.google.com', 'teams.microsoft.com'],
                'tls_host' => '*zoom.us,*zoom.com,*meet.google.com,*teams.microsoft.com',
                'udp_port' => '8801,8802,3478-3481',
            ],
            [
                'mark' => 'agy_cm_cloud_drive',
                'comment' => '[AGY-TRAFFIC-CLOUD] Google Drive & Cloud Storage',
                'category' => 'cloud_work',
                'address_list' => 'agy_list_drive',
                'fqdn_domains' => ['drive.google.com', 'onedrive.live.com', 'dropbox.com'],
                'tls_host' => '*drive.google.com,*onedrive.live.com,*dropbox.com,*icloud.com',
            ],

            // 5. Web Browsing & E-Commerce
            [
                'mark' => 'agy_cm_ecommerce',
                'comment' => '[AGY-TRAFFIC-BROWSING] Shopee & Tokopedia',
                'category' => 'browsing',
                'address_list' => 'agy_list_ecommerce',
                'fqdn_domains' => ['shopee.co.id', 'tokopedia.com', 'lazada.co.id', 'bukalapak.com'],
                'tls_host' => '*shopee.co.id,*shopeemobile.com,*tokopedia.com,*tokopedia.net,*lazada.co.id,*bukalapak.com',
            ],
        ];

        try {
            // 1. Clean existing AGY mangle rules to ensure clean order & no duplicates
            $existing = $client->query(new Query('/ip/firewall/mangle/print'))->read();
            foreach ($existing as $ex) {
                $c = $ex['comment'] ?? '';
                if ((str_starts_with($c, '[AGY-MARK') || str_starts_with($c, '[AGY-TRAFFIC-') || str_starts_with($c, '[AGY-ADDR')) && isset($ex['.id'])) {
                    $delQ = (new Query('/ip/firewall/mangle/remove'))->equal('.id', $ex['.id']);
                    $client->query($delQ)->read();
                }
            }

            // 2. Clean and populate FQDN Address-Lists in MikroTik
            $existingAddressList = $client->query(new Query('/ip/firewall/address-list/print'))->read();
            foreach ($existingAddressList as $al) {
                $listName = $al['list'] ?? '';
                $comment = $al['comment'] ?? '';
                if ((str_starts_with($listName, 'agy_list_') || str_starts_with($comment, '[AGY-')) && isset($al['.id'])) {
                    $delAl = (new Query('/ip/firewall/address-list/remove'))->equal('.id', $al['.id']);
                    $client->query($delAl)->read();
                }
            }

            $deployed = 0;

            // Seed static FQDN domains into address-lists
            foreach ($definitions as $item) {
                if (!empty($item['address_list']) && !empty($item['fqdn_domains'])) {
                    foreach ($item['fqdn_domains'] as $dom) {
                        $qAddList = (new Query('/ip/firewall/address-list/add'))
                            ->equal('list', $item['address_list'])
                            ->equal('address', $dom)
                            ->equal('comment', "[AGY-FQDN] {$dom}");
                        $client->query($qAddList)->read();
                        $deployed++;
                    }
                }
            }

            // 3. Add Dynamic Address-List Learning from TLS SNI
            foreach ($definitions as $item) {
                if (!empty($item['tls_host']) && !empty($item['address_list'])) {
                    $qLearn = (new Query('/ip/firewall/mangle/add'))
                        ->equal('chain', 'forward')
                        ->equal('protocol', 'tcp')
                        ->equal('tls-host', $item['tls_host'])
                        ->equal('action', 'add-dst-to-address-list')
                        ->equal('address-list', $item['address_list'])
                        ->equal('address-list-timeout', '1d')
                        ->equal('passthrough', 'yes')
                        ->equal('comment', "[AGY-ADDR-TLS] {$item['comment']}");
                    $client->query($qLearn)->read();
                    $deployed++;
                }
            }

            // 4. Add Mark-Connection Classification Rules (TLS SNI + Address-List + QUIC UDP 443 + Ports)
            foreach ($definitions as $item) {
                $markName = $item['mark'];
                $addrList = $item['address_list'] ?? null;

                // A. TLS Host matcher
                if (!empty($item['tls_host'])) {
                    $qTls = (new Query('/ip/firewall/mangle/add'))
                        ->equal('chain', 'forward')
                        ->equal('protocol', 'tcp')
                        ->equal('tls-host', $item['tls_host'])
                        ->equal('action', 'mark-connection')
                        ->equal('new-connection-mark', $markName)
                        ->equal('passthrough', 'yes')
                        ->equal('comment', "[AGY-MARK-TLS] {$item['comment']}");
                    $client->query($qTls)->read();
                    $deployed++;
                }

                // B. QUIC (HTTP/3 over UDP port 443) for modern browsers & YouTube
                if (!empty($item['quic']) && $addrList) {
                    $qQuic = (new Query('/ip/firewall/mangle/add'))
                        ->equal('chain', 'forward')
                        ->equal('protocol', 'udp')
                        ->equal('dst-port', '443')
                        ->equal('dst-address-list', $addrList)
                        ->equal('action', 'mark-connection')
                        ->equal('new-connection-mark', $markName)
                        ->equal('passthrough', 'yes')
                        ->equal('comment', "[AGY-MARK-QUIC] {$item['comment']}");
                    $client->query($qQuic)->read();
                    $deployed++;
                }

                // C. Destination Address-List (TCP)
                if ($addrList) {
                    $qDstTcp = (new Query('/ip/firewall/mangle/add'))
                        ->equal('chain', 'forward')
                        ->equal('protocol', 'tcp')
                        ->equal('dst-address-list', $addrList)
                        ->equal('action', 'mark-connection')
                        ->equal('new-connection-mark', $markName)
                        ->equal('passthrough', 'yes')
                        ->equal('comment', "[AGY-MARK-DST] {$item['comment']}");
                    $client->query($qDstTcp)->read();
                    $deployed++;
                }

                // D. UDP Port matcher (for Gaming / VoIP / Meet)
                if (!empty($item['udp_port'])) {
                    $qUdp = (new Query('/ip/firewall/mangle/add'))
                        ->equal('chain', 'forward')
                        ->equal('protocol', 'udp')
                        ->equal('dst-port', $item['udp_port'])
                        ->equal('action', 'mark-connection')
                        ->equal('new-connection-mark', $markName)
                        ->equal('passthrough', 'yes')
                        ->equal('comment', "[AGY-MARK-UDP] {$item['comment']}");
                    $client->query($qUdp)->read();
                    $deployed++;
                }

                // E. TCP Port matcher
                if (!empty($item['tcp_port'])) {
                    $qTcp = (new Query('/ip/firewall/mangle/add'))
                        ->equal('chain', 'forward')
                        ->equal('protocol', 'tcp')
                        ->equal('dst-port', $item['tcp_port'])
                        ->equal('action', 'mark-connection')
                        ->equal('new-connection-mark', $markName)
                        ->equal('passthrough', 'yes')
                        ->equal('comment', "[AGY-MARK-PORT] {$item['comment']}");
                    $client->query($qTcp)->read();
                    $deployed++;
                }
            }

            // 5. Add Accounting Counter Rules (Reads Connection-Marks to count ALL packets in stream)
            foreach ($definitions as $item) {
                $qCount = (new Query('/ip/firewall/mangle/add'))
                    ->equal('chain', 'forward')
                    ->equal('connection-mark', $item['mark'])
                    ->equal('action', 'passthrough')
                    ->equal('passthrough', 'yes')
                    ->equal('comment', $item['comment']);
                $client->query($qCount)->read();
                $deployed++;
            }

            // 6. Add Catch-All General Web Browsing Rule
            $qCatchAll = (new Query('/ip/firewall/mangle/add'))
                ->equal('chain', 'forward')
                ->equal('connection-mark', 'no-mark')
                ->equal('action', 'passthrough')
                ->equal('passthrough', 'yes')
                ->equal('comment', '[AGY-TRAFFIC-BROWSING] General Web Browsing');
            $client->query($qCatchAll)->read();
            $deployed++;

            return [
                'success' => true,
                'message' => "Berhasil memasang {$deployed} filter trafik cerdas (QUIC UDP 443 + TLS + Address-Lists) di MikroTik!",
                'deployed_count' => $deployed,
            ];
        } catch (Exception $e) {
            Log::error("RouterOS deployTrafficMangleRules error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menerapkan rule: ' . $e->getMessage(),
                'deployed_count' => 0,
            ];
        }
    }

    /**
     * Reset traffic category mangle counters in MikroTik.
     */
    public function resetTrafficCategoryCounters(): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $rules = $client->query(new Query('/ip/firewall/mangle/print'))->read();

            foreach ($rules as $r) {
                $comment = $r['comment'] ?? '';
                if ((str_starts_with($comment, '[AGY-TRAFFIC-') || str_starts_with($comment, '[AGY-MARK')) && isset($r['.id'])) {
                    $resetQ = (new Query('/ip/firewall/mangle/reset-counters'))->equal('.id', $r['.id']);
                    $client->query($resetQ)->read();
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error("RouterOS resetTrafficCategoryCounters error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch active firewall connections for a specific source IP.
     */
    public function getDeviceActiveConnections(string $ip): array
    {
        $client = $this->getClient();
        if (!$client || empty($ip) || $ip === '-') {
            return [];
        }

        try {
            // Read active connections from MikroTik without incompatible regex operators
            $query = new Query('/ip/firewall/connection/print');
            $connections = $client->query($query)->read();
            $results = [];

            foreach ($connections as $c) {
                $src = $c['src-address'] ?? '';
                // Match source IP
                if (str_starts_with($src, $ip . ':') || str_starts_with($src, $ip . ' ') || $src === $ip) {
                    $results[] = [
                        'id' => $c['.id'] ?? '',
                        'protocol' => strtolower($c['protocol'] ?? 'tcp'),
                        'src_address' => $src,
                        'dst_address' => $c['dst-address'] ?? '',
                        'reply_src_address' => $c['reply-src-address'] ?? '',
                        'reply_dst_address' => $c['reply-dst-address'] ?? '',
                        'tcp_state' => strtolower($c['tcp-state'] ?? ($c['protocol'] ?? 'active')),
                        'orig_bytes' => (int) ($c['orig-bytes'] ?? 0),
                        'repl_bytes' => (int) ($c['repl-bytes'] ?? 0),
                        'timeout' => $c['timeout'] ?? '',
                    ];
                }
            }

            return $results;
        } catch (Exception $e) {
            Log::warning("RouterOS getDeviceActiveConnections error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch DNS cache map from MikroTik (IP => Domain Name lookup).
     */
    public function getDnsCacheMap(): array
    {
        $client = $this->getClient();
        if (!$client) {
            return [];
        }

        try {
            $cache = $client->query(new Query('/ip/dns/cache/print'))->read();
            $map = [];

            foreach ($cache as $entry) {
                $domain = $entry['name'] ?? null;
                $data = $entry['data'] ?? ($entry['address'] ?? null);

                if ($domain && $data) {
                    $map[$data] = $domain;
                }
            }

            return $map;
        } catch (Exception $e) {
            Log::warning("RouterOS getDnsCacheMap error: " . $e->getMessage());
            return [];
        }
    }
}


