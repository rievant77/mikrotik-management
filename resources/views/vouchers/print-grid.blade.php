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
    <div class="max-w-5xl mx-auto mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs no-print">
        <div>
            <h2 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Layout Cetak Kartu Voucher (A4 / F4 Sheet)</h2>
            <p class="text-xs text-zinc-500">Format kartu potong dengan garis putus-putus dan QR Code</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('vouchers.print.58mm') }}" class="px-3 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                Thermal 58mm
            </a>
            <a href="{{ route('vouchers.print.80mm') }}" class="px-3 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                Thermal 80mm
            </a>
            <button onclick="window.print()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 hover:bg-zinc-800 flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak / Print (Ctrl+P)</span>
            </button>
        </div>
    </div>

    <!-- Voucher Sheet Grid -->
    <div class="max-w-5xl mx-auto bg-white text-zinc-900 p-6 rounded-xl border border-zinc-300 print:border-0 print:p-0 print:m-0 print:shadow-none shadow-md">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 print:grid-cols-3 print:gap-2">
            @for ($i = 1; $i <= 12; $i++)
                <div class="border border-dashed border-zinc-400 p-3 rounded-lg flex flex-col justify-between bg-white text-zinc-900 break-inside-avoid">
                    <!-- Voucher Header -->
                    <div class="flex items-center justify-between border-b border-zinc-200 pb-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold text-xs">MIKROTIK HOTSPOT</span>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 bg-zinc-100 rounded">Paket 3 Jam</span>
                    </div>

                    <!-- Credential & QR Code Section -->
                    <div class="my-2 flex items-center justify-between gap-2">
                        <div>
                            <div class="text-[10px] text-zinc-500 uppercase">Kode Voucher:</div>
                            <div class="font-mono text-base font-extrabold tracking-wider text-zinc-900 mt-0.5">
                                VC-892{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <div class="text-[10px] text-zinc-500 mt-1">Masa Aktif: <strong>3 Jam</strong></div>
                        </div>
                        <!-- Mock QR Code SVG -->
                        <div class="w-12 h-12 border border-zinc-200 p-0.5 rounded bg-zinc-50 flex items-center justify-center">
                            <svg class="w-10 h-10 text-zinc-800" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm8-2h6v6h-6V3zm2 2v2h2V5h-2zM3 13h6v6H3v-6zm2 2v2h2v-2H5zm13-2h3v2h-3v-2zm-5 0h2v3h-2v-3zm2 3h3v3h-3v-3zm3 0h2v3h-2v-3zm-8 2h3v2h-3v-2z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Voucher Footer -->
                    <div class="border-t border-zinc-200 pt-1.5 flex items-center justify-between text-[10px] text-zinc-500">
                        <span>Rp3.000</span>
                        <span>Login: 192.168.88.1</span>
                    </div>
                </div>
            @endfor
        </div>
    </div>

</body>
</html>
