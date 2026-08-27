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
        }
    </style>
</head>
<body class="bg-zinc-100 p-4 text-zinc-900 flex flex-col items-center">

    <div class="no-print mb-4 flex items-center gap-3">
        <button onclick="window.print()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 shadow-xs">
            Cetak Struk 58mm
        </button>
        <a href="{{ route('vouchers.print.grid') }}" class="px-3 py-2 text-xs rounded-lg border border-zinc-300 bg-white">
            Kembali ke Grid
        </a>
    </div>

    <!-- 58mm Thermal Paper Container (width approx 48mm-54mm printable) -->
    <div class="w-[54mm] bg-white p-2 border border-zinc-300 print:border-0 text-center font-mono text-[11px] leading-tight print:p-1">
        <div class="font-bold text-xs">MIKROTIK HOTSPOT</div>
        <div class="text-[9px] text-zinc-500 mt-0.5">Internet Cepat & Stabil</div>
        <div class="my-2 border-t border-dashed border-zinc-400"></div>

        <div class="text-[10px]">Paket: <strong>Paket-3Jam</strong></div>
        <div class="text-[10px]">Masa Aktif: <strong>3 Jam</strong></div>

        <div class="my-2 p-1.5 bg-zinc-100 border border-zinc-300 rounded">
            <div class="text-[9px] text-zinc-500 uppercase">KODE LOGIN</div>
            <div class="text-sm font-extrabold tracking-wider my-0.5">VC-89201</div>
        </div>

        <div class="flex justify-center my-2">
            <svg class="w-16 h-16 text-zinc-900" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm8-2h6v6h-6V3zm2 2v2h2V5h-2zM3 13h6v6H3v-6zm2 2v2h2v-2H5zm13-2h3v2h-3v-2zm-5 0h2v3h-2v-3zm2 3h3v3h-3v-3zm3 0h2v3h-2v-3zm-8 2h3v2h-3v-2z" />
            </svg>
        </div>

        <div class="text-[9px] text-zinc-500">Scan QR atau buka browser di</div>
        <div class="font-bold text-[10px]">http://hotspot.lan</div>

        <div class="my-2 border-t border-dashed border-zinc-400"></div>
        <div class="font-bold text-xs">Rp3.000</div>
        <div class="text-[8px] text-zinc-400 mt-1">Terima Kasih Atas Kunjungan Anda</div>
    </div>

</body>
</html>
