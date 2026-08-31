<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Voucher 80mm Thermal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; margin: 0; padding: 0; }
            @page { margin: 0; size: 80mm auto; }
            .receipt-break { page-break-after: always; break-after: page; }
        }
    </style>
</head>
<body class="bg-zinc-100 dark:bg-zinc-950 p-4 text-zinc-900 flex flex-col items-center">

    <div class="no-print mb-4 flex items-center gap-2">
        <a href="{{ route('vouchers.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200">
            <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Daftar Voucher</span>
        </a>
        <a href="{{ route('vouchers.print.grid', request()->query()) }}" class="px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200">
            Layout Grid A4
        </a>
        <button onclick="window.print()" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 shadow-xs">
            Cetak Struk 80mm (Ctrl+P)
        </button>
    </div>

    @forelse($vouchers as $voucher)
        <!-- 80mm Thermal Paper Container -->
        <div class="w-[72mm] bg-white p-3 border border-zinc-300 print:border-0 text-center font-mono text-xs leading-tight print:p-1 shadow-md print:shadow-none mb-6 print:mb-0 receipt-break">
            <div class="font-bold text-sm">{{ $routerSetting->hotspot_name ?? 'MIKROTIK HOTSPOT NETWORK' }}</div>
            <div class="text-[10px] text-zinc-500 mt-0.5">High Speed Fiber Hotspot Connection</div>
            <div class="my-2 border-t border-dashed border-zinc-400"></div>

            <div class="flex justify-between text-xs py-0.5">
                <span>Paket Hotspot:</span>
                <strong>{{ $voucher->profile?->name ?? 'Hotspot' }}</strong>
            </div>
            @if($voucher->profile?->rate_limit)
                <div class="flex justify-between text-xs py-0.5">
                    <span>Speed Rate Limit:</span>
                    <span>{{ $voucher->profile->rate_limit }}</span>
                </div>
            @endif
            <div class="flex justify-between text-xs py-0.5">
                <span>Masa Berlaku:</span>
                <strong>{{ $voucher->profile?->validity ?? ($voucher->uptime_limit ?: '3 Jam') }}</strong>
            </div>

            <div class="my-3 p-2 bg-zinc-100 border border-zinc-300 rounded">
                <div class="text-[10px] text-zinc-500 uppercase">KODE LOGIN HOTSPOT</div>
                <div class="text-lg font-extrabold tracking-wider my-1">{{ $voucher->username }}</div>
                @if($voucher->password && $voucher->password !== $voucher->username)
                    <div class="text-[10px] text-zinc-600">Password: <strong>{{ $voucher->password }}</strong></div>
                @endif
            </div>

            <!-- Real Scannable QR Code & 1D Barcode -->
            <div class="my-3 flex items-center justify-center gap-4 bg-zinc-50 p-2.5 rounded-lg border border-zinc-200">
                <div class="p-1 bg-white border border-zinc-300 rounded shrink-0">
                    {!! \App\Support\BarcodeHelper::generateQrSvg(\App\Support\BarcodeHelper::getHotspotLoginUrl($routerSetting->login_url ?? null, $voucher->username, $voucher->password), 80) !!}
                </div>
                <div class="text-left flex-1 min-w-0">
                    <div class="text-[9px] text-zinc-500 font-sans">Scan QR untuk Login Otomatis</div>
                    <div class="mt-2 w-full">
                        {!! \App\Support\BarcodeHelper::generateBarcodeSvg($voucher->username, 26, 1.2) !!}
                    </div>
                </div>
            </div>

            <div class="text-[10px] text-zinc-500">Hubungkan WiFi & buka halaman:</div>
            <div class="font-bold text-xs mt-0.5">{{ $routerSetting->login_url ?? 'http://hotspot.lan' }}</div>

            <div class="my-2 border-t border-dashed border-zinc-400"></div>
            <div class="flex justify-between font-bold text-sm">
                <span>TOTAL:</span>
                <span>{{ ($voucher->profile && (float)$voucher->profile->selling_price == 0) ? 'Gratis (Rp 0)' : \App\Support\FormatHelper::formatRupiah($voucher->profile?->selling_price ?? 3000) }}</span>
            </div>
            <div class="text-[9px] text-zinc-400 mt-2">{{ $routerSetting->footer_text ?? 'Simpan struk ini sebagai bukti login yang sah' }}</div>
        </div>
    @empty
        <div class="p-8 text-center text-xs text-zinc-500 bg-white rounded-xl shadow-xs">
            Belum ada voucher untuk dicetak.
        </div>
    @endforelse

</body>
</html>
