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
        $profileName = $params['profile'] ?? 'Paket-3Jam';
        $qty = min(1000, max(1, (int) ($params['qty'] ?? 10)));
        $credentialMode = $params['credentialMode'] ?? 'same'; // same or different
        $charSet = $params['charSet'] ?? 'numeric'; // numeric, lowercase, uppercase, mixed
        $prefix = $params['prefix'] ?? 'VC-';
        $length = max(3, min(12, (int) ($params['length'] ?? 5)));

        $profile = HotspotProfile::where('name', $profileName)->first();
        $generatedVouchers = [];

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
                    'uptime_limit' => $profile?->validity ?? '3h',
                    'comment' => 'Batch ' . date('Y-m-d H:i') . ' ' . $profileName,
                    'is_active' => true,
                ]);

                // Sync to MikroTik router if connected
                $this->routerOs->addHotspotUser(
                    $code,
                    $password,
                    $profileName,
                    $profile?->validity,
                    'Voucher ' . $profileName
                );

                $generatedVouchers[] = [
                    'id' => $user->id,
                    'username' => $code,
                    'password' => $password,
                    'profile' => $profileName,
                    'price' => $profile ? $profile->selling_price : 3000,
                    'validity' => $profile ? $profile->validity : '3 Jam',
                ];
            }

            // Record Audit Log
            AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'action' => 'voucher_batch_generated',
                'entity_type' => 'HotspotUser',
                'new_values' => [
                    'qty' => $qty,
                    'profile' => $profileName,
                    'prefix' => $prefix,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
                'created_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'count' => count($generatedVouchers),
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
