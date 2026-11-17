<x-layouts.app>
    <x-slot:header>Dashboard Finansial & POS</x-slot:header>

    <div class="space-y-6" x-data="posDashboard()">
        <!-- Header & Top Navigation Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Dashboard Finansial & POS</h1>
                <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-300 mt-1">Kalkulasi terpadu omzet, modal operasional, laba bersih, dan arus kas bisnis hotspot</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Month Filter -->
                <div class="flex items-center gap-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg p-1 shadow-xs">
                    <input 
                        type="month" 
                        x-model="selectedMonth" 
                        @change="changeMonth()" 
                        class="px-2.5 py-1 text-xs font-bold font-mono rounded bg-transparent dark:bg-transparent border-0 dark:border-0 text-zinc-900 dark:text-zinc-100 focus:ring-0"
                    >
                    <button type="button" @click="setThisMonth()" class="px-2.5 py-1 text-xs font-semibold rounded bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 transition-colors">
                        Bulan Ini
                    </button>
                </div>


                <a href="{{ route('pos.monthly') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Tagihan Bulanan</span>
                </a>
                <a href="{{ route('pos.vouchers') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    <span>Rekap Voucher</span>
                </a>
            </div>
        </div>

        <!-- 4 Key Executive Financial KPI Cards (2 Columns on Mobile, 4 Columns on Desktop) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- 1. Total Pemasukan Kas / Gross Inflow -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-5 shadow-xs relative overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Pemasukan Kas</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700">Gross</span>
                    </div>
                    <div class="mt-1.5 sm:mt-2 text-lg sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono" x-text="formatRupiah(overview.total_gross_income)"></div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-zinc-200 dark:border-zinc-800/80 text-[10px] sm:text-[11px] text-zinc-600 dark:text-zinc-400 flex flex-col gap-0.5">
                    <div class="flex justify-between">
                        <span class="truncate">Voucher:</span>
                        <strong class="font-mono text-zinc-900 dark:text-zinc-100 ml-1" x-text="formatRupiah(overview.voucher_revenue)"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="truncate">Bulanan:</span>
                        <strong class="font-mono text-zinc-900 dark:text-zinc-100 ml-1" x-text="formatRupiah(overview.monthly_paid)"></strong>
                    </div>
                </div>
            </div>

            <!-- 2. Total Laba Bersih / Net Profit -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-5 shadow-xs relative overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Laba Bersih</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700" x-text="'Margin ' + (overview.overall_profit_margin || 0) + '%'"></span>
                    </div>
                    <div class="mt-1.5 sm:mt-2 text-lg sm:text-2xl font-bold text-emerald-700 dark:text-emerald-400 font-mono" x-text="formatRupiah(overview.total_net_profit)"></div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-zinc-200 dark:border-zinc-800/80 text-[10px] sm:text-[11px] text-zinc-600 dark:text-zinc-400 flex flex-col gap-0.5">
                    <div class="flex justify-between">
                        <span class="truncate">Laba Vchr:</span>
                        <strong class="font-mono text-emerald-700 dark:text-emerald-400 ml-1" x-text="formatRupiah(overview.voucher_profit)"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="truncate">Laba Bln:</span>
                        <strong class="font-mono text-emerald-700 dark:text-emerald-400 ml-1" x-text="formatRupiah(overview.monthly_profit)"></strong>
                    </div>
                </div>
            </div>

            <!-- 3. Kinerja Omzet & Profit Voucher -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-5 shadow-xs relative overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Voucher</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-700" x-text="'Margin ' + (overview.voucher_margin || 0) + '%'"></span>
                    </div>
                    <div class="mt-1.5 sm:mt-2 text-lg sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono" x-text="formatRupiah(overview.voucher_revenue)"></div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-zinc-200 dark:border-zinc-800/80 text-[10px] sm:text-[11px] text-zinc-600 dark:text-zinc-400 flex flex-col gap-0.5">
                    <div class="flex justify-between">
                        <span class="truncate">Terpakai:</span>
                        <strong class="font-mono text-zinc-900 dark:text-zinc-100 ml-1" x-text="formatNumber(overview.voucher_count || 0) + ' Unit'"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="truncate">Laba:</span>
                        <strong class="font-mono text-emerald-700 dark:text-emerald-400 ml-1" x-text="formatRupiah(overview.voucher_profit)"></strong>
                    </div>
                </div>
            </div>

            <!-- 4. Kinerja Omzet & Profit Bulanan -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-5 shadow-xs relative overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Bulanan</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-900 dark:text-blue-300 border border-blue-300 dark:border-blue-700" x-text="'Lunas ' + (overview.monthly_collection_rate || 0) + '%'"></span>
                    </div>
                    <div class="mt-1.5 sm:mt-2 text-lg sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono" x-text="formatRupiah(overview.monthly_paid)"></div>
                </div>
                <div class="mt-2.5 pt-2 border-t border-zinc-200 dark:border-zinc-800/80 text-[10px] sm:text-[11px] text-zinc-600 dark:text-zinc-400 flex flex-col gap-0.5">
                    <div class="flex justify-between">
                        <span class="truncate">Laba:</span>
                        <strong class="font-mono text-emerald-700 dark:text-emerald-400 ml-1" x-text="formatRupiah(overview.monthly_profit)"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="truncate">Piutang:</span>
                        <strong class="font-mono text-rose-700 dark:text-rose-400 ml-1" x-text="formatRupiah(overview.monthly_unpaid)"></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visual Analytics Grid: Daily Revenue Trend (Area Chart) & Breakdown (Donut Charts) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Chart (2 Columns): Daily Revenue & Profit Trend -->
            <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-200 dark:border-zinc-800">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Tren Arus Kas & Laba Harian</h3>
                        <p class="text-[11px] sm:text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">Komparasi omzet voucher, pembayaran bulanan, dan laba harian (<span class="font-medium text-zinc-800 dark:text-zinc-200" x-text="overview.period"></span>)</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="flex items-center gap-1.5 text-zinc-800 dark:text-zinc-200 font-semibold">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Voucher
                        </span>
                        <span class="flex items-center gap-1.5 text-zinc-800 dark:text-zinc-200 font-semibold">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Bulanan
                        </span>
                        <span class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-bold">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Laba
                        </span>
                    </div>
                </div>
                <div class="pt-4 flex-1 min-h-[300px]">
                    <div id="posDailyTrendChart" class="w-full h-full"></div>
                </div>
            </div>

            <!-- Right Column (1 Column): Distribution Donuts (Revenue Share & Payment Method) -->
            <div class="space-y-6">
                <!-- Donut 1: Revenue Contribution -->
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                    <div class="pb-3 border-b border-zinc-200 dark:border-zinc-800">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Proporsi Sumber Omzet</h3>
                        <p class="text-[11px] sm:text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">Perbandingan kontribusi Voucher vs Bulanan</p>
                    </div>
                    <div class="pt-3 min-h-[190px] flex items-center justify-center">
                        <template x-if="!hasRevenueData">
                            <div class="py-10 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                Belum ada data transaksi omzet pada periode ini.
                            </div>
                        </template>
                        <div x-show="hasRevenueData" id="posRevenueShareChart" class="w-full flex justify-center"></div>
                    </div>
                </div>

                <!-- Donut 2: Payment Method Breakdown -->
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                    <div class="pb-3 border-b border-zinc-200 dark:border-zinc-800">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Metode Pembayaran</h3>
                        <p class="text-[11px] sm:text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">Cash / Tunai vs Transfer Bank vs QRIS</p>
                    </div>
                    <div class="pt-3 min-h-[190px] flex items-center justify-center">
                        <template x-if="!hasMethodData">
                            <div class="py-10 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                Belum ada rincian metode pembayaran pada periode ini.
                            </div>
                        </template>
                        <div x-show="hasMethodData" id="posPaymentMethodChart" class="w-full flex justify-center"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Leaderboard & Subscription Health Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left: Top 5 Paket Voucher Terlaris -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-200 dark:border-zinc-800">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Top 5 Paket Voucher Terlaris</h3>
                        <p class="text-[11px] sm:text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">Profil voucher dengan penjualan & profit tertinggi</p>
                    </div>
                    <a href="{{ route('pos.vouchers') }}" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-emerald-700 dark:hover:text-emerald-400 underline-offset-2 hover:underline">Semua &rarr;</a>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/80 mt-2">
                    <template x-if="!overview.top_profiles || overview.top_profiles.length === 0">
                        <div class="py-8 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400">
                            Belum ada voucher yang teraktivasi pada periode ini.
                        </div>
                    </template>
                    <template x-for="(p, idx) in (overview.top_profiles || [])" :key="p.profile_name">
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold font-mono" :class="idx === 0 ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-300 dark:border-amber-700' : (idx === 1 ? 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 border border-zinc-300 dark:border-zinc-600' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700')" x-text="idx + 1"></span>
                                <div>
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100 font-mono" x-text="p.profile_name"></div>
                                    <div class="text-[11px] text-zinc-600 dark:text-zinc-400 font-medium" x-text="p.count + ' Voucher Terjual'"></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="formatRupiah(p.revenue)"></div>
                                <div class="text-[11px] text-emerald-700 dark:text-emerald-400 font-semibold" x-text="'Laba: ' + formatRupiah(p.profit)"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right: Kesehatan Pelanggan Bulanan (Subscription Health) -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-200 dark:border-zinc-800">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Kesehatan Pelanggan Bulanan</h3>
                            <p class="text-[11px] sm:text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">Status pelunasan & metrik ARPU</p>
                        </div>
                        <a href="{{ route('pos.monthly') }}" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-emerald-700 dark:hover:text-emerald-400 underline-offset-2 hover:underline">Kelola Tagihan &rarr;</a>
                    </div>

                    <div class="grid grid-cols-2 gap-4 py-4 border-b border-zinc-200 dark:border-zinc-800">
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700">
                            <span class="text-[11px] font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Total Pelanggan Aktif</span>
                            <div class="text-xl font-bold font-mono text-zinc-900 dark:text-zinc-100 mt-1" x-text="(overview.subscription_health?.total_active || 0) + ' Pelanggan'"></div>
                        </div>
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700">
                            <span class="text-[11px] font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">ARPU (Rata-rata Pendapatan)</span>
                            <div class="text-xl font-bold font-mono text-emerald-700 dark:text-emerald-400 mt-1" x-text="formatRupiah(overview.arpu || 0) + ' /usr'"></div>
                        </div>
                    </div>

                    <div class="space-y-3 pt-4 text-xs">
                        <div>
                            <div class="flex justify-between font-semibold mb-1">
                                <span class="text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tagihan Lunas
                                </span>
                                <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="(overview.subscription_health?.paid_count || 0) + ' Tagihan'"></span>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between font-semibold mb-1">
                                <span class="text-amber-800 dark:text-amber-300 flex items-center gap-1.5 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Tagihan Angsuran
                                </span>
                                <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="(overview.subscription_health?.partial_count || 0) + ' Tagihan'"></span>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between font-semibold mb-1">
                                <span class="text-rose-700 dark:text-rose-400 flex items-center gap-1.5 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Belum Bayar Sama Sekali
                                </span>
                                <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="(overview.subscription_health?.unpaid_count || 0) + ' Tagihan'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 mt-2">
                    <a href="{{ route('pos.monthly') }}" class="w-full py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 font-semibold text-xs text-center block transition-colors shadow-xs">
                        Buka Daftar Tagihan & Pelanggan
                    </a>
                </div>
            </div>
        </div>

        <!-- Live Cash Inflow Feed (Riwayat Transaksi Masuk Terkini) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left: Voucher Activations -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-200 dark:border-zinc-800">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Aktivasi Voucher Terbaru</h3>
                        <p class="text-[11px] sm:text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">Omzet langsung masuk saat voucher pertama login</p>
                    </div>
                    <a href="{{ route('pos.vouchers') }}" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-emerald-700 dark:hover:text-emerald-400 underline-offset-2 hover:underline">Semua &rarr;</a>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/80 mt-2">
                    @forelse($recentVouchers as $v)
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100">{{ $v->username }}</div>
                                <div class="text-[11px] text-zinc-600 dark:text-zinc-400 font-medium">{{ $v->profile_name }} • Diaktifkan {{ \App\Support\FormatHelper::formatDateTimeShort($v->activated_at) }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100">{{ \App\Support\FormatHelper::formatRupiah($v->selling_price) }}</div>
                                <div class="text-[11px] text-emerald-700 dark:text-emerald-400 font-semibold">Laba: {{ \App\Support\FormatHelper::formatRupiah($v->profit) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs space-y-1">
                            <p class="font-medium text-zinc-700 dark:text-zinc-300">Belum ada voucher yang teraktivasi pada periode ini.</p>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Omzet voucher otomatis tercatat saat pengguna pertama kali login di hotspot.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right: Pembayaran Bulanan Terbaru -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-200 dark:border-zinc-800">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Pembayaran Tagihan Bulanan Masuk</h3>
                        <p class="text-[11px] sm:text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">Angsuran & pelunasan tagihan pelanggan</p>
                    </div>
                    <a href="{{ route('pos.monthly') }}" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:text-emerald-700 dark:hover:text-emerald-400 underline-offset-2 hover:underline">Semua &rarr;</a>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/80 mt-2">
                    @forelse($recentPayments as $p)
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $p->customer->name ?? 'Pelanggan' }}</div>
                                <div class="text-[11px] text-zinc-600 dark:text-zinc-400 font-medium">
                                    Periode {{ \App\Support\FormatHelper::formatMonthIndo(\Carbon\Carbon::parse($p->billing_month)) }} • <span class="uppercase font-semibold text-zinc-800 dark:text-zinc-200">{{ $p->payment_method }}</span>
                                    @if($p->notes) <span class="italic text-zinc-500 dark:text-zinc-400">({{ $p->notes }})</span> @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-bold text-emerald-700 dark:text-emerald-400">{{ \App\Support\FormatHelper::formatRupiah($p->amount_paid) }}</div>
                                <span class="text-[11px] text-zinc-600 dark:text-zinc-400 font-mono">{{ \App\Support\FormatHelper::formatDateTimeShort($p->paid_at) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs space-y-1">
                            <p class="font-medium text-zinc-700 dark:text-zinc-300">Belum ada pembayaran bulanan yang dicatat pada periode ini.</p>
                            <a href="{{ route('pos.monthly') }}" class="inline-block text-[11px] text-emerald-700 dark:text-emerald-400 hover:underline font-semibold mt-1">
                                Kelola tagihan pelanggan di menu Tagihan Bulanan &rarr;
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function posDashboard() {
            return {
                selectedMonth: '{{ $overview["month_value"] ?? now()->format("Y-m") }}',
                overview: @json($overview ?? []),
                trendChart: null,
                shareChart: null,
                methodChart: null,
                themeObserver: null,

                get hasRevenueData() {
                    const vRev = parseFloat(this.overview.voucher_revenue) || 0;
                    const mRev = parseFloat(this.overview.monthly_paid) || 0;
                    return (vRev + mRev) > 0;
                },

                get hasMethodData() {
                    const cash = parseFloat(this.overview.payment_methods?.cash) || 0;
                    const transfer = parseFloat(this.overview.payment_methods?.transfer) || 0;
                    const qris = parseFloat(this.overview.payment_methods?.qris) || 0;
                    return (cash + transfer + qris) > 0;
                },

                init() {
                    this.$nextTick(() => {
                        this.initCharts();
                    });

                    // Dark mode reactivity observer (Antislop C-4 Resilience)
                    this.themeObserver = new MutationObserver((mutations) => {
                        for (const m of mutations) {
                            if (m.type === 'attributes' && m.attributeName === 'class') {
                                this.updateChartsTheme();
                            }
                        }
                    });
                    this.themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
                },

                changeMonth() {
                    window.location.href = `{{ route('pos.index') }}?month=${this.selectedMonth}`;
                },

                setThisMonth() {
                    const now = new Date();
                    const m = (now.getMonth() + 1).toString().padStart(2, '0');
                    this.selectedMonth = `${now.getFullYear()}-${m}`;
                    this.changeMonth();
                },

                initCharts() {
                    if (typeof ApexCharts === 'undefined') return;

                    const isDark = document.documentElement.classList.contains('dark');
                    const textColor = isDark ? '#f4f4f5' : '#18181b';
                    const labelColor = isDark ? '#d4d4d8' : '#3f3f46';
                    const gridColor = isDark ? '#3f3f46' : '#e4e4e7';

                    // 1. Daily Trend Area Chart
                    const trendEl = document.querySelector("#posDailyTrendChart");
                    if (trendEl) {
                        const trendOptions = {
                            series: [
                                {
                                    name: 'Omzet Voucher',
                                    data: this.overview.chart_voucher_series || []
                                },
                                {
                                    name: 'Pembayaran Bulanan',
                                    data: this.overview.chart_monthly_series || []
                                },
                                {
                                    name: 'Laba Bersih',
                                    data: this.overview.chart_profit_series || []
                                }
                            ],
                            chart: {
                                type: 'area',
                                height: 280,
                                toolbar: { show: false },
                                zoom: { enabled: false },
                                background: 'transparent'
                            },
                            colors: ['#f59e0b', '#3b82f6', '#10b981'],
                            dataLabels: { enabled: false },
                            stroke: { curve: 'smooth', width: 2 },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.35,
                                    opacityTo: 0.05,
                                    stops: [0, 95, 100]
                                }
                            },
                            xaxis: {
                                categories: this.overview.chart_categories || [],
                                labels: {
                                    style: { colors: labelColor, fontSize: '11px', fontWeight: 500 },
                                    rotate: -45,
                                    rotateAlways: false
                                },
                                axisBorder: { show: false },
                                axisTicks: { show: false }
                            },
                            yaxis: {
                                labels: {
                                    style: { colors: labelColor, fontSize: '11px', fontWeight: 500 },
                                    formatter: val => 'Rp ' + (val >= 1000000 ? (val / 1000000).toFixed(1) + 'M' : (val >= 1000 ? (val / 1000).toFixed(0) + 'k' : val))
                                }
                            },
                            grid: { borderColor: gridColor, strokeDashArray: 3 },
                            tooltip: {
                                theme: isDark ? 'dark' : 'light',
                                y: { formatter: val => window.formatRupiah(val) }
                            },
                            legend: { show: false }
                        };
                        this.trendChart = new ApexCharts(trendEl, trendOptions);
                        this.trendChart.render();
                    }

                    // 2. Revenue Share Donut Chart (Render ONLY when real data exists - Antislop R-38 / C-5)
                    const shareEl = document.querySelector("#posRevenueShareChart");
                    if (shareEl && this.hasRevenueData) {
                        const vRev = parseFloat(this.overview.voucher_revenue) || 0;
                        const mRev = parseFloat(this.overview.monthly_paid) || 0;

                        const shareOptions = {
                            series: [vRev, mRev],
                            labels: ['Voucher Hotspot', 'Tagihan Bulanan'],
                            chart: { type: 'donut', height: 180, background: 'transparent' },
                            colors: ['#f59e0b', '#3b82f6'],
                            dataLabels: { enabled: false },
                            legend: {
                                position: 'bottom',
                                fontSize: '12px',
                                fontWeight: 500,
                                labels: { colors: textColor }
                            },
                            tooltip: {
                                theme: isDark ? 'dark' : 'light',
                                y: { formatter: val => window.formatRupiah(val) }
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '70%',
                                        labels: {
                                            show: true,
                                            total: {
                                                show: true,
                                                label: 'Total',
                                                color: labelColor,
                                                fontSize: '12px',
                                                fontWeight: 600,
                                                formatter: () => window.formatRupiah(vRev + mRev)
                                            },
                                            value: {
                                                color: textColor,
                                                fontSize: '15px',
                                                fontWeight: 700
                                            }
                                        }
                                    }
                                }
                            }
                        };
                        this.shareChart = new ApexCharts(shareEl, shareOptions);
                        this.shareChart.render();
                    }

                    // 3. Payment Method Donut Chart (Render ONLY when real data exists - Antislop R-38 / C-5)
                    const methodEl = document.querySelector("#posPaymentMethodChart");
                    if (methodEl && this.hasMethodData) {
                        const cash = parseFloat(this.overview.payment_methods?.cash) || 0;
                        const transfer = parseFloat(this.overview.payment_methods?.transfer) || 0;
                        const qris = parseFloat(this.overview.payment_methods?.qris) || 0;

                        const methodOptions = {
                            series: [cash, transfer, qris],
                            labels: ['Tunai / Cash', 'Transfer Bank', 'QRIS'],
                            chart: { type: 'donut', height: 180, background: 'transparent' },
                            colors: ['#10b981', '#6366f1', '#ec4899'],
                            dataLabels: { enabled: false },
                            legend: {
                                position: 'bottom',
                                fontSize: '12px',
                                fontWeight: 500,
                                labels: { colors: textColor }
                            },
                            tooltip: {
                                theme: isDark ? 'dark' : 'light',
                                y: { formatter: val => window.formatRupiah(val) }
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '70%',
                                        labels: {
                                            show: true,
                                            total: {
                                                show: true,
                                                label: 'Total',
                                                color: labelColor,
                                                fontSize: '12px',
                                                fontWeight: 600,
                                                formatter: () => window.formatRupiah(cash + transfer + qris)
                                            },
                                            value: {
                                                color: textColor,
                                                fontSize: '15px',
                                                fontWeight: 700
                                            }
                                        }
                                    }
                                }
                            }
                        };
                        this.methodChart = new ApexCharts(methodEl, methodOptions);
                        this.methodChart.render();
                    }
                },

                updateChartsTheme() {
                    if (typeof ApexCharts === 'undefined') return;
                    const isDark = document.documentElement.classList.contains('dark');
                    const textColor = isDark ? '#f4f4f5' : '#18181b';
                    const labelColor = isDark ? '#d4d4d8' : '#3f3f46';
                    const gridColor = isDark ? '#3f3f46' : '#e4e4e7';

                    if (this.trendChart) {
                        this.trendChart.updateOptions({
                            xaxis: {
                                labels: { style: { colors: labelColor } }
                            },
                            yaxis: {
                                labels: { style: { colors: labelColor } }
                            },
                            grid: { borderColor: gridColor },
                            tooltip: { theme: isDark ? 'dark' : 'light' }
                        });
                    }

                    if (this.shareChart) {
                        this.shareChart.updateOptions({
                            legend: { labels: { colors: textColor } },
                            tooltip: { theme: isDark ? 'dark' : 'light' },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        labels: {
                                            total: { color: labelColor },
                                            value: { color: textColor }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    if (this.methodChart) {
                        this.methodChart.updateOptions({
                            legend: { labels: { colors: textColor } },
                            tooltip: { theme: isDark ? 'dark' : 'light' },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        labels: {
                                            total: { color: labelColor },
                                            value: { color: textColor }
                                        }
                                    }
                                }
                            }
                        });
                    }
                },

                formatRupiah(num) {
                    return window.formatRupiah ? window.formatRupiah(num || 0) : 'Rp ' + Number(num || 0).toLocaleString('id-ID');
                },

                formatNumber(num) {
                    return window.formatNumber ? window.formatNumber(num || 0) : Number(num || 0).toLocaleString('id-ID');
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>