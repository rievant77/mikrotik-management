<x-layouts.app>
    <x-slot:header>Dashboard Finansial & POS</x-slot:header>

    <div class="space-y-6" x-data="posDashboard()">
        <!-- Header & Top Navigation Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Dashboard Finansial & POS</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Kalkulasi terpadu omzet, modal operasional, laba bersih, dan arus kas bisnis hotspot</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Month Filter -->
                <div class="flex items-center gap-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-1 shadow-xs">
                    <input 
                        type="month" 
                        x-model="selectedMonth" 
                        @change="changeMonth()" 
                        class="px-2.5 py-1 text-xs font-bold font-mono rounded bg-transparent border-0 text-zinc-900 dark:text-zinc-100 focus:ring-0"
                    >
                    <button type="button" @click="setThisMonth()" class="px-2.5 py-1 text-xs font-semibold rounded bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors">
                        Bulan Ini
                    </button>
                </div>

                <a href="{{ route('pos.monthly') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Tagihan Bulanan</span>
                </a>
                <a href="{{ route('pos.vouchers') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    <span>Rekap Voucher</span>
                </a>
            </div>
        </div>

        <!-- 4 Key Executive Financial KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Total Pemasukan Kas / Gross Inflow -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Total Pemasukan Kas</span>
                    <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono" x-text="formatRupiah(overview.total_gross_income)"></div>
                <div class="mt-2 text-[11px] text-zinc-500 dark:text-zinc-400 flex flex-col gap-0.5">
                    <div class="flex justify-between">
                        <span>Voucher Terpakai:</span>
                        <strong class="font-mono text-zinc-700 dark:text-zinc-300" x-text="formatRupiah(overview.voucher_revenue)"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Bulanan Masuk:</span>
                        <strong class="font-mono text-zinc-700 dark:text-zinc-300" x-text="formatRupiah(overview.monthly_paid)"></strong>
                    </div>
                </div>
            </div>

            <!-- 2. Total Laba Bersih / Net Profit -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Total Laba Bersih</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20" x-text="'Margin ' + (overview.overall_profit_margin || 0) + '%'"></span>
                </div>
                <div class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono" x-text="formatRupiah(overview.total_net_profit)"></div>
                <div class="mt-2 text-[11px] text-zinc-500 dark:text-zinc-400 flex flex-col gap-0.5">
                    <div class="flex justify-between">
                        <span>Laba Voucher:</span>
                        <strong class="font-mono text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(overview.voucher_profit)"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Laba Bulanan:</span>
                        <strong class="font-mono text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(overview.monthly_profit)"></strong>
                    </div>
                </div>
            </div>

            <!-- 3. Kinerja Omzet & Profit Voucher -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Kinerja Voucher</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20" x-text="'Margin ' + (overview.voucher_margin || 0) + '%'"></span>
                </div>
                <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono" x-text="formatRupiah(overview.voucher_revenue)"></div>
                <div class="mt-2 text-[11px] text-zinc-500 dark:text-zinc-400 flex flex-col gap-0.5">
                    <div class="flex justify-between">
                        <span>Voucher Terpakai:</span>
                        <strong class="font-mono text-zinc-700 dark:text-zinc-300" x-text="(overview.voucher_count || 0) + ' Unit'"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Laba Bersih Voucher:</span>
                        <strong class="font-mono text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(overview.voucher_profit)"></strong>
                    </div>
                </div>
            </div>

            <!-- 4. Kinerja Omzet & Profit Bulanan -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Kinerja Bulanan</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-500/20" x-text="'Lunas ' + (overview.monthly_collection_rate || 0) + '%'"></span>
                </div>
                <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono" x-text="formatRupiah(overview.monthly_paid)"></div>
                <div class="mt-2 text-[11px] text-zinc-500 dark:text-zinc-400 flex flex-col gap-0.5">
                    <div class="flex justify-between">
                        <span>Laba Bersih Bulanan:</span>
                        <strong class="font-mono text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(overview.monthly_profit)"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Sisa Piutang:</span>
                        <strong class="font-mono text-rose-600 dark:text-rose-400" x-text="formatRupiah(overview.monthly_unpaid)"></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visual Analytics Grid: Daily Revenue Trend (Area Chart) & Breakdown (Donut Charts) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Chart (2 Columns): Daily Revenue & Profit Trend -->
            <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Tren Arus Kas & Laba Harian</h3>
                        <p class="text-[11px] text-zinc-400">Komparasi omzet voucher, pembayaran bulanan, dan laba harian (<span x-text="overview.period"></span>)</p>
                    </div>
                    <div class="flex items-center gap-3 text-[11px]">
                        <span class="flex items-center gap-1.5 text-zinc-600 dark:text-zinc-400 font-medium">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Voucher
                        </span>
                        <span class="flex items-center gap-1.5 text-zinc-600 dark:text-zinc-400 font-medium">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Bulanan
                        </span>
                        <span class="flex items-center gap-1.5 text-emerald-600 font-bold">
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
                    <div class="pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Proporsi Sumber Omzet</h3>
                        <p class="text-[11px] text-zinc-400">Perbandingan kontribusi Voucher vs Bulanan</p>
                    </div>
                    <div class="pt-3">
                        <div id="posRevenueShareChart" class="w-full flex justify-center"></div>
                    </div>
                </div>

                <!-- Donut 2: Payment Method Breakdown -->
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                    <div class="pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Metode Pembayaran</h3>
                        <p class="text-[11px] text-zinc-400">Cash / Tunai vs Transfer Bank vs QRIS</p>
                    </div>
                    <div class="pt-3">
                        <div id="posPaymentMethodChart" class="w-full flex justify-center"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Leaderboard & Subscription Health Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left: Top 5 Paket Voucher Terlaris -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Top 5 Paket Voucher Terlaris</h3>
                        <p class="text-[11px] text-zinc-400">Profil voucher dengan penjualan & profit tertinggi</p>
                    </div>
                    <a href="{{ route('pos.vouchers') }}" class="text-xs text-zinc-600 dark:text-zinc-400 hover:text-emerald-600">Semua &rarr;</a>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/60 mt-2">
                    <template x-if="!overview.top_profiles || overview.top_profiles.length === 0">
                        <div class="py-8 text-center text-xs text-zinc-400">
                            Belum ada voucher yang teraktivasi pada periode ini.
                        </div>
                    </template>
                    <template x-for="(p, idx) in (overview.top_profiles || [])" :key="p.profile_name">
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold font-mono" :class="idx === 0 ? 'bg-amber-500/20 text-amber-600' : (idx === 1 ? 'bg-zinc-200 text-zinc-700' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500')" x-text="idx + 1"></span>
                                <div>
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100 font-mono" x-text="p.profile_name"></div>
                                    <div class="text-[10px] text-zinc-400" x-text="p.count + ' Voucher Terjual'"></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="formatRupiah(p.revenue)"></div>
                                <div class="text-[10px] text-emerald-600 font-semibold" x-text="'Laba: ' + formatRupiah(p.profit)"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right: Kesehatan Pelanggan Bulanan (Subscription Health) -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Kesehatan Pelanggan Bulanan</h3>
                            <p class="text-[11px] text-zinc-400">Status pelunasan & metrik ARPU</p>
                        </div>
                        <a href="{{ route('pos.monthly') }}" class="text-xs text-zinc-600 dark:text-zinc-400 hover:text-emerald-600">Kelola Tagihan &rarr;</a>
                    </div>

                    <div class="grid grid-cols-2 gap-4 py-4 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/60 dark:border-zinc-700/60">
                            <span class="text-[10px] font-bold text-zinc-400 uppercase">Total Pelanggan Aktif</span>
                            <div class="text-xl font-bold font-mono text-zinc-900 dark:text-zinc-100 mt-1" x-text="(overview.subscription_health?.total_active || 0) + ' Pelanggan'"></div>
                        </div>
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/60 dark:border-zinc-700/60">
                            <span class="text-[10px] font-bold text-zinc-400 uppercase">ARPU (Rata-rata Pendapatan)</span>
                            <div class="text-xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1" x-text="formatRupiah(overview.arpu || 0) + ' /usr'"></div>
                        </div>
                    </div>

                    <div class="space-y-3 pt-4 text-xs">
                        <div>
                            <div class="flex justify-between font-semibold mb-1">
                                <span class="text-emerald-600 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Tagihan Lunas
                                </span>
                                <span class="font-mono text-zinc-700 dark:text-zinc-300" x-text="(overview.subscription_health?.paid_count || 0) + ' Tagihan'"></span>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between font-semibold mb-1">
                                <span class="text-amber-600 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Tagihan Angsuran
                                </span>
                                <span class="font-mono text-zinc-700 dark:text-zinc-300" x-text="(overview.subscription_health?.partial_count || 0) + ' Tagihan'"></span>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between font-semibold mb-1">
                                <span class="text-rose-600 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Belum Bayar Sama Sekali
                                </span>
                                <span class="font-mono text-zinc-700 dark:text-zinc-300" x-text="(overview.subscription_health?.unpaid_count || 0) + ' Tagihan'"></span>
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
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Aktivasi Voucher Terbaru</h3>
                        <p class="text-[11px] text-zinc-400">Omzet langsung masuk saat voucher pertama login</p>
                    </div>
                    <a href="{{ route('pos.vouchers') }}" class="text-xs text-zinc-600 dark:text-zinc-400 hover:text-emerald-600">Semua &rarr;</a>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/60 mt-2">
                    @forelse($recentVouchers as $v)
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100">{{ $v->username }}</div>
                                <div class="text-[10px] text-zinc-400">{{ $v->profile_name }} • Diaktifkan {{ $v->activated_at }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($v->selling_price, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-emerald-600 font-semibold">Laba: Rp {{ number_format($v->profit, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-zinc-400">
                            Belum ada voucher yang diaktivasi.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right: Pembayaran Bulanan Terbaru -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Pembayaran Tagihan Bulanan Masuk</h3>
                        <p class="text-[11px] text-zinc-400">Angsuran & pelunasan tagihan pelanggan</p>
                    </div>
                    <a href="{{ route('pos.monthly') }}" class="text-xs text-zinc-600 dark:text-zinc-400 hover:text-emerald-600">Semua &rarr;</a>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/60 mt-2">
                    @forelse($recentPayments as $p)
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $p->customer->name ?? 'Pelanggan' }}</div>
                                <div class="text-[10px] text-zinc-400">
                                    Periode {{ \Carbon\Carbon::parse($p->billing_month)->format('M Y') }} • {{ strtoupper($p->payment_method) }}
                                    @if($p->notes) <span class="italic">({{ $p->notes }})</span> @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($p->amount_paid, 0, ',', '.') }}</div>
                                <span class="text-[10px] text-zinc-400 font-mono">{{ $p->paid_at }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-zinc-400">
                            Belum ada pembayaran bulanan yang dicatat.
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

                init() {
                    this.$nextTick(() => {
                        this.initCharts();
                    });
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
                    const textColor = isDark ? '#a1a1aa' : '#71717a';
                    const gridColor = isDark ? '#27272a' : '#f4f4f5';

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
                                    style: { colors: textColor, fontSize: '10px' },
                                    rotate: -45,
                                    rotateAlways: false
                                },
                                axisBorder: { show: false },
                                axisTicks: { show: false }
                            },
                            yaxis: {
                                labels: {
                                    style: { colors: textColor, fontSize: '10px' },
                                    formatter: val => 'Rp ' + (val >= 1000000 ? (val / 1000000).toFixed(1) + 'M' : (val >= 1000 ? (val / 1000).toFixed(0) + 'k' : val))
                                }
                            },
                            grid: { borderColor: gridColor, strokeDashArray: 4 },
                            tooltip: {
                                theme: isDark ? 'dark' : 'light',
                                y: { formatter: val => window.formatRupiah(val) }
                            },
                            legend: { show: false }
                        };
                        this.trendChart = new ApexCharts(trendEl, trendOptions);
                        this.trendChart.render();
                    }

                    // 2. Revenue Share Donut Chart
                    const shareEl = document.querySelector("#posRevenueShareChart");
                    if (shareEl) {
                        const vRev = parseFloat(this.overview.voucher_revenue) || 0;
                        const mRev = parseFloat(this.overview.monthly_paid) || 0;
                        const hasData = (vRev + mRev) > 0;

                        const shareOptions = {
                            series: hasData ? [vRev, mRev] : [1, 1],
                            labels: ['Voucher Hotspot', 'Tagihan Bulanan'],
                            chart: { type: 'donut', height: 180, background: 'transparent' },
                            colors: ['#f59e0b', '#3b82f6'],
                            dataLabels: { enabled: false },
                            legend: {
                                position: 'bottom',
                                fontSize: '11px',
                                labels: { colors: textColor }
                            },
                            tooltip: {
                                theme: isDark ? 'dark' : 'light',
                                y: { formatter: val => hasData ? window.formatRupiah(val) : '0' }
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
                                                color: textColor,
                                                formatter: () => hasData ? window.formatRupiah(vRev + mRev) : 'Rp 0'
                                            }
                                        }
                                    }
                                }
                            }
                        };
                        this.shareChart = new ApexCharts(shareEl, shareOptions);
                        this.shareChart.render();
                    }

                    // 3. Payment Method Donut Chart
                    const methodEl = document.querySelector("#posPaymentMethodChart");
                    if (methodEl) {
                        const cash = parseFloat(this.overview.payment_methods?.cash) || 0;
                        const transfer = parseFloat(this.overview.payment_methods?.transfer) || 0;
                        const qris = parseFloat(this.overview.payment_methods?.qris) || 0;
                        const hasMethodData = (cash + transfer + qris) > 0;

                        const methodOptions = {
                            series: hasMethodData ? [cash, transfer, qris] : [1, 0, 0],
                            labels: ['Tunai / Cash', 'Transfer Bank', 'QRIS'],
                            chart: { type: 'donut', height: 180, background: 'transparent' },
                            colors: ['#10b981', '#6366f1', '#ec4899'],
                            dataLabels: { enabled: false },
                            legend: {
                                position: 'bottom',
                                fontSize: '11px',
                                labels: { colors: textColor }
                            },
                            tooltip: {
                                theme: isDark ? 'dark' : 'light',
                                y: { formatter: val => hasMethodData ? window.formatRupiah(val) : '0' }
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
                                                color: textColor,
                                                formatter: () => hasMethodData ? window.formatRupiah(cash + transfer + qris) : 'Rp 0'
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

                formatRupiah(num) {
                    return window.formatRupiah(num || 0);
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
