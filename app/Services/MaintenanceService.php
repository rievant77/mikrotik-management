<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CashierShift;
use App\Models\DailyUserUsageSummary;
use App\Models\HotspotSession;
use App\Models\HotspotUser;
use App\Models\MonthlyCustomer;
use App\Models\MonthlyInvoice;
use App\Models\MonthlyPayment;
use App\Models\UsageSnapshot;
use App\Models\VoucherSale;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaintenanceService
{
    protected RouterOsService $routerService;

    public function __construct(RouterOsService $routerService)
    {
        $this->routerService = $routerService;
    }

    /**
     * Get real-time record count summary across the database.
     */
    public function getDataCounts(): array
    {
        return [
            'voucher_sales' => VoucherSale::count(),
            'monthly_invoices' => MonthlyInvoice::count(),
            'monthly_payments' => MonthlyPayment::count(),
            'cashier_shifts' => CashierShift::count(),
            'hotspot_users' => HotspotUser::count(),
            'monthly_customers' => MonthlyCustomer::count(),
            'daily_summaries' => DailyUserUsageSummary::count(),
            'usage_snapshots' => UsageSnapshot::count(),
            'hotspot_sessions' => HotspotSession::count(),
            'fup_throttled_users' => HotspotUser::where('fup_active', true)->count(),
            'audit_logs' => AuditLog::count(),
        ];
    }

    /**
     * Reset sales & POS accounting data.
     */
    public function resetSalesData(?string $beforeDate = null): array
    {
        DB::beginTransaction();
        try {
            $salesQ = VoucherSale::query();
            $paymentsQ = MonthlyPayment::query();
            $invoicesQ = MonthlyInvoice::query();
            $shiftsQ = CashierShift::query();

            if ($beforeDate) {
                $salesQ->where('activated_at', '<', $beforeDate);
                $paymentsQ->where('paid_at', '<', $beforeDate);
                $invoicesQ->where('billing_month', '<', $beforeDate);
                $shiftsQ->where('opened_at', '<', $beforeDate);
            }

            $deletedSales = $salesQ->delete();
            $deletedPayments = $paymentsQ->delete();
            $deletedInvoices = $invoicesQ->delete();
            $deletedShifts = $shiftsQ->delete();

            AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'action' => 'maintenance_reset_sales',
                'entity_type' => 'System',
                'entity_id' => 0,
                'new_values' => [
                    'before_date' => $beforeDate,
                    'deleted_sales' => $deletedSales,
                    'deleted_payments' => $deletedPayments,
                    'deleted_invoices' => $deletedInvoices,
                    'deleted_shifts' => $deletedShifts,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => "Data penjualan berhasil direset ({$deletedSales} voucher, {$deletedPayments} pembayaran, {$deletedInvoices} invoice).",
                'deleted' => [
                    'sales' => $deletedSales,
                    'payments' => $deletedPayments,
                    'invoices' => $deletedInvoices,
                ],
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Maintenance resetSalesData error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reset / Remove hotspot users and vouchers.
     */
    public function resetHotspotUsers(bool $includeMonthlyCustomers = false, bool $deleteFromRouter = true): array
    {
        DB::beginTransaction();
        try {
            $userCount = HotspotUser::count();
            $customerCount = 0;

            // 1. Remove from MikroTik
            if ($deleteFromRouter) {
                $this->routerService->removeAllHotspotUsers(['default']);
            }

            // 2. Clear from DB
            HotspotUser::query()->delete();

            if ($includeMonthlyCustomers) {
                $customerCount = MonthlyCustomer::count();
                MonthlyCustomer::query()->delete();
            }

            AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'action' => 'maintenance_reset_users',
                'entity_type' => 'System',
                'entity_id' => 0,
                'new_values' => [
                    'deleted_hotspot_users' => $userCount,
                    'deleted_monthly_customers' => $customerCount,
                    'delete_from_router' => $deleteFromRouter,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => "Pengguna hotspot berhasil direset ({$userCount} user hotspot" . ($includeMonthlyCustomers ? ", {$customerCount} pelanggan bulanan" : "") . ").",
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Maintenance resetHotspotUsers error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reset internet traffic counters, bandwidth usage history, and FUP status.
     */
    public function resetInternetCounters(?string $username = null): array
    {
        DB::beginTransaction();
        try {
            // 1. Reset counters on MikroTik
            $this->routerService->resetHotspotUserCounters($username);

            // 2. Reset database traffic & usage tables
            $summaryQ = DailyUserUsageSummary::query();
            $snapshotsQ = UsageSnapshot::query();
            $sessionsQ = HotspotSession::query();

            if ($username) {
                $summaryQ->where('username', $username);
                $snapshotsQ->where('username', $username);
                $sessionsQ->where('username', $username);
            }

            $deletedSummaries = $summaryQ->delete();
            $deletedSnapshots = $snapshotsQ->delete();
            $deletedSessions = $sessionsQ->delete();

            // 3. Reset FUP counters and restore throttled speeds
            $userQ = HotspotUser::query();
            if ($username) {
                $userQ->where('username', $username);
            }
            $users = $userQ->get();
            $fupRestored = 0;

            foreach ($users as $u) {
                if ($u->fup_active) {
                    try {
                        app(FupManagementService::class)->restoreUserFromFup($u, $this->routerService);
                        $fupRestored++;
                    } catch (Exception $e) {
                        Log::warning("FUP restore during reset failed for {$u->username}: " . $e->getMessage());
                    }
                }
                $u->update([
                    'fup_usage_bytes' => 0,
                    'fup_active' => false,
                    'fup_triggered_at' => null,
                    'fup_last_reset_at' => now(),
                ]);
            }

            AuditLog::create([
                'user_id' => auth()->id() ?? null,
                'action' => 'maintenance_reset_counters',
                'entity_type' => 'System',
                'entity_id' => 0,
                'new_values' => [
                    'target_user' => $username ?: 'all',
                    'deleted_summaries' => $deletedSummaries,
                    'deleted_snapshots' => $deletedSnapshots,
                    'deleted_sessions' => $deletedSessions,
                    'fup_users_restored' => $fupRestored,
                ],
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => "Counter internet, kuota FUP, dan riwayat traffic berhasil direset ke 0.",
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Maintenance resetInternetCounters error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reset system audit logs.
     */
    public function resetAuditLogs(?string $beforeDate = null): array
    {
        try {
            $q = AuditLog::query();
            if ($beforeDate) {
                $q->where('created_at', '<', $beforeDate);
            }
            $count = $q->delete();

            return [
                'success' => true,
                'message' => "Log audit berhasil dibersihkan ({$count} log dihapus).",
            ];
        } catch (Exception $e) {
            Log::error("Maintenance resetAuditLogs error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Full Factory Reset (All operational data).
     */
    public function factoryResetSystem(): array
    {
        $this->resetSalesData();
        $this->resetInternetCounters();
        $this->resetHotspotUsers(true, true);

        AuditLog::create([
            'user_id' => auth()->id() ?? null,
            'action' => 'maintenance_factory_reset',
            'entity_type' => 'System',
            'entity_id' => 0,
            'new_values' => ['type' => 'full_factory_reset'],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Factory Reset berhasil! Seluruh data operasional telah dibersihkan secara total.',
        ];
    }
}
