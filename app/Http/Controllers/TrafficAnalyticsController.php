<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\TrafficSignature;
use App\Services\RouterOsService;
use App\Services\TrafficAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    /**
     * Get list of all traffic signatures for management.
     */
    public function signatures(Request $request): JsonResponse
    {
        $signatures = TrafficSignature::orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $signatures,
        ]);
    }

    /**
     * Create a new custom traffic signature.
     */
    public function storeSignature(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'in:video,social_media,gaming,cloud_work,browsing'],
            'mark_name' => ['nullable', 'string', 'max:100', 'unique:traffic_signatures,mark_name'],
            'address_list_name' => ['nullable', 'string', 'max:100'],
            'fqdn_domains' => ['nullable'],
            'tls_host' => ['nullable', 'string', 'max:500'],
            'tcp_ports' => ['nullable', 'string', 'max:255'],
            'udp_ports' => ['nullable', 'string', 'max:255'],
            'is_quic' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $markName = $validated['mark_name'] ?? ('agy_cm_' . Str::slug($validated['name'], '_'));
        $addressList = $validated['address_list_name'] ?? ('agy_list_' . Str::slug($validated['name'], '_'));

        $domains = $validated['fqdn_domains'] ?? [];
        if (is_string($domains)) {
            $domains = array_values(array_filter(array_map('trim', explode(',', $domains))));
        }

        $signature = TrafficSignature::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'mark_name' => $markName,
            'address_list_name' => $addressList,
            'fqdn_domains' => $domains,
            'tls_host' => $validated['tls_host'] ?? null,
            'tcp_ports' => $validated['tcp_ports'] ?? null,
            'udp_ports' => $validated['udp_ports'] ?? null,
            'is_quic' => (bool) ($validated['is_quic'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'is_system' => false,
        ]);

        AuditLog::create([
            'user_id' => Auth::id() ?? null,
            'action' => 'create_traffic_signature',
            'entity_type' => 'TrafficSignature',
            'entity_id' => $signature->id,
            'new_values' => $signature->toArray(),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Signature '{$signature->name}' berhasil ditambahkan!",
            'data' => $signature,
        ], 201);
    }

    /**
     * Update an existing traffic signature.
     */
    public function updateSignature(Request $request, int $id): JsonResponse
    {
        $signature = TrafficSignature::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'category' => ['sometimes', 'required', 'string', 'in:video,social_media,gaming,cloud_work,browsing'],
            'mark_name' => ['sometimes', 'nullable', 'string', 'max:100', 'unique:traffic_signatures,mark_name,' . $id],
            'address_list_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'fqdn_domains' => ['nullable'],
            'tls_host' => ['nullable', 'string', 'max:500'],
            'tcp_ports' => ['nullable', 'string', 'max:255'],
            'udp_ports' => ['nullable', 'string', 'max:255'],
            'is_quic' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (isset($validated['fqdn_domains']) && is_string($validated['fqdn_domains'])) {
            $validated['fqdn_domains'] = array_values(array_filter(array_map('trim', explode(',', $validated['fqdn_domains']))));
        }

        $oldValues = $signature->toArray();
        $signature->update($validated);

        AuditLog::create([
            'user_id' => Auth::id() ?? null,
            'action' => 'update_traffic_signature',
            'entity_type' => 'TrafficSignature',
            'entity_id' => $signature->id,
            'old_values' => $oldValues,
            'new_values' => $signature->toArray(),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Signature '{$signature->name}' berhasil diperbarui!",
            'data' => $signature,
        ]);
    }

    /**
     * Delete a custom traffic signature.
     */
    public function destroySignature(Request $request, int $id): JsonResponse
    {
        $signature = TrafficSignature::findOrFail($id);
        $name = $signature->name;
        $signature->delete();

        AuditLog::create([
            'user_id' => Auth::id() ?? null,
            'action' => 'delete_traffic_signature',
            'entity_type' => 'TrafficSignature',
            'entity_id' => $id,
            'old_values' => ['name' => $name],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Signature '{$name}' berhasil dihapus!",
        ]);
    }

    /**
     * Reset signatures to latest default definitions.
     */
    public function resetDefaultSignatures(Request $request): JsonResponse
    {
        TrafficSignature::seedDefaults();

        AuditLog::create([
            'user_id' => Auth::id() ?? null,
            'action' => 'reset_default_traffic_signatures',
            'entity_type' => 'TrafficSignature',
            'entity_id' => null,
            'new_values' => ['status' => 'defaults_restored'],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        $signatures = TrafficSignature::orderBy('category')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Signature trafik bawaan berhasil dipulihkan ke versi default terbaru!',
            'data' => $signatures,
        ]);
    }

    /**
     * Get real-time user rank for a specific application/platform.
     */
    public function platformUserRank(Request $request, string $platformKey): JsonResponse
    {
        $rankData = $this->analyticsService->getPlatformUserRank($platformKey);

        return response()->json([
            'success' => true,
            'data' => $rankData,
        ]);
    }
}
