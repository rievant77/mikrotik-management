<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Voucher 58mm Thermal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; margin: 0; padding: 0; }
            @page { margin: 0; size: 58mm auto; }
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
            Cetak Struk (Ctrl+P)
        </button>
    </div>

    @forelse($vouchers as $voucher)
        <!-- 58mm Thermal Paper Container -->
        <div class="w-[54mm] bg-white p-2 border border-zinc-300 print:border-0 text-center font-mono text-[11px] leading-tight print:p-1 shadow-md print:shadow-none mb-6 print:mb-0 receipt-break">
            <div class="font-bold text-xs">{{ $routerSetting->hotspot_name ?? 'MIKROTIK HOTSPOT' }}</div>
            <div class="text-[9px] text-zinc-500 mt-0.5">Internet Cepat & Stabil</div>
            <div class="my-2 border-t border-dashed border-zinc-400"></div>

            <div class="text-[10px]">Paket: <strong>{{ $voucher->profile?->name ?? 'Hotspot' }}</strong></div>
            <div class="text-[10px]">Masa Aktif: <strong>{{ $voucher->profile?->validity ?? ($voucher->uptime_limit ?: '3 Jam') }}</strong></div>

            <div class="my-2 p-1.5 bg-zinc-100 border border-zinc-300 rounded">
                <div class="text-[9px] text-zinc-500 uppercase">KODE LOGIN</div>
                <div class="text-sm font-extrabold tracking-wider my-0.5">{{ $voucher->username }}</div>
                @if($voucher->password && $voucher->password !== $voucher->username)
                    <div class="text-[9px] text-zinc-600">Pass: <strong>{{ $voucher->password }}</strong></div>
                @endif
            </div>

            <!-- Real Scannable QR Code -->
            <div class="flex flex-col items-center justify-center my-2">
                <div class="p-1 bg-white border border-zinc-300 rounded inline-block">
                    {!! \App\Support\BarcodeHelper::generateQrSvg(\App\Support\BarcodeHelper::getHotspotLoginUrl($routerSetting->login_url ?? null, $voucher->username, $voucher->password), 88) !!}
                </div>
                <div class="text-[8px] text-zinc-500 mt-1 font-sans">Scan QR untuk Login Otomatis</div>
            </div>

            <div class="text-[9px] text-zinc-500">Atau buka halaman login di:</div>
            <div class="font-bold text-[10px]">{{ $routerSetting->login_url ?? 'http://hotspot.lan' }}</div>

            <div class="my-2 border-t border-dashed border-zinc-400"></div>
            <div class="font-bold text-xs">
                {{ ($voucher->profile && (float)$voucher->profile->selling_price == 0) ? 'Gratis (Rp 0)' : \App\Support\FormatHelper::formatRupiah($voucher->profile?->selling_price ?? 3000) }}
            </div>
            <div class="text-[8px] text-zinc-400 mt-1">{{ $routerSetting->footer_text ?? 'Terima Kasih Atas Kunjungan Anda' }}</div>
        </div>
    @empty
        <div class="p-8 text-center text-xs text-zinc-500 bg-white rounded-xl shadow-xs">
            Belum ada voucher untuk dicetak.
        </div>
    @endforelse

</body>
</html>
