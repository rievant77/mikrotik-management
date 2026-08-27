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
        }
    </style>
</head>
<body class="bg-zinc-100 p-4 text-zinc-900 flex flex-col items-center">

    <div class="no-print mb-4 flex items-center gap-3">
        <button onclick="window.print()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 shadow-xs">
            Cetak Struk 80mm
        </button>
        <a href="{{ route('vouchers.print.grid') }}" class="px-3 py-2 text-xs rounded-lg border border-zinc-300 bg-white">
            Kembali ke Grid
        </a>
    </div>

    <!-- 80mm Thermal Paper Container (width approx 72mm printable) -->
    <div class="w-[72mm] bg-white p-3 border border-zinc-300 print:border-0 text-center font-mono text-xs leading-tight print:p-1">
        <div class="font-bold text-sm">MIKROTIK HOTSPOT NETWORK</div>
        <div class="text-[10px] text-zinc-500 mt-0.5">High Speed Fiber Hotspot Connection</div>
        <div class="my-2 border-t border-dashed border-zinc-400"></div>

        <div class="flex justify-between text-xs py-0.5">
            <span>Paket Hotspot:</span>
            <strong>Paket-24Jam</strong>
        </div>
        <div class="flex justify-between text-xs py-0.5">
            <span>Speed Rate Limit:</span>
            <span>Up to 3 Mbps</span>
        </div>
        <div class="flex justify-between text-xs py-0.5">
            <span>Masa Berlaku:</span>
            <strong>24 Jam</strong>
        </div>

        <div class="my-3 p-2 bg-zinc-100 border border-zinc-300 rounded">
            <div class="text-[10px] text-zinc-500 uppercase">KODE LOGIN HOTSPOT</div>
            <div class="text-lg font-extrabold tracking-wider my-1">VC-77192</div>
        </div>

        <div class="flex justify-center my-2">
            <svg class="w-20 h-20 text-zinc-900" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm8-2h6v6h-6V3zm2 2v2h2V5h-2zM3 13h6v6H3v-6zm2 2v2h2v-2H5zm13-2h3v2h-3v-2zm-5 0h2v3h-2v-3zm2 3h3v3h-3v-3zm3 0h2v3h-2v-3zm-8 2h3v2h-3v-2z" />
            </svg>
        </div>

        <div class="text-[10px] text-zinc-500">Hubungkan WiFi & buka halaman:</div>
        <div class="font-bold text-xs mt-0.5">http://hotspot.lan</div>

        <div class="my-2 border-t border-dashed border-zinc-400"></div>
        <div class="flex justify-between font-bold text-sm">
            <span>TOTAL:</span>
            <span>Rp10.000</span>
        </div>
        <div class="text-[9px] text-zinc-400 mt-2">Simpan struk ini sebagai bukti login yang sah</div>
    </div>

</body>
</html>
