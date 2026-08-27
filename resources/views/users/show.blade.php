<x-layouts.app>
    <x-slot:header>Detail User: {{ $username }}</x-slot:header>

    <div class="space-y-6" x-data="userDetailComponent()">
        <!-- Back Navigation & Action Bar -->
        <div class="flex items-center justify-between">
            <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Daftar User</span>
            </a>

            <div class="flex items-center gap-2">
                @if($isOnline)
                    <button 
                        @click="disconnectUser('{{ $username }}')" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors shadow-xs"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Putus Sesi Aktif</span>
                    </button>
                @endif
                <a href="{{ route('users.historical', ['search' => $username]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs">
                    <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span>Laporan Histori Kuota</span>
                </a>
            </div>
        </div>

        <!-- User Profile Summary Header Card -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- User Identity -->
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-bold text-xl flex items-center justify-center shadow-xs uppercase">
                        {{ strtoupper(substr($username, 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">{{ $username }}</h2>
                            @if($isOnline)
                                <x-badge type="active" dot="true">Online</x-badge>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                                    Offline
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                            <span>Profile: <strong class="text-zinc-800 dark:text-zinc-200">{{ $user?->profile?->name ?? ($primaryLiveSession['profile'] ?? 'Hotspot Profile') }}</strong></span>
                            @if($user?->profile?->rate_limit)
                                <span>• Limit: <span class="font-mono font-medium text-zinc-700 dark:text-zinc-300">{{ $user->profile->rate_limit }}</span></span>
                            @endif
                            @if($user?->profile?->selling_price)
                                <span>• Tarif: <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ \App\Support\FormatHelper::formatRupiah($user->profile->selling_price) }}</span></span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Session Quick Details -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 border-t lg:border-t-0 lg:border-l border-zinc-100 dark:border-zinc-800 pt-4 lg:pt-0 lg:pl-6 text-xs">
                    <div>
                        <div class="text-zinc-400 text-[11px]">IP Address</div>
                        <div class="font-mono font-semibold text-zinc-800 dark:text-zinc-200 mt-0.5">
                            {{ $primaryLiveSession['address'] ?? ($pastSessions->first()?->ip_address ?? '-') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-zinc-400 text-[11px]">MAC Address</div>
                        <div class="font-mono font-semibold text-zinc-800 dark:text-zinc-200 mt-0.5">
                            {{ $primaryLiveSession['mac-address'] ?? ($pastSessions->first()?->mac_address ?? '-') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-zinc-400 text-[11px]">Uptime Sesi Aktif</div>
                        <div class="font-mono font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5">
                            {{ $primaryLiveSession['uptime'] ?? '0s (Offline)' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Live Transfer Rate -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Kecepatan Realtime</span>
                <div class="mt-2 flex items-baseline gap-2">
                    @if($isOnline && $primaryLiveSession)
                        <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400 font-mono whitespace-nowrap">
                            ↓ {{ \App\Support\FormatHelper::formatBytes((($primaryLiveSession['rx-rate'] ?? 0) / 8), 1) }}/s
                        </span>
                        <span class="text-xs text-sky-600 dark:text-sky-400 font-mono whitespace-nowrap">
                            ↑ {{ \App\Support\FormatHelper::formatBytes((($primaryLiveSession['tx-rate'] ?? 0) / 8), 1) }}/s
                        </span>
                    @else
                        <span class="text-xl font-bold text-zinc-400 font-mono">0 B/s</span>
                        <span class="text-xs text-zinc-400">Offline</span>
                    @endif
                </div>
                <p class="text-[11px] text-zinc-400 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    {{ $isOnline ? 'Throughput aktif saat ini' : 'Tidak ada transfer data' }}
                </p>
            </div>

            <!-- Total Usage -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Total Pemakaian Kuota</span>
                <div class="mt-2">
                    <span class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                        {{ \App\Support\FormatHelper::formatBytes($totalBytesAllTime, 2) }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-zinc-400 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">Rx: {{ \App\Support\FormatHelper::formatBytes($totalBytesOut, 1) }}</span>
                    <span class="text-sky-600 dark:text-sky-400 font-medium">Tx: {{ \App\Support\FormatHelper::formatBytes($totalBytesIn, 1) }}</span>
                </div>
            </div>

            <!-- Total Uptime -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Akumulasi Uptime</span>
                <div class="mt-2">
                    <span class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                        {{ \App\Support\FormatHelper::formatUptime($totalUptimeSeconds) }}
                    </span>
                </div>
                <p class="text-[11px] text-zinc-400 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    Total durasi online tercatat
                </p>
            </div>

            <!-- Total Sessions -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Total Sesi Login</span>
                <div class="mt-2">
                    <span class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                        {{ number_format($totalSessionsCount) }} Sesi
                    </span>
                </div>
                <p class="text-[11px] text-zinc-400 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    Riwayat koneksi ke router
                </p>
            </div>
        </div>

        <!-- User Bandwidth Usage Chart (Last 14 Days) -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Grafik Konsumsi Kuota User (14 Hari Terakhir)</h3>
                    <p class="text-[11px] text-zinc-400 mt-0.5">Riwayat volume download dan upload per hari</p>
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
                <div id="userDailyTrendChart" class="w-full h-64"></div>
            </div>
        </div>

        <!-- Live Active Session Table -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
            <div class="px-5 py-3.5 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/20">
                <div class="flex items-center gap-2">
                    <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">Sesi Aktif di Router</h3>
                    @if($isOnline)
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    @endif
                </div>
                <span class="text-[11px] text-zinc-400">Live RouterOS Stream</span>
            </div>

            @if($isOnline && count($liveSessions) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="py-3.5 px-4">Session ID / User</th>
                                <th class="py-3.5 px-4">IP / MAC Address</th>
                                <th class="py-3.5 px-4">Uptime</th>
                                <th class="py-3.5 px-4">Live Rate (Rx / Tx)</th>
                                <th class="py-3.5 px-4">Total Data Sesi</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($liveSessions as $live)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="font-mono font-semibold text-zinc-900 dark:text-zinc-100">{{ $live['.id'] ?? $username }}</div>
                                        <div class="text-[10px] text-zinc-400">{{ $live['user'] ?? $username }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono">
                                        <div class="text-zinc-800 dark:text-zinc-200">{{ $live['address'] ?? '-' }}</div>
                                        <div class="text-[10px] text-zinc-400">{{ $live['mac-address'] ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-medium text-emerald-600 dark:text-emerald-400">
                                        {{ $live['uptime'] ?? '0s' }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold">↓ {{ \App\Support\FormatHelper::formatBytes((($live['rx-rate'] ?? 0) / 8), 1) }}/s</span>
                                            <span class="text-sky-600 dark:text-sky-400">↑ {{ \App\Support\FormatHelper::formatBytes((($live['tx-rate'] ?? 0) / 8), 1) }}/s</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-zinc-800 dark:text-zinc-200">
                                        {{ \App\Support\FormatHelper::formatBytes(((int)($live['bytes-in'] ?? 0)) + ((int)($live['bytes-out'] ?? 0)), 1) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <button @click="disconnectUser('{{ $username }}')" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 transition-colors">
                                            Putus
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-xs text-zinc-400">
                    <p>User <strong class="text-zinc-600 dark:text-zinc-300 font-mono">{{ $username }}</strong> sedang tidak terhubung di router MikroTik.</p>
                </div>
            @endif
        </div>

        <!-- Session History Ledger Table -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
            <div class="px-5 py-3.5 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/20">
                <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">Riwayat Sesi Login Sebelumnya</h3>
                <span class="text-[11px] text-zinc-400">{{ count($pastSessions) }} Sesi Terakhir</span>
            </div>

            @if(count($pastSessions) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="py-3.5 px-4">Perangkat / Hostname</th>
                                <th class="py-3.5 px-4">IP & MAC</th>
                                <th class="py-3.5 px-4">Waktu Mulai</th>
                                <th class="py-3.5 px-4">Waktu Selesai</th>
                                <th class="py-3.5 px-4">Upload (Tx)</th>
                                <th class="py-3.5 px-4">Download (Rx)</th>
                                <th class="py-3.5 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($pastSessions as $session)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="py-3.5 px-4 font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ $session->hostname ?: ($session->device_name ?: 'Unknown Device') }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-[11px]">
                                        <div class="text-zinc-800 dark:text-zinc-200">{{ $session->ip_address ?: '-' }}</div>
                                        <div class="text-[10px] text-zinc-400">{{ $session->mac_address ?: '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-400">
                                        {{ $session->started_at ? \Carbon\Carbon::parse($session->started_at)->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-400">
                                        {{ $session->ended_at ? \Carbon\Carbon::parse($session->ended_at)->format('d M Y, H:i') : '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-sky-600 dark:text-sky-400">
                                        ↑ {{ \App\Support\FormatHelper::formatBytes($session->total_bytes_in, 1) }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-emerald-600 dark:text-emerald-400">
                                        ↓ {{ \App\Support\FormatHelper::formatBytes($session->total_bytes_out, 1) }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($session->status === 'active')
                                            <x-badge type="active" dot="true">Aktif</x-badge>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                                                Ended
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-xs text-zinc-400">
                    Belum ada riwayat sesi login sebelumnya untuk user ini.
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function userDetailComponent() {
            return {
                chartCategories: @json($chartCategories ?? []),
                chartRxSeries: @json($chartRxSeries ?? []),
                chartTxSeries: @json($chartTxSeries ?? []),
                init() {
                    this.renderChart();
                },
                renderChart() {
                    const el = document.getElementById('userDailyTrendChart');
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
                disconnectUser(username) {
                    if (!confirm(`Apakah Anda yakin ingin memutus koneksi user ${username}?`)) return;

                    fetch(`/users/${username}/disconnect`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message || 'User berhasil diputus.');
                        window.location.reload();
                    })
                    .catch(() => {
                        alert('Gagal memutus user.');
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
