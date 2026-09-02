<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\RouterOsService;
use App\Services\TrafficAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TrafficAnalyticsController extends Controller
{
    protected TrafficAnalyticsService $analyticsService;
    protected RouterOsService $routerOs;

    public function __construct(TrafficAnalyticsService $analyticsService, RouterOsService $routerOs)
    {
        $this->analyticsService = $analyticsService;
        $this->routerOs = $routerOs;
    }

    /**
     * Display the Traffic & Application Analytics page.
     */
    public function index(Request $request): View
    {
        $period = $request->input('period', 'today');
        $data = $this->analyticsService->getAnalyticsData($period);

        return view('traffic.index', compact('data', 'period'));
    }

    /**
     * Get real-time live data for frontend polling & charts.
     */
    public function liveData(Request $request): JsonResponse
    {
        $period = $request->input('period', 'today');
        $data = $this->analyticsService->getAnalyticsData($period);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Deploy non-intrusive traffic monitoring mangle rules to MikroTik.
     */
    public function deployRules(Request $request): JsonResponse
    {
        $result = $this->routerOs->deployTrafficMangleRules();

        if ($result['success']) {
            AuditLog::create([
                'user_id' => Auth::id() ?? null,
                'action' => 'deploy_traffic_rules',
                'entity_type' => 'RouterOS',
                'entity_id' => null,
                'new_values' => ['deployed_count' => $result['deployed_count']],
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);
        }

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Reset traffic category mangle counters in MikroTik.
     */
    public function resetCounters(Request $request): JsonResponse
    {
        $success = $this->routerOs->resetTrafficCategoryCounters();

        if ($success) {
            AuditLog::create([
                'user_id' => Auth::id() ?? null,
                'action' => 'reset_traffic_counters',
                'entity_type' => 'RouterOS',
                'entity_id' => null,
                'new_values' => ['status' => 'counters_reset'],
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Counter pemakaian trafik berhasil di-reset ke 0 di MikroTik!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal me-reset counter trafik di MikroTik.',
        ], 500);
    }
}
