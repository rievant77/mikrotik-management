<?php

namespace App\Http\Controllers;

use App\Services\MaintenanceService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    protected MaintenanceService $maintenanceService;

    public function __construct(MaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * Show Maintenance & Reset dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        $counts = $this->maintenanceService->getDataCounts();
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'counts' => $counts]);
        }
        return view('settings.maintenance', compact('counts'));
    }

    /**
     * Execute specific reset operation.
     */
    public function executeReset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|in:reset_sales,reset_users,reset_counters,reset_audit,factory_reset',
            'confirmation_text' => 'required|string',
            'include_monthly_customers' => 'nullable|boolean',
            'delete_from_router' => 'nullable|boolean',
            'target_username' => 'nullable|string',
            'before_date' => 'nullable|date',
        ]);

        if (strtoupper(trim($validated['confirmation_text'])) !== 'RESET') {
            return response()->json([
                'success' => false,
                'message' => 'Kata konfirmasi salah. Harap ketik "RESET" untuk mengonfirmasi.',
            ], 422);
        }

        try {
            switch ($validated['action']) {
                case 'reset_sales':
                    $result = $this->maintenanceService->resetSalesData($validated['before_date'] ?? null);
                    break;
                case 'reset_users':
                    $result = $this->maintenanceService->resetHotspotUsers(
                        $validated['include_monthly_customers'] ?? false,
                        $validated['delete_from_router'] ?? true
                    );
                    break;
                case 'reset_counters':
                    $result = $this->maintenanceService->resetInternetCounters($validated['target_username'] ?? null);
                    break;
                case 'reset_audit':
                    $result = $this->maintenanceService->resetAuditLogs($validated['before_date'] ?? null);
                    break;
                case 'factory_reset':
                    $result = $this->maintenanceService->factoryResetSystem();
                    break;
                default:
                    throw new Exception('Aksi reset tidak valid.');
            }

            $result['counts'] = $this->maintenanceService->getDataCounts();
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan reset: ' . $e->getMessage(),
            ], 500);
        }
    }
}
