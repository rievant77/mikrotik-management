<x-layouts.app>
    <x-slot:header>Historical Usage Analytics</x-slot:header>

    <div class="space-y-6" x-data="historicalAnalytics()">
        <!-- Title and Filter Toolbar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Histori Pemakaian Kuota & Bandwidth</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                        {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Laporan analitik pemakaian data user aktual dari router MikroTik dan database harian</p>
            </div>

            <!-- Action & Export Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Preset Buttons -->
                <div class="inline-flex rounded-lg border border-zinc-200 dark:border-zinc-700 p-0.5 bg-zinc-100 dark:bg-zinc-800 text-xs">
                    <button @click="setPeriod('today')" :class="period === 'today' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-xs font-semibold' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400'" class="px-2.5 py-1.5 rounded-md transition-colors">Hari Ini</button>
                    <button @click="setPeriod('yesterday')" :class="period === 'yesterday' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-xs font-semibold' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400'" class="px-2.5 py-1.5 rounded-md transition-colors">Kemarin</button>
                    <button @click="setPeriod('7days')" :class="period === '7days' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-xs font-semibold' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400'" class="px-2.5 py-1.5 rounded-md transition-colors">7 Hari</button>
                    <button @click="setPeriod('30days')" :class="period === '30days' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-xs font-semibold' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400'" class="px-2.5 py-1.5 rounded-md transition-colors">30 Hari</button>
                    <button @click="setPeriod('this_month')" :class="period === 'this_month' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-xs font-semibold' : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400'" class="px-2.5 py-1.5 rounded-md transition-colors">Bulan Ini</button>
                </div>

                <!-- Export CSV -->
                <a :href="'/users/historical?period=' + period + '&start_date=' + customStart + '&end_date=' + customEnd + '&export=csv'" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 shadow-xs transition-colors">
                    <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Ekspor CSV</span>
                </a>
            </div>
        </div>

        <!-- Custom Date Range Form Drawer (Collapsible) -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 shadow-xs">
            <form @submit.prevent="applyCustomRange()" class="flex flex-wrap items-center gap-3 text-xs">
                <span class="font-medium text-zinc-700 dark:text-zinc-300">Rentang Kustom:</span>
                <div class="flex items-center gap-2">
                    <label class="text-zinc-400 text-[11px]">Dari:</label>
                    <input type="date" x-model="customStart" class="px-2.5 py-1 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-zinc-400 text-[11px]">Sampai:</label>
                    <input type="date" x-model="customEnd" class="px-2.5 py-1 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                </div>
                <button type="submit" class="px-3 py-1 bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-semibold rounded-lg hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors shadow-xs">
                    Terapkan Rentang
                </button>
            </form>
        </div>

        <!-- Summary Aggregated Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <span class="text-xs text-zinc-400 uppercase font-medium">Total Konsumsi (Rx + Tx)</span>
                <h4 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1 font-mono">{{ \App\Support\FormatHelper::formatBytes($totalDataPeriod, 1) }}</h4>
                <div class="flex items-center justify-between text-[11px] text-zinc-400 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">Rx: {{ \App\Support\FormatHelper::formatBytes(collect($summaries)->sum('total_out'), 1) }}</span>
                    <span class="text-sky-600 dark:text-sky-400 font-medium">Tx: {{ \App\Support\FormatHelper::formatBytes(collect($summaries)->sum('total_in'), 1) }}</span>
                </div>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <span class="text-xs text-zinc-400 uppercase font-medium">Total Sesi Tercatat</span>
                <h4 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ number_format($totalSessionsPeriod ?? 0) }} Sesi</h4>
                <p class="text-[11px] text-zinc-400 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">Akumulasi login koneksi</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <span class="text-xs text-zinc-400 uppercase font-medium">Pengguna Unik Aktif</span>
                <h4 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ number_format($uniqueUsersCount ?? 0) }} User</h4>
                <p class="text-[11px] text-zinc-400 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">Hotspot Voucher + Member</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <span class="text-xs text-zinc-400 uppercase font-medium">Rata-rata Kuota / User</span>
                <h4 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1 font-mono">
                    {{ \App\Support\FormatHelper::formatBytes($uniqueUsersCount > 0 ? ($totalDataPeriod / $uniqueUsersCount) : 0, 1) }}
                </h4>
                <p class="text-[11px] text-zinc-400 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">Konsumsi rata-rata</p>
            </div>
        </div>

        <!-- Daily / Hourly Usage Trend Chart -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800 gap-2">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                        @if($isHourlyChart ?? false)
                            Grafik Konsumsi Data Per-Jam (24 Jam: 00:00 - 23:00)
                        @else
                            Tren Konsumsi Data Harian (MB)
                        @endif
                    </h3>
                    <p class="text-[11px] text-zinc-400 mt-0.5">
                        @if($isHourlyChart ?? false)
                            Distribusi volume data unduh (Rx) dan unggah (Tx) per-jam pada tanggal {{ $startDate->format('d M Y') }}
                        @else
                            Grafik volume data unduh (Rx) dan unggah (Tx) per tanggal
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Download (Rx)
                    </span>
                    <span class="inline-flex items-center gap-1 text-sky-600 dark:text-sky-400 font-medium">
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span> Upload (Tx)
                    </span>
                </div>
            </div>
            <div class="mt-4">
                <div id="historical-trend-chart" class="w-full h-64"></div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Search Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Cari user..." 
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                    >
                </div>

                <!-- Profile Filter Dropdown -->
                <div>
                    <select 
                        x-model="selectedProfile" 
                        class="w-full px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                    >
                        <option value="">Semua Profile / Paket</option>
                        @foreach($profiles as $profile)
                            <option value="{{ $profile->name }}">{{ $profile->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort By Dropdown -->
                <div>
                    <select 
                        x-model="sortBy" 
                        class="w-full px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                    >
                        <option value="usage_desc">Total Pemakaian Terbanyak</option>
                        <option value="rx_desc">Download (Rx) Terbanyak</option>
                        <option value="tx_desc">Upload (Tx) Terbanyak</option>
                        <option value="uptime_desc">Uptime Terlama</option>
                        <option value="username_asc">Username (A-Z)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <template x-if="filteredSummaries.length === 0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Belum Ada Histori Pemakaian</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Tidak ada data transaksi atau pemakaian data yang sesuai dengan filter periode yang dipilih.</p>
            </div>
        </template>

        <!-- Historical Breakdown Table -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs" x-show="filteredSummaries.length > 0">
            <div class="px-5 py-3.5 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/20">
                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                    Daftar Pengguna (<span x-text="filteredSummaries.length"></span>)
                </span>
                <span class="text-[11px] text-zinc-400">Diurutkan berdasarkan pemakaian aktual</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3.5 px-4">User & Profile</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Jumlah Sesi</th>
                            <th class="py-3.5 px-4">Total Uptime</th>
                            <th class="py-3.5 px-4">Upload (Tx)</th>
                            <th class="py-3.5 px-4">Download (Rx)</th>
                            <th class="py-3.5 px-4 font-bold">Total Pemakaian</th>
                            <th class="py-3.5 px-4 text-right">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <template x-for="item in filteredSummaries" :key="item.username">
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <!-- User & Profile -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-bold text-xs flex items-center justify-center">
                                            <span x-text="item.username.substring(0,2).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <a :href="'/users/' + item.username" class="font-semibold text-zinc-900 dark:text-zinc-100 hover:underline" x-text="item.username"></a>
                                            <div class="text-[10px] text-zinc-400" x-text="item.profile_name || 'Standard'"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Online/Offline -->
                                <td class="py-3.5 px-4">
                                    <template x-if="item.is_online">
                                        <x-badge type="active" dot="true">Online</x-badge>
                                    </template>
                                    <template x-if="!item.is_online">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                                            Offline
                                        </span>
                                    </template>
                                </td>

                                <!-- Sessions Count -->
                                <td class="py-3.5 px-4 font-medium text-zinc-700 dark:text-zinc-300" x-text="(item.total_sessions || 1) + ' Sesi'"></td>

                                <!-- Total Uptime -->
                                <td class="py-3.5 px-4 font-mono text-zinc-500" x-text="item.total_uptime_formatted || '-'"></td>

                                <!-- Upload (Tx) -->
                                <td class="py-3.5 px-4 font-mono font-medium text-sky-600 dark:text-sky-400 whitespace-nowrap" x-text="'↑ ' + (item.total_in_formatted || '0 B')"></td>

                                <!-- Download (Rx) -->
                                <td class="py-3.5 px-4 font-mono font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap" x-text="'↓ ' + (item.total_out_formatted || '0 B')"></td>

                                <!-- Total Usage with Ratio Bar -->
                                <td class="py-3.5 px-4 font-mono">
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100" x-text="item.total_usage_formatted || '0 B'"></div>
                                    <div class="w-24 bg-zinc-100 dark:bg-zinc-800 h-1.5 rounded-full overflow-hidden mt-1">
                                        <div class="bg-zinc-900 dark:bg-zinc-100 h-full rounded-full" :style="'width: ' + Math.min(100, Math.round((item.total_usage / (totalData || 1)) * 100)) + '%'"></div>
                                    </div>
                                </td>

                                <!-- Action Link -->
                                <td class="py-3.5 px-4 text-right">
                                    <a :href="'/users/' + item.username" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function historicalAnalytics() {
            return {
                period: '{{ $period ?? "7days" }}',
                customStart: '{{ $startDate->format("Y-m-d") }}',
                customEnd: '{{ $endDate->format("Y-m-d") }}',
                search: '',
                selectedProfile: '',
                sortBy: 'usage_desc',
                summaries: @json($summaries ?? []),
                totalData: {{ $totalDataPeriod ?? 0 }},
                totalSessions: {{ $totalSessionsPeriod ?? 0 }},
                uniqueUsers: {{ $uniqueUsersCount ?? 0 }},
                chartCategories: @json($chartCategories ?? []),
                chartRxSeries: @json($chartRxSeries ?? []),
                chartTxSeries: @json($chartTxSeries ?? []),
                init() {
                    this.renderTrendChart();
                },
                renderTrendChart() {
                    const el = document.getElementById('historical-trend-chart');
                    if (!el || typeof ApexCharts === 'undefined') return;

                    const isDark = document.documentElement.classList.contains('dark');

                    const options = {
                        chart: {
                            type: 'area',
                            height: 240,
                            toolbar: { show: false },
                            zoom: { enabled: false },
                            fontFamily: 'inherit',
                            background: 'transparent',
                        },
                        colors: ['#10b981', '#0284c7'],
                        dataLabels: { enabled: false },
                        stroke: {
                            curve: 'smooth',
                            width: [2, 2],
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.35,
                                opacityTo: 0.05,
                                stops: [0, 90, 100]
                            }
                        },
                        series: [
                            {
                                name: 'Download (Rx)',
                                data: this.chartRxSeries
                            },
                            {
                                name: 'Upload (Tx)',
                                data: this.chartTxSeries
                            }
                        ],
                        xaxis: {
                            categories: this.chartCategories,
                            labels: {
                                style: {
                                    colors: isDark ? '#71717a' : '#a1a1aa',
                                    fontSize: '11px'
                                }
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: {
                                formatter: function (val) {
                                    if (val >= 1024) return (val / 1024).toFixed(1) + ' GB';
                                    return Math.round(val) + ' MB';
                                },
                                style: {
                                    colors: isDark ? '#71717a' : '#a1a1aa',
                                    fontSize: '11px'
                                }
                            }
                        },
                        grid: {
                            borderColor: isDark ? '#27272a' : '#f4f4f5',
                            strokeDashArray: 4,
                        },
                        tooltip: {
                            theme: isDark ? 'dark' : 'light',
                            y: {
                                formatter: function (val) {
                                    if (val >= 1024) return (val / 1024).toFixed(2) + ' GB';
                                    return val.toFixed(2) + ' MB';
                                }
                            }
                        },
                        legend: { show: false }
                    };

                    const chart = new ApexCharts(el, options);
                    chart.render();
                },
                get filteredSummaries() {
                    let list = this.summaries.filter(s => {
                        const name = s.username || '';
                        const profile = s.profile_name || '';
                        const matchesSearch = !this.search || name.toLowerCase().includes(this.search.toLowerCase());
                        const matchesProfile = !this.selectedProfile || profile === this.selectedProfile;
                        return matchesSearch && matchesProfile;
                    });

                    if (this.sortBy === 'usage_desc') {
                        list.sort((a, b) => (b.total_usage || 0) - (a.total_usage || 0));
                    } else if (this.sortBy === 'rx_desc') {
                        list.sort((a, b) => (b.total_out || 0) - (a.total_out || 0));
                    } else if (this.sortBy === 'tx_desc') {
                        list.sort((a, b) => (b.total_in || 0) - (a.total_in || 0));
                    } else if (this.sortBy === 'uptime_desc') {
                        list.sort((a, b) => (b.total_uptime || 0) - (a.total_uptime || 0));
                    } else if (this.sortBy === 'username_asc') {
                        list.sort((a, b) => (a.username || '').localeCompare(b.username || ''));
                    }

                    return list;
                },
                setPeriod(p) {
                    window.location.href = `/users/historical?period=${p}`;
                },
                applyCustomRange() {
                    if (this.customStart && this.customEnd) {
                        window.location.href = `/users/historical?period=custom&start_date=${this.customStart}&end_date=${this.customEnd}`;
                    }
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
