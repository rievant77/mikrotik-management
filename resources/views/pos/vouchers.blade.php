<x-layouts.app>
    <x-slot:header>Rekap Penjualan & Omzet Voucher</x-slot:header>

    <div class="space-y-6" x-data="voucherRevenueLedger()">
        <!-- Header & Top Navigation Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Rekap Penjualan & Omzet Voucher</h1>
                <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-300 mt-1">Pendapatan riil voucher hotspot yang telah diaktivasi dan digunakan oleh pelanggan</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Ringkasan POS</span>
                </a>

                <a href="{{ route('pos.monthly') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Tagihan Bulanan</span>
                </a>


                <a href="{{ route('vouchers.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    <span>Stok & Cetak Voucher</span>
                </a>
            </div>
        </div>

        <!-- 4 Key Financial KPI Cards (2 Columns on Mobile, 4 Columns on Desktop) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- 1. Voucher Terpakai -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-5 shadow-xs relative overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Voucher Terpakai</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700">Unit</span>
                    </div>
                    <div class="mt-1.5 sm:mt-2 text-lg sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                        {{ \App\Support\FormatHelper::formatNumber($metrics['total_count'] ?? 0) }}
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-zinc-200 dark:border-zinc-800/80 text-[10px] sm:text-[11px] text-zinc-600 dark:text-zinc-400">
                    Periode: <strong class="text-zinc-900 dark:text-zinc-100 font-medium">{{ $metrics['period'] ?? 'Bulan Ini' }}</strong>
                </div>
            </div>

            <!-- 2. Total Omzet Penjualan -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-5 shadow-xs relative overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Omzet Penjualan</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-700">Gross</span>
                    </div>
                    <div class="mt-1.5 sm:mt-2 text-lg sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                        {{ \App\Support\FormatHelper::formatRupiah($metrics['total_revenue'] ?? 0) }}
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-zinc-200 dark:border-zinc-800/80 text-[10px] sm:text-[11px] text-zinc-600 dark:text-zinc-400">
                    Rata-rata: <strong class="font-mono text-zinc-900 dark:text-zinc-100 font-medium">{{ \App\Support\FormatHelper::formatRupiah($metrics['avg_revenue'] ?? 0) }}</strong> /vchr
                </div>
            </div>

            <!-- 3. Total Laba Bersih (Profit) -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-5 shadow-xs relative overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Laba Bersih</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                            Margin {{ $metrics['margin'] ?? 0 }}%
                        </span>
                    </div>
                    <div class="mt-1.5 sm:mt-2 text-lg sm:text-2xl font-bold text-emerald-700 dark:text-emerald-400 font-mono">
                        {{ \App\Support\FormatHelper::formatRupiah($metrics['total_profit'] ?? 0) }}
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-zinc-200 dark:border-zinc-800/80 text-[10px] sm:text-[11px] text-zinc-600 dark:text-zinc-400">
                    Masuk ke kas bersih usaha
                </div>
            </div>

            <!-- 4. Beban Pokok (HPP) -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-5 shadow-xs relative overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Beban Pokok (HPP)</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700">Cost</span>
                    </div>
                    <div class="mt-1.5 sm:mt-2 text-lg sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                        {{ \App\Support\FormatHelper::formatRupiah($metrics['total_cost'] ?? 0) }}
                    </div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-zinc-200 dark:border-zinc-800/80 text-[10px] sm:text-[11px] text-zinc-600 dark:text-zinc-400">
                    Modal voucher terpakai
                </div>
            </div>
        </div>

        <!-- Filter & Search Controls Bar -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
            <form method="GET" action="{{ route('pos.vouchers') }}" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Left Filters Group -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Period Filter -->
                    <div class="flex items-center gap-2 bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-300 dark:border-zinc-700 rounded-lg p-1">
                        <label class="text-[11px] font-bold text-zinc-700 dark:text-zinc-300 uppercase pl-1.5 hidden sm:inline">Bulan:</label>
                        <input 
                            type="month" 
                            name="month" 
                            value="{{ $month !== 'all' ? $month : '' }}" 
                            onchange="this.form.submit()"
                            class="px-2 py-1 text-xs font-bold font-mono rounded bg-transparent border-0 text-zinc-900 dark:text-zinc-100 focus:ring-0"
                        >
                        <a 
                            href="{{ route('pos.vouchers', array_merge(request()->except(['month', 'page']), ['month' => now()->format('Y-m')])) }}" 
                            class="px-2 py-1 text-xs font-semibold rounded {{ $month === now()->format('Y-m') ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 hover:bg-zinc-300 dark:hover:bg-zinc-600' }} transition-colors"
                        >
                            Bulan Ini
                        </a>
                        <a 
                            href="{{ route('pos.vouchers', array_merge(request()->except(['month', 'page']), ['month' => 'all'])) }}" 
                            class="px-2 py-1 text-xs font-semibold rounded {{ $month === 'all' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 hover:bg-zinc-300 dark:hover:bg-zinc-600' }} transition-colors"
                        >
                            Semua
                        </a>
                    </div>

                    <!-- Profile Dropdown Filter -->
                    <div class="flex items-center gap-1.5">
                        <select 
                            name="profile" 
                            onchange="this.form.submit()"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:ring-0"
                        >
                            <option value="all">Semua Profil Voucher</option>
                            @foreach($profiles as $prof)
                                <option value="{{ $prof->name }}" {{ ($profileName === $prof->name) ? 'selected' : '' }}>
                                    {{ $prof->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="flex items-center gap-1.5">
                        <select 
                            name="category" 
                            onchange="this.form.submit()"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:ring-0"
                        >
                            <option value="all" {{ ($category === 'all') ? 'selected' : '' }}>Semua Kategori</option>
                            <option value="profit" {{ ($category === 'profit') ? 'selected' : '' }}>Komersial (Profit)</option>
                            <option value="pribadi" {{ ($category === 'pribadi') ? 'selected' : '' }}>Pribadi (Internal)</option>
                        </select>
                    </div>
                </div>

                <!-- Right Search Group -->
                <div class="flex items-center gap-2">
                    <div class="relative w-full sm:w-64">
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $search }}" 
                            placeholder="Cari kode / username..."
                            class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-500 dark:placeholder-zinc-400 focus:ring-0"
                        >
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-zinc-500 dark:text-zinc-400">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shrink-0"
                    >
                        Cari
                    </button>

                    @if(request()->filled('search') || request()->filled('profile') && request('profile') !== 'all' || request()->filled('category') && request('category') !== 'all' || request('month') === 'all')
                        <a 
                            href="{{ route('pos.vouchers') }}" 
                            class="px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-300 dark:hover:bg-zinc-600 transition-colors shrink-0"
                            title="Reset filter ke kondisi awal"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Card / Data Feed -->
        @if($sales->count() > 0)
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50/80 dark:bg-zinc-800/60 text-[11px] font-bold text-zinc-700 dark:text-zinc-300 uppercase border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="py-3.5 px-4">Kode Voucher</th>
                                <th class="py-3.5 px-4">Paket / Profil</th>
                                <th class="py-3.5 px-4">Waktu Aktivasi</th>
                                <th class="py-3.5 px-4">Harga Pokok (HPP)</th>
                                <th class="py-3.5 px-4">Harga Jual</th>
                                <th class="py-3.5 px-4">Laba Bersih</th>
                                <th class="py-3.5 px-4 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                            @foreach($sales as $r)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ $r->username }}
                                    </td>
                                    <td class="py-3.5 px-4 font-medium text-zinc-900 dark:text-zinc-100">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span>{{ $r->profile_name }}</span>
                                            @if($r->profile && $r->profile->category === 'pribadi')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-300 dark:border-purple-700">
                                                    Pribadi
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                                    Komersial
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-400 font-mono text-[11px]">
                                        {{ \App\Support\FormatHelper::formatDateTimeShort($r->activated_at) }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-zinc-600 dark:text-zinc-400">
                                        {{ \App\Support\FormatHelper::formatRupiah($r->cost_price) }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ \App\Support\FormatHelper::formatRupiah($r->selling_price) }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-emerald-700 dark:text-emerald-400">
                                        {{ \App\Support\FormatHelper::formatRupiah($r->profit) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <x-badge type="paid">Completed</x-badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($sales instanceof \Illuminate\Pagination\LengthAwarePaginator && $sales->hasPages())
                    <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
                        {{ $sales->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Actionable Empty State (Antislop R-27) -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-500 dark:text-zinc-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                
                @if(request()->filled('search') || (request()->filled('profile') && request('profile') !== 'all') || (request()->filled('category') && request('category') !== 'all'))
                    <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Tidak Ditemukan Penjualan yang Cocok</h3>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1 max-w-sm mx-auto">
                        Tidak ada catatan voucher teraktivasi yang sesuai dengan kata kunci atau filter yang Anda terapkan.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('pos.vouchers') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors shadow-xs">
                            Reset Semua Filter
                        </a>
                    </div>
                @else
                    <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Belum Ada Voucher Terpakai</h3>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1 max-w-sm mx-auto">
                        Pendapatan omzet voucher akan otomatis tercatat di sini saat voucher pertama kali diaktivasi dan digunakan oleh pelanggan pada periode ini.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('vouchers.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors shadow-xs">
                            Buka Stok & Cetak Voucher &rarr;
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function voucherRevenueLedger() {
            return {
                init() {}
            };
        }
    </script>
    @endpush
</x-layouts.app>