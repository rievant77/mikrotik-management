<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Voucher Hotspot - Grid A4/F4</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            @page { margin: 8mm; size: A4 portrait; }
        }
    </style>
</head>
<body class="bg-zinc-100 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 p-4 sm:p-8">

    <!-- Top Action Bar (hidden on print) -->
    <div class="max-w-5xl mx-auto mb-6 flex flex-col md:flex-row items-center justify-between gap-4 bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs no-print">
        <div class="flex items-center gap-3">
            <a href="{{ route('vouchers.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Daftar Voucher</span>
            </a>
            <a href="{{ route('hotspot.generate') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                <span>Generator</span>
            </a>
        </div>

        <div>
            <h2 class="font-bold text-sm text-zinc-900 dark:text-zinc-100 text-center md:text-left">Layout Cetak Kartu Voucher (A4 / F4 Sheet)</h2>
            <p class="text-xs text-zinc-500 text-center md:text-left">Total: {{ count($vouchers) }} Lembar Voucher</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('vouchers.print.58mm', request()->query()) }}" class="px-3 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                Thermal 58mm
            </a>
            <a href="{{ route('vouchers.print.80mm', request()->query()) }}" class="px-3 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                Thermal 80mm
            </a>
            <button onclick="window.print()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:bg-zinc-800 flex items-center gap-2 shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak (Ctrl+P)</span>
            </button>
        </div>
    </div>

    <!-- Voucher Sheet Grid -->
    <div class="max-w-5xl mx-auto bg-white text-zinc-900 p-6 rounded-xl border border-zinc-300 print:border-0 print:p-0 print:m-0 print:shadow-none shadow-md">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 print:grid-cols-3 print:gap-2">
            @forelse($vouchers as $v)
                <div class="border border-dashed border-zinc-400 p-3 rounded-lg flex flex-col justify-between bg-white text-zinc-900 break-inside-avoid">
                    <!-- Voucher Header -->
                    <div class="flex items-center justify-between border-b border-zinc-200 pb-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold text-xs">{{ $routerSetting->hotspot_name ?? 'MIKROTIK HOTSPOT' }}</span>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 bg-zinc-100 rounded">
                            {{ $v->profile?->name ?? 'Hotspot' }}
                        </span>
                    </div>

                    <!-- Credential & QR Code Section -->
                    <div class="my-2 flex items-center justify-between gap-2">
                        <div>
                            <div class="text-[10px] text-zinc-500 uppercase">KODE LOGIN:</div>
                            <div class="font-mono text-base font-extrabold tracking-wider text-zinc-900 mt-0.5">
                                {{ $v->username }}
                            </div>
                            @if($v->password && $v->password !== $v->username)
                                <div class="text-[10px] text-zinc-600 font-mono">Pass: <strong>{{ $v->password }}</strong></div>
                            @endif
                            <div class="text-[10px] text-zinc-500 mt-1">Masa Aktif: <strong>{{ $v->profile?->validity ?? ($v->uptime_limit ?: '3 Jam') }}</strong></div>
                        </div>
                        <!-- Real Scannable QR Code -->
                        <div class="w-14 h-14 p-0.5 border border-zinc-300 rounded bg-white flex items-center justify-center shrink-0" title="Scan QR untuk login otomatis">
                            {!! \App\Support\BarcodeHelper::generateQrSvg(\App\Support\BarcodeHelper::getHotspotLoginUrl($routerSetting->login_url ?? null, $v->username, $v->password), 50) !!}
                        </div>
                    </div>

                    <!-- Voucher Footer -->
                    <div class="border-t border-zinc-200 pt-1.5 flex items-center justify-between text-[10px] text-zinc-500">
                        <strong class="text-zinc-900 font-mono">
                            {{ ($v->profile && (float)$v->profile->selling_price == 0) ? 'Gratis (Rp 0)' : \App\Support\FormatHelper::formatRupiah($v->profile?->selling_price ?? 3000) }}
                        </strong>
                        <span>Login: {{ $routerSetting->login_url ?? 'http://hotspot.lan' }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-3 p-12 text-center text-xs text-zinc-400">
                    Tidak ada voucher yang tersedia untuk dicetak.
                </div>
            @endforelse
        </div>
    </div>

</body>
</html>
