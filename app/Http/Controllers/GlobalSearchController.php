<?php

namespace App\Http\Controllers;

use App\Models\HotspotUser;
use App\Models\MonthlyCustomer;
use App\Models\MonthlyInvoice;
use App\Services\RouterOsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    protected RouterOsService $routerOs;

    public function __construct(RouterOsService $routerOs)
    {
        $this->routerOs = $routerOs;
    }

    /**
     * Handle global universal search queries.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 1) {
            return response()->json([
                'query' => $q,
                'results' => [
                    'navigation' => $this->getDefaultNavigation(),
                    'users' => [],
                    'customers' => [],
                    'devices' => [],
                ],
            ]);
        }

        $lowerQ = strtolower($q);

        // 1. Filter Navigation items
        $allNav = $this->getAllNavigation();
        $matchedNav = array_values(array_filter($allNav, function ($nav) use ($lowerQ) {
            return str_contains(strtolower($nav['title']), $lowerQ) ||
                   str_contains(strtolower($nav['keywords'] ?? ''), $lowerQ) ||
                   str_contains(strtolower($nav['description'] ?? ''), $lowerQ);
        }));

        // 2. Search Hotspot Users
        $users = HotspotUser::with('profile')
            ->where('username', 'LIKE', "%{$q}%")
            ->orWhere('comment', 'LIKE', "%{$q}%")
            ->take(8)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'title' => $u->username,
                    'subtitle' => 'Paket: ' . ($u->profile?->name ?? 'Default') . ($u->comment ? ' • ' . $u->comment : ''),
                    'url' => route('users.show', $u->username),
                    'icon' => 'user',
                    'type' => 'Hotspot User',
                    'is_active' => (bool) $u->is_active,
                ];
            });

        // 3. Search Monthly Customers & Invoices
        $customers = MonthlyCustomer::where('name', 'LIKE', "%{$q}%")
            ->orWhere('contact', 'LIKE', "%{$q}%")
            ->orWhere('address', 'LIKE', "%{$q}%")
            ->take(5)
            ->get()
            ->toBase()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'title' => $c->name,
                    'subtitle' => 'Tarif: Rp ' . number_format($c->monthly_price, 0, ',', '.') . ($c->contact ? ' • ' . $c->contact : ''),
                    'url' => route('pos.monthly') . '?search=' . urlencode($c->name),
                    'icon' => 'customer',
                    'type' => 'Pelanggan Bulanan',
                ];
            });

        $invoices = MonthlyInvoice::with('customer')
            ->where('invoice_number', 'LIKE', "%{$q}%")
            ->take(5)
            ->get()
            ->toBase()
            ->map(function ($inv) {
                return [
                    'id' => $inv->id,
                    'title' => $inv->invoice_number,
                    'subtitle' => ($inv->customer?->name ?? 'Pelanggan') . ' • Tagihan: Rp ' . number_format($inv->amount, 0, ',', '.') . ' (' . strtoupper($inv->status) . ')',
                    'url' => route('pos.monthly') . '?search=' . urlencode($inv->invoice_number),
                    'icon' => 'invoice',
                    'type' => 'Invoice',
                ];
            });

        // 4. Search Devices / Leases on Router
        $devices = [];
        try {
            $leases = $this->routerOs->getDhcpLeases();
            foreach ($leases as $l) {
                $ip = $l['address'] ?? ($l['active-address'] ?? '');
                $mac = $l['mac-address'] ?? ($l['active-mac-address'] ?? '');
                $host = $l['host-name'] ?? ($l['active-hostname'] ?? ($l['comment'] ?? ''));

                if (
                    str_contains(strtolower($ip), $lowerQ) ||
                    str_contains(strtolower($mac), $lowerQ) ||
                    str_contains(strtolower($host), $lowerQ)
                ) {
                    $devices[] = [
                        'title' => $host ?: ($ip ?: $mac),
                        'subtitle' => "IP: {$ip} • MAC: {$mac}",
                        'url' => route('devices.index') . '?search=' . urlencode($ip ?: $mac),
                        'icon' => 'device',
                        'type' => 'Perangkat / DHCP Lease',
                    ];
                    if (count($devices) >= 5) break;
                }
            }
        } catch (\Throwable $e) {
            // Router might be offline
        }

        return response()->json([
            'query' => $q,
            'results' => [
                'navigation' => $matchedNav,
                'users' => $users->toBase(),
                'customers' => array_merge($customers->toArray(), $invoices->toArray()),
                'devices' => $devices,
            ],
        ]);
    }

    protected function getDefaultNavigation(): array
    {
        return array_slice($this->getAllNavigation(), 0, 6);
    }

    protected function getAllNavigation(): array
    {
        return [
            [
                'title' => 'Dashboard Utama & Real-Time Traffic',
                'description' => 'Monitoring traffic, CPU router, user aktif harian',
                'url' => route('dashboard'),
                'icon' => 'dashboard',
                'keywords' => 'home beranda live monitoring traffic grafik',
            ],
            [
                'title' => 'Online Users (Pengguna Aktif)',
                'description' => 'Pantau user hotspot yang sedang terhubung',
                'url' => route('users.index'),
                'icon' => 'users',
                'keywords' => 'online hotspot active disconnect putus fup kuota',
            ],
            [
                'title' => 'Live Devices & Bandwidth',
                'description' => 'Daftar perangkat jaringan, DHCP lease, dan binding IP',
                'url' => route('devices.index'),
                'icon' => 'device',
                'keywords' => 'perangkat ip mac binding bypass dhcp lease',
            ],
            [
                'title' => 'Daftar Voucher & Stok',
                'description' => 'Kelola voucher hotspot, filter batch, dan cetak',
                'url' => route('vouchers.index'),
                'icon' => 'voucher',
                'keywords' => 'voucher batch stok print cetak print thermal',
            ],
            [
                'title' => 'Generate Voucher Hotspot',
                'description' => 'Buat batch voucher baru massal ke router MikroTik',
                'url' => route('hotspot.generate'),
                'icon' => 'generate',
                'keywords' => 'generate buat cetak voucher baru kode batch',
            ],
            [
                'title' => 'Profil Paket Hotspot (Tarif)',
                'description' => 'Atur harga paket, masa aktif, rate limit, dan FUP',
                'url' => route('hotspot.profiles'),
                'icon' => 'profile',
                'keywords' => 'profile paket tarif harga speed fup rate limit',
            ],
            [
                'title' => 'Hotspot Users (Database Akun)',
                'description' => 'Kelola seluruh username hotspot tersimpan',
                'url' => route('hotspot.users'),
                'icon' => 'users',
                'keywords' => 'hotspot user akun sandi password database',
            ],
            [
                'title' => 'POS & Analisis Finansial',
                'description' => 'Omzet, laba bersih voucher & bulanan, grafik tren harian',
                'url' => route('pos.index'),
                'icon' => 'pos',
                'keywords' => 'pos kasir omzet laba profit keuangan laporan uang',
            ],
            [
                'title' => 'Tagihan Bulanan & Pelanggan',
                'description' => 'Generate invoice bulanan, catat cicilan, dan data pelanggan',
                'url' => route('pos.monthly'),
                'icon' => 'invoice',
                'keywords' => 'tagihan invoice bulanan langganan pelanggan bayar cicilan',
            ],
            [
                'title' => 'Kustomisasi Template Voucher',
                'description' => 'Desain voucher thermal 58mm/80mm, QR code, logo print',
                'url' => route('settings.templates'),
                'icon' => 'template',
                'keywords' => 'template cetak print barcode qrcode logo',
            ],
            [
                'title' => 'Pengaturan Profil & Aplikasi',
                'description' => 'Ubah profil admin, ganti password, logo & icon aplikasi',
                'url' => route('settings.profile'),
                'icon' => 'settings',
                'keywords' => 'profil akun password ganti sandi logo favicon icon brand nama',
            ],
            [
                'title' => 'Pengaturan Router MikroTik',
                'description' => 'IP host, port API, kredensial login, dan tes koneksi',
                'url' => route('settings.router'),
                'icon' => 'router',
                'keywords' => 'router ip host port ssl password mikrotik connect',
            ],
            [
                'title' => 'Pemeliharaan & Reset Data Sistem',
                'description' => 'Reset penjualan, reset counter internet, hapus user, factory reset',
                'url' => route('settings.maintenance'),
                'icon' => 'maintenance',
                'keywords' => 'reset hapus bersihkan counter maintenance factory reset',
            ],
            [
                'title' => 'Audit Log & Riwayat Aktivitas',
                'description' => 'Rekaman log audit seluruh aksi admin pada sistem',
                'url' => route('audit.index'),
                'icon' => 'audit',
                'keywords' => 'audit log riwayat aktivitas catatan keamanan',
            ],
        ];
    }
}
