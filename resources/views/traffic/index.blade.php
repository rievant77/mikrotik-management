<x-layouts.app>
    <x-slot:header>Analisis Trafik & Konten</x-slot:header>

    <div class="space-y-6" x-data="trafficAnalyticsManager(@js($data))">
        <!-- Header & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Analisis Trafik & Konten Aplikasi</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Klasifikasi pemakaian bandwidth user (Video Streaming, Sosial Media, Game Online, Cloud & Browsing)
                </p>
            </div>

            <!-- Action Controls -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Status Badge -->
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold"
                     :class="analytics.is_rules_installed ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800'">
                    <span class="w-2 h-2 rounded-full" :class="analytics.is_rules_installed ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                    <span x-text="analytics.is_rules_installed ? 'Mangle MikroTik Aktif' : 'Estimasi Sesi / Belum Deploy'"></span>
                </div>

                <!-- Deploy Rules Button -->
                <button 
                    type="button" 
                    @click="deployRules()" 
                    :disabled="isDeploying"
                    class="px-3.5 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-900 text-xs font-semibold transition-colors shadow-xs flex items-center gap-2"
                >
                    <svg class="w-4 h-4 text-emerald-400 dark:text-emerald-600" :class="{ 'animate-spin': isDeploying }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span x-text="isDeploying ? 'Memasang Filter...' : 'Pasang Filter di MikroTik'"></span>
                </button>

                <!-- Reset Counters Button -->
                <button 
                    type="button" 
                    @click="resetCounters()" 
                    :disabled="isResetting"
                    class="px-3.5 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition-colors flex items-center gap-2 border border-zinc-200 dark:border-zinc-700"
                    title="Reset counter pemakaian trafik ke 0 di MikroTik"
                >
                    <svg class="w-4 h-4 text-zinc-400" :class="{ 'animate-spin': isResetting }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Reset Counter</span>
                </button>
            </div>
        </div>

        <!-- KPI Metric Cards (4 Categories) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Video Streaming -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
                <div class="absolute top-0 left-0 h-1 w-full bg-rose-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Streaming Video</span>
                    <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-sm">
                        🎬
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" x-text="analytics.categories.video.formatted_bytes || '0 B'"></div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs font-semibold text-rose-600 dark:text-rose-400" x-text="(analytics.categories.video.percentage || 0) + '% dari total'"></span>
                        <span class="text-[11px] text-zinc-400">YouTube, TikTok, Netflix</span>
                    </div>
                </div>
            </div>

            <!-- 2. Social Media & Chat -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
                <div class="absolute top-0 left-0 h-1 w-full bg-blue-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Sosmed & Chat</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-sm">
                        💬
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" x-text="analytics.categories.social_media.formatted_bytes || '0 B'"></div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400" x-text="(analytics.categories.social_media.percentage || 0) + '% dari total'"></span>
                        <span class="text-[11px] text-zinc-400">WhatsApp, IG, FB, X</span>
                    </div>
                </div>
            </div>

            <!-- 3. Online Gaming -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
                <div class="absolute top-0 left-0 h-1 w-full bg-emerald-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Online Gaming</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                        🎮
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" x-text="analytics.categories.gaming.formatted_bytes || '0 B'"></div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400" x-text="(analytics.categories.gaming.percentage || 0) + '% dari total'"></span>
                        <span class="text-[11px] text-zinc-400">MLBB, Free Fire, PUBG</span>
                    </div>
                </div>
            </div>

            <!-- 4. Cloud, Work & Browsing -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs relative overflow-hidden">
                <div class="absolute top-0 left-0 h-1 w-full bg-amber-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Cloud & Browsing</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                        🌐
                    </div>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100" x-text="analytics.categories.browsing.formatted_bytes || '0 B'"></div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs font-semibold text-amber-600 dark:text-amber-400" x-text="(analytics.categories.browsing.percentage || 0) + '% dari total'"></span>
                        <span class="text-[11px] text-zinc-400">Google, Zoom, Web</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Charts Section (Donut + Hourly Area Trend) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Donut Chart: Persentase Kategori -->
            <div class="lg:col-span-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="pb-3 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Proporsi Kategori Trafik</h2>
                            <p class="text-xs text-zinc-500">Persentase pembagian pemakaian bandwidth</p>
                        </div>
                        <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300" x-text="'Total: ' + analytics.formatted_total_traffic"></span>
                    </div>

                    <div class="mt-4 flex items-center justify-center min-h-[280px]">
                        <div id="donutChart" class="w-full"></div>
                    </div>
                </div>

                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 grid grid-cols-2 gap-2 text-[11px]">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <span class="text-zinc-600 dark:text-zinc-400">Video (<span x-text="(analytics.categories.video.percentage || 0) + '%'"></span>)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        <span class="text-zinc-600 dark:text-zinc-400">Sosmed (<span x-text="(analytics.categories.social_media.percentage || 0) + '%'"></span>)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-zinc-600 dark:text-zinc-400">Gaming (<span x-text="(analytics.categories.gaming.percentage || 0) + '%'"></span>)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span class="text-zinc-600 dark:text-zinc-400">Web/Cloud (<span x-text="(analytics.categories.browsing.percentage || 0) + '%'"></span>)</span>
                    </div>
                </div>
            </div>

            <!-- Hourly Area Trend Chart -->
            <div class="lg:col-span-7 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="pb-3 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Tren Jam Sibuk per Kategori (24 Jam)</h2>
                            <p class="text-xs text-zinc-500">Volume konsumsi bandwidth (MB) per jam</p>
                        </div>
                        <span class="text-xs font-medium text-zinc-400">Hari Ini (00:00 - 23:00)</span>
                    </div>

                    <div class="mt-4 min-h-[280px]">
                        <div id="hourlyChart" class="w-full"></div>
                    </div>
                </div>

                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-400 flex items-center justify-between">
                    <span>💡 Puncak pemakaian video streaming biasanya terjadi pada pukul 19:00 - 22:00</span>
                </div>
            </div>
        </div>

        <!-- Detailed Applications & Platforms Table -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xs overflow-hidden">
            <div class="p-5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Rincian Aplikasi & Platform Terpopuler</h2>
                    <p class="text-xs text-zinc-500">Detail volume konsumsi data per aplikasi yang diakses pengguna hotspot</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300" x-text="analytics.platforms.length + ' Platform Terdeteksi'"></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 text-zinc-500 font-semibold">
                            <th class="py-3.5 px-4 sm:px-6">Platform / Aplikasi</th>
                            <th class="py-3.5 px-4">Kategori Konten</th>
                            <th class="py-3.5 px-4">Total Pemakaian</th>
                            <th class="py-3.5 px-4 w-48">Porsi Konsumsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                        <template x-for="(item, idx) in analytics.platforms" :key="item.platform">
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm shrink-0"
                                             :class="{
                                                 'bg-rose-500/10 text-rose-600 dark:text-rose-400': item.category_key === 'video',
                                                 'bg-blue-500/10 text-blue-600 dark:text-blue-400': item.category_key === 'social_media',
                                                 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400': item.category_key === 'gaming',
                                                 'bg-violet-500/10 text-violet-600 dark:text-violet-400': item.category_key === 'cloud_work',
                                                 'bg-amber-500/10 text-amber-600 dark:text-amber-400': item.category_key === 'browsing'
                                             }">
                                            <span x-text="item.category_key === 'video' ? '🎬' : (item.category_key === 'social_media' ? '💬' : (item.category_key === 'gaming' ? '🎮' : (item.category_key === 'cloud_work' ? '💼' : '🌐')))"></span>
                                        </div>
                                        <div>
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100 block" x-text="item.platform"></span>
                                            <span class="text-[11px] text-zinc-400" x-text="item.packets ? (item.packets + ' packets tercatat') : 'Aktif'"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold"
                                          :class="{
                                              'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400': item.category_key === 'video',
                                              'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400': item.category_key === 'social_media',
                                              'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400': item.category_key === 'gaming',
                                              'bg-violet-50 dark:bg-violet-950/40 text-violet-700 dark:text-violet-400': item.category_key === 'cloud_work',
                                              'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400': item.category_key === 'browsing'
                                          }"
                                          x-text="item.category">
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100 font-mono text-xs" x-text="item.formatted_bytes"></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500"
                                                 :class="{
                                                     'bg-rose-500': item.category_key === 'video',
                                                     'bg-blue-500': item.category_key === 'social_media',
                                                     'bg-emerald-500': item.category_key === 'gaming',
                                                     'bg-violet-500': item.category_key === 'cloud_work',
                                                     'bg-amber-500': item.category_key === 'browsing'
                                                 }"
                                                 :style="'width: ' + (item.percentage || 0) + '%'"></div>
                                        </div>
                                        <span class="text-[11px] font-mono font-semibold text-zinc-600 dark:text-zinc-300 w-10 text-right" x-text="(item.percentage || 0) + '%'"></span>
                                    </div>
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
        function trafficAnalyticsManager(initialData) {
            return {
                analytics: initialData,
                isDeploying: false,
                isResetting: false,
                donutChartInstance: null,
                hourlyChartInstance: null,

                init() {
                    this.$nextTick(() => {
                        this.renderCharts();
                    });
                },

                renderCharts() {
                    // 1. Render Donut Chart
                    const donutEl = document.querySelector('#donutChart');
                    if (donutEl && window.ApexCharts) {
                        if (this.donutChartInstance) this.donutChartInstance.destroy();

                        const isDark = document.documentElement.classList.contains('dark');
                        const options = {
                            series: this.analytics.donut_chart.series.length > 0 ? this.analytics.donut_chart.series : [50, 25, 15, 10],
                            labels: this.analytics.donut_chart.labels.length > 0 ? this.analytics.donut_chart.labels : ['Video', 'Sosmed', 'Gaming', 'Web'],
                            colors: this.analytics.donut_chart.colors.length > 0 ? this.analytics.donut_chart.colors : ['#f43f5e', '#3b82f6', '#10b981', '#f59e0b'],
                            chart: {
                                type: 'donut',
                                height: 260,
                                background: 'transparent',
                            },
                            stroke: {
                                width: 2,
                                colors: isDark ? ['#18181b'] : ['#ffffff'],
                            },
                            dataLabels: {
                                enabled: false,
                            },
                            legend: {
                                show: false,
                            },
                            tooltip: {
                                theme: isDark ? 'dark' : 'light',
                                y: {
                                    formatter: (val) => val + ' MB',
                                }
                            },
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '72%',
                                        labels: {
                                            show: true,
                                            total: {
                                                show: true,
                                                label: 'Total Trafik',
                                                formatter: () => this.analytics.formatted_total_traffic || '0 B',
                                            }
                                        }
                                    }
                                }
                            }
                        };
                        this.donutChartInstance = new ApexCharts(donutEl, options);
                        this.donutChartInstance.render();
                    }

                    // 2. Render Hourly Area Chart
                    const hourlyEl = document.querySelector('#hourlyChart');
                    if (hourlyEl && window.ApexCharts) {
                        if (this.hourlyChartInstance) this.hourlyChartInstance.destroy();

                        const isDark = document.documentElement.classList.contains('dark');
                        const options = {
                            series: this.analytics.hourly_chart.series,
                            chart: {
                                type: 'area',
                                height: 260,
                                background: 'transparent',
                                toolbar: { show: false },
                                zoom: { enabled: false },
                            },
                            colors: ['#f43f5e', '#3b82f6', '#10b981', '#f59e0b'],
                            dataLabels: { enabled: false },
                            stroke: { curve: 'smooth', width: 2 },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.35,
                                    opacityTo: 0.05,
                                    stops: [0, 90, 100]
                                }
                            },
                            xaxis: {
                                categories: this.analytics.hourly_chart.categories,
                                labels: {
                                    style: { colors: isDark ? '#a1a1aa' : '#71717a', fontSize: '10px' },
                                    rotate: 0,
                                },
                                axisBorder: { show: false },
                                axisTicks: { show: false },
                            },
                            yaxis: {
                                labels: {
                                    style: { colors: isDark ? '#a1a1aa' : '#71717a', fontSize: '10px' },
                                    formatter: (val) => val + ' MB',
                                },
                            },
                            grid: {
                                borderColor: isDark ? '#27272a' : '#f4f4f5',
                                strokeDashArray: 3,
                            },
                            tooltip: {
                                theme: isDark ? 'dark' : 'light',
                                y: { formatter: (val) => val + ' MB' }
                            },
                            legend: {
                                position: 'top',
                                horizontalAlign: 'right',
                                labels: { colors: isDark ? '#d4d4d8' : '#3f3f46' },
                                fontSize: '11px',
                            }
                        };
                        this.hourlyChartInstance = new ApexCharts(hourlyEl, options);
                        this.hourlyChartInstance.render();
                    }
                },

                deployRules() {
                    this.isDeploying = true;
                    fetch('{{ route("traffic.deploy") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isDeploying = false;
                        if (data.success) {
                            window.showToast(data.message, 'success');
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            window.showToast(data.message || 'Gagal memasang filter trafik', 'error');
                        }
                    })
                    .catch(() => {
                        this.isDeploying = false;
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },

                resetCounters() {
                    if (!confirm('Apakah Anda yakin ingin me-reset seluruh counter pemakaian trafik ke 0?')) {
                        return;
                    }

                    this.isResetting = true;
                    fetch('{{ route("traffic.reset-counters") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isResetting = false;
                        if (data.success) {
                            window.showToast(data.message, 'success');
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            window.showToast(data.message || 'Gagal reset counter', 'error');
                        }
                    })
                    .catch(() => {
                        this.isResetting = false;
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
