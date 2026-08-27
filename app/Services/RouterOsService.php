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
     * Add user to MikroTik Hotspot.
     */
    public function addHotspotUser(string $username, string $password, ?string $profile = null, ?string $limitUptime = null, ?string $comment = null): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $query = (new Query('/ip/hotspot/user/add'))
                ->equal('name', $username)
                ->equal('password', $password);

            if ($profile) {
                $query->equal('profile', $profile);
            }
            if ($limitUptime) {
                $query->equal('limit-uptime', $limitUptime);
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
    public function addHotspotProfile(string $name, ?string $rateLimit = null, ?int $sharedUsers = 1): bool
    {
        $client = $this->getClient();
        if (!$client) {
            return false;
        }

        try {
            $query = (new Query('/ip/hotspot/user/profile/add'))
                ->equal('name', $name)
                ->equal('shared-users', (string) ($sharedUsers ?: 1));

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
}
