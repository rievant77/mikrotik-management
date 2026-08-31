<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\HotspotProfile;
use App\Models\HotspotUser;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoucherBatchService
{
    protected RouterOsService $routerOs;

    public function __construct(?RouterOsService $routerOs = null)
    {
        $this->routerOs = $routerOs ?? new RouterOsService();
    }

    /**
     * Generate a batch of vouchers.
     */
    public function generate(array $params): array
    {
        $profileName = $params['profile'] ?? 'default';
        $server = $params['server'] ?? 'all';
        $qty = min(1000, max(1, (int) ($params['qty'] ?? 5)));
        $credentialMode = $params['credentialMode'] ?? 'same'; // same or different
        $charSet = $params['charSet'] ?? 'numeric'; // numeric, lowercase, uppercase, mixed
        $prefix = $params['prefix'] ?? '';
        $length = max(3, min(12, (int) ($params['length'] ?? 5)));
        $timeLimitRaw = $params['timeLimit'] ?? null;
        $dataLimitRaw = $params['dataLimit'] ?? null;
        $userComment = trim($params['comment'] ?? '');

        $profile = HotspotProfile::where('name', $profileName)->first();
        $generatedVouchers = [];

        // Parse Time Limit & Data Limit
        $uptimeLimit = $timeLimitRaw ? \App\Support\FormatHelper::parseValidityToRouterTime($timeLimitRaw) : (\App\Support\FormatHelper::parseValidityToRouterTime($profile?->validity) ?? null);
        $bytesLimit = $dataLimitRaw ? \App\Support\FormatHelper::parseBytesLimit($dataLimitRaw) : null;

        $batchTag = 'Batch ' . date('Y-m-d H:i');
        $finalComment = $userComment !== '' ? $userComment : ($batchTag . ' ' . $profileName);

        DB::beginTransaction();

        try {
            for ($i = 0; $i < $qty; $i++) {
                $code = $prefix . $this->generateCode($charSet, $length);
                $password = ($credentialMode === 'same') ? $code : $this->generateCode('numeric', 4);

                // Ensure unique username
                while (HotspotUser::where('username', $code)->exists()) {
                    $code = $prefix . $this->generateCode($charSet, $length);
                    $password = ($credentialMode === 'same') ? $code : $this->generateCode('numeric', 4);
                }

                $user = HotspotUser::create([
                    'profile_id' => $profile?->id,
                    'username' => $code,
                    'password' => $password,
                    'uptime_limit' => $uptimeLimit,
                    'comment' => $finalComment,
                    'is_active' => true,
                ]);

                // Sync to MikroTik router if connected
                $this->routerOs->addHotspotUser(
                    $code,
                    $password,
                    $profileName,
                    $uptimeLimit,
                    $finalComment,
                    $server,
                    $bytesLimit
                );

                $generatedVouchers[] = [
                    'id' => $user->id,
                    'username' => $code,
                    'password' => $password,
                    'profile' => $profileName,
                    'server' => $server,
                    'price' => $profile ? $profile->selling_price : 3000,
                    'validity' => $profile ? $profile->validity : ($uptimeLimit ?: '-'),
                    'time_limit' => $uptimeLimit,
                    'data_limit' => $bytesLimit,
                ];
            }

            // Record Audit Log
            AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'action' => 'voucher_batch_generated',
                'entity_type' => 'HotspotUser',
                'new_values' => [
                    'qty' => $qty,
                    'server' => $server,
                    'profile' => $profileName,
                    'prefix' => $prefix,
                    'batch' => $batchTag,
                    'time_limit' => $uptimeLimit,
                    'data_limit' => $bytesLimit,
                    'comment' => $finalComment,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'count' => count($generatedVouchers),
                'batch' => $batchTag,
                'vouchers' => $generatedVouchers,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal generate voucher: ' . $e->getMessage(),
                'vouchers' => [],
            ];
        }
    }

    /**
     * Generate random string based on charset.
     */
    protected function generateCode(string $charSet, int $length): string
    {
        $characters = match ($charSet) {
            'numeric' => '0123456789',
            'lowercase' => 'abcdefghjkmnpqrstuvwxyz', // omit confusing chars like l, o, i
            'uppercase' => 'ABCDEFGHJKLMNPQRSTUVWXYZ',
            default => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ',
        };

        $code = '';
        $maxIndex = strlen($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, $maxIndex)];
        }

        return $code;
    }
}
