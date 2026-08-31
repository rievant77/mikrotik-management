<x-layouts.app>
    <x-slot:header>Live Dashboard</x-slot:header>

    <div class="space-y-6" x-data="dashboardLive()">
        <!-- Top Row: Welcome & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Live Network Overview</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Realtime monitoring data aktif & performa Hotspot MikroTik</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('hotspot.generate') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Generate Voucher</span>
                </a>
                <a href="{{ route('pos.monthly') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Bayar Bulanan</span>
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards Grid: Non-Duplicated Core KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Metric 1: Online Users & Active Sessions -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs transition-colors flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">User Online</span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-100 tracking-tight" x-text="stats.onlineUsers">{{ $onlineUsersCount ?? 0 }}</span>
                        <span class="text-xs text-zinc-400">/ {{ $totalHotspotUsers ?? 0 }} Total Akun</span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                    <span>Sesi Aktif: <strong class="text-zinc-700 dark:text-zinc-300" x-text="stats.activeDevices">{{ $activeSessionsCount ?? 0 }}</strong></span>
                    <a href="{{ route('users.index') }}" class="text-zinc-700 dark:text-zinc-300 hover:underline font-medium">Lihat User &rarr;</a>
                </div>
            </div>

            <!-- Metric 2: Today Total Data Consumption -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs transition-colors flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pemakaian Hari Ini</span>
                        <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-3xl font-extrabold text-zinc-900 dark:text-zinc-100 tracking-tight" x-text="stats.todayData">{{ \App\Support\FormatHelper::formatBytes($todayUsage, 1) }}</span>
                        <span class="text-xs text-zinc-400">Rx + Tx</span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                    <span>Akumulasi Hari Ini</span>
                    <a href="{{ route('users.historical') }}" class="text-zinc-700 dark:text-zinc-300 hover:underline font-medium">Histori &rarr;</a>
                </div>
            </div>

            <!-- Metric 3: Today Voucher Sales / Revenue -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs transition-colors flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Voucher Hari Ini</span>
                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-2xl font-extrabold text-zinc-900 dark:text-zinc-100 tracking-tight" x-text="stats.todayRevenueFormatted">{{ \App\Support\FormatHelper::formatRupiah($todayRevenue) }}</span>
                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium font-mono" x-text="stats.todayVouchersSold + ' Vcr'">{{ $todayVouchersSold }} Vcr</span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                    <span>Kasir & Billing</span>
                    <a href="{{ route('pos.vouchers') }}" class="text-zinc-700 dark:text-zinc-300 hover:underline font-medium">Lihat POS &rarr;</a>
                </div>
            </div>

            <!-- Metric 4: Router Connection & Gateway Status -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs transition-colors flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Router Gateway</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold transition-colors"
                            :class="routerInfo.is_online ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="routerInfo.is_online ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                            <span x-text="routerInfo.is_online ? 'Online' : 'Offline'">{{ $routerInfo['is_online'] ? 'Online' : 'Offline' }}</span>
                        </span>
                    </div>
                    <div class="mt-2">
                        <h4 class="text-xl font-extrabold text-zinc-900 dark:text-zinc-100 tracking-tight truncate" x-text="routerInfo.board_name || 'MikroTik'">{{ $routerInfo['board_name'] }}</h4>
                        <span class="text-xs text-zinc-400 font-mono block mt-0.5" x-text="'IP: ' + routerInfo.host + ':' + routerInfo.api_port">IP: {{ $routerInfo['host'] }}:{{ $routerInfo['api_port'] }}</span>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                    <span class="truncate" x-text="'Uptime: ' + (routerInfo.uptime_formatted || routerInfo.uptime || '-')">Uptime: {{ $routerInfo['uptime_formatted'] }}</span>
                    <a href="{{ route('settings.router') }}" class="text-zinc-700 dark:text-zinc-300 hover:underline shrink-0 font-medium">Setting &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Main Chart Section: Professional Network Traffic Monitor -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 sm:p-6 shadow-xs">
            <!-- Header & Telemetry Badges -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Realtime Interface Bandwidth Monitor</h3>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold" :class="isLiveActive ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500'">
                            <span class="w-2 h-2 rounded-full bg-emerald-500" :class="{ 'animate-ping': isLiveActive }"></span>
                            <span x-text="isLiveActive ? 'Live Stream (2s)' : 'Paused'"></span>
                        </span>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Pantauan kecepatan transmisi data WAN / Hotspot Interface secara langsung</p>
                </div>

                <!-- Digital Speed Readout Badges -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs">
                    <!-- Download Rx Badge -->
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60">
                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                        <span class="text-[11px] font-medium text-emerald-800 dark:text-emerald-300">Download (Rx):</span>
                        <strong class="font-mono text-sm text-emerald-600 dark:text-emerald-400" x-text="stats.downloadRate + ' Mbps'">0.0 Mbps</strong>
                    </div>

                    <!-- Upload Tx Badge -->
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-900/60">
                        <div class="w-2 h-2 rounded-full bg-sky-500"></div>
                        <span class="text-[11px] font-medium text-sky-800 dark:text-sky-300">Upload (Tx):</span>
                        <strong class="font-mono text-sm text-sky-600 dark:text-sky-400" x-text="stats.uploadRate + ' Mbps'">0.0 Mbps</strong>
                    </div>

                    <!-- Peak Throughput Badge -->
                    <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300">
                        <span class="text-[11px]">Peak Rx:</span>
                        <strong class="font-mono text-xs text-zinc-900 dark:text-zinc-100" x-text="peakRx + ' Mbps'">0.0 Mbps</strong>
                    </div>

                    <!-- Pause/Resume & Refresh Buttons -->
                    <button 
                        @click="toggleLiveStream()" 
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 transition-colors shadow-xs"
                    >
                        <span x-text="isLiveActive ? '⏸ Jeda' : '▶ Lanjutkan'"></span>
                    </button>
                </div>
            </div>

            <!-- ApexChart Container -->
            <div class="mt-4">
                <div id="realtimeTrafficChart" class="w-full h-72"></div>
            </div>

            <!-- Sub-chart info footer -->
            <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 flex flex-wrap items-center justify-between text-[11px] text-zinc-400">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-1 rounded bg-emerald-500"></span>
                        <span>Rx (Ingress / Download)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-1 rounded bg-sky-500"></span>
                        <span>Tx (Egress / Upload)</span>
                    </div>
                </div>
                <div>
                    <span>Total Throughput Sesi: </span>
                    <strong class="text-zinc-700 dark:text-zinc-300 font-mono" x-text="((Number(stats.downloadRate) + Number(stats.uploadRate)).toFixed(2)) + ' Mbps'">0.00 Mbps</strong>
                </div>
            </div>
        </div>

        <!-- Router System Health & Hardware Telemetry Panel -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 sm:p-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-700 dark:text-zinc-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-zinc-900 dark:text-zinc-100">Kesehatan & Spesifikasi Hardware Router</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Informasi resource, CPU, memori RAM, dan status koneksi API RouterOS</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                        :class="routerInfo.is_online ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'">
                        <span class="w-2 h-2 rounded-full" :class="routerInfo.is_online ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                        <span x-text="routerInfo.is_online ? 'Terhubung (API Connected)' : 'Terputus (Disconnected)'">{{ $routerInfo['is_online'] ? 'Terhubung (API Connected)' : 'Terputus (Disconnected)' }}</span>
                    </span>
                    <a href="{{ route('settings.router') }}" class="p-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors" title="Pengaturan Router">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- 4 Telemetry Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
                <!-- 1. Identitas & Model Router -->
                <div class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/80 dark:border-zinc-700/60 rounded-xl p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs text-zinc-400 font-medium">
                            <span>Model & Board</span>
                            <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mt-1.5 truncate" x-text="routerInfo.board_name || 'MikroTik'">{{ $routerInfo['board_name'] }}</h4>
                        <div class="mt-2 space-y-1 text-xs">
                            <div class="flex justify-between text-zinc-500 dark:text-zinc-400">
                                <span>RouterOS:</span>
                                <strong class="text-zinc-700 dark:text-zinc-300 font-mono" x-text="'v' + (routerInfo.version || '-')">v{{ $routerInfo['version'] }}</strong>
                            </div>
                            <div class="flex justify-between text-zinc-500 dark:text-zinc-400">
                                <span>Architecture:</span>
                                <span class="text-zinc-700 dark:text-zinc-300 font-mono" x-text="routerInfo.architecture || '-'">{{ $routerInfo['architecture'] ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-zinc-200/60 dark:border-zinc-700/60 text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        <span x-text="'Host: ' + routerInfo.host + ':' + routerInfo.api_port">Host: {{ $routerInfo['host'] }}:{{ $routerInfo['api_port'] }}</span>
                    </div>
                </div>

                <!-- 2. CPU Load & Processor -->
                <div class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/80 dark:border-zinc-700/60 rounded-xl p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs text-zinc-400 font-medium">
                            <span>Beban CPU</span>
                            <span class="font-mono font-semibold" :class="routerInfo.cpu_load > 85 ? 'text-rose-500' : (routerInfo.cpu_load > 60 ? 'text-amber-500' : 'text-emerald-500')" x-text="routerInfo.cpu_load + '%'">{{ $routerInfo['cpu_load'] }}%</span>
                        </div>
                        <div class="mt-1 flex items-baseline gap-2">
                            <h4 class="text-2xl font-extrabold text-zinc-900 dark:text-zinc-100 font-mono" x-text="routerInfo.cpu_load + '%'">{{ $routerInfo['cpu_load'] }}%</h4>
                            <span class="text-xs text-zinc-400" x-text="(routerInfo.cpu_count || 1) + ' Core' + (routerInfo.cpu_freq ? ' @ ' + routerInfo.cpu_freq : '')">{{ $routerInfo['cpu_count'] }} Core</span>
                        </div>
                        <div class="w-full h-2 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden mt-3">
                            <div class="h-full rounded-full transition-all duration-500"
                                :class="routerInfo.cpu_load > 85 ? 'bg-rose-500' : (routerInfo.cpu_load > 60 ? 'bg-amber-500' : 'bg-emerald-500')"
                                :style="'width: ' + Math.min(100, Math.max(2, routerInfo.cpu_load)) + '%'"></div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-zinc-200/60 dark:border-zinc-700/60 flex justify-between text-[11px] text-zinc-500 dark:text-zinc-400">
                        <span>Status CPU:</span>
                        <span class="font-medium" :class="routerInfo.cpu_load > 85 ? 'text-rose-500' : (routerInfo.cpu_load > 60 ? 'text-amber-500' : 'text-emerald-600 dark:text-emerald-400')" x-text="routerInfo.cpu_load > 85 ? 'Sangat Tinggi' : (routerInfo.cpu_load > 60 ? 'Sedang' : 'Normal / Sehat')">{{ $routerInfo['cpu_load'] > 85 ? 'Sangat Tinggi' : ($routerInfo['cpu_load'] > 60 ? 'Sedang' : 'Normal / Sehat') }}</span>
                    </div>
                </div>

                <!-- 3. RAM / Memory Resource -->
                <div class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/80 dark:border-zinc-700/60 rounded-xl p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs text-zinc-400 font-medium">
                            <span>Pemakaian Memori (RAM)</span>
                            <span class="font-mono font-semibold text-zinc-700 dark:text-zinc-300" x-text="routerInfo.memory_percent + '%'">{{ $routerInfo['memory_percent'] }}%</span>
                        </div>
                        <div class="mt-1 flex items-baseline justify-between">
                            <h4 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 font-mono" x-text="routerInfo.free_memory">{{ $routerInfo['free_memory'] }}</h4>
                            <span class="text-[11px] text-zinc-400">Free RAM</span>
                        </div>
                        <div class="w-full h-2 bg-zinc-200 dark:bg-zinc-700 rounded-full overflow-hidden mt-3">
                            <div class="h-full bg-indigo-500 rounded-full transition-all duration-500" :style="'width: ' + Math.min(100, Math.max(2, routerInfo.memory_percent)) + '%'"></div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-zinc-200/60 dark:border-zinc-700/60 flex justify-between text-[11px] text-zinc-500 dark:text-zinc-400">
                        <span>Total: <strong class="text-zinc-700 dark:text-zinc-300 font-mono" x-text="routerInfo.total_memory">{{ $routerInfo['total_memory'] }}</strong></span>
                        <span>Terpakai: <strong class="text-zinc-700 dark:text-zinc-300 font-mono" x-text="routerInfo.used_memory">{{ $routerInfo['used_memory'] }}</strong></span>
                    </div>
                </div>

                <!-- 4. Uptime & Storage -->
                <div class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/80 dark:border-zinc-700/60 rounded-xl p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs text-zinc-400 font-medium">
                            <span>Uptime & Storage</span>
                            <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mt-1.5 font-mono truncate" x-text="routerInfo.uptime_formatted || routerInfo.uptime || '-'">{{ $routerInfo['uptime_formatted'] ?? $routerInfo['uptime'] ?? '-' }}</h4>
                        <div class="mt-2 space-y-1 text-xs">
                            <div class="flex justify-between text-zinc-500 dark:text-zinc-400">
                                <span>Free Disk:</span>
                                <strong class="text-zinc-700 dark:text-zinc-300 font-mono" x-text="routerInfo.free_hdd || '-'">{{ $routerInfo['free_hdd'] }}</strong>
                            </div>
                            <div class="flex justify-between text-zinc-500 dark:text-zinc-400">
                                <span>Total Disk:</span>
                                <span class="text-zinc-700 dark:text-zinc-300 font-mono" x-text="routerInfo.total_hdd || '-'">{{ $routerInfo['total_hdd'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2.5 border-t border-zinc-200/60 dark:border-zinc-700/60 flex justify-between text-[11px] text-zinc-500 dark:text-zinc-400">
                        <span>Last Poll:</span>
                        <span class="font-medium text-zinc-700 dark:text-zinc-300" x-text="lastPollTimestamp || routerInfo.last_poll">{{ $routerInfo['last_poll'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Split Grid: Top Bandwidth Consumers & Live Active Users Table -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Top Bandwidth Consumers (1 col) -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Top Bandwidth Consumers</h3>
                    <span class="text-xs text-zinc-400">Saat Ini</span>
                </div>
                <template x-if="topConsumers.length === 0">
                    <div class="py-8 text-center text-xs text-zinc-400">
                        Belum ada data pemakaian aktif.
                    </div>
                </template>
                <div class="mt-4 space-y-4" x-show="topConsumers.length > 0">
                    <template x-for="(user, idx) in topConsumers" :key="user.id || user.username">
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-bold text-[10px] flex items-center justify-center" x-text="idx + 1"></span>
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200" x-text="user.username"></span>
                                </div>
                                <div class="text-right font-mono">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold" x-text="user.download_speed || '0 B/s'"></span>
                                </div>
                            </div>
                            <div class="w-full h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" :style="'width: ' + Math.min(100, Math.max(5, (user.current_rx_bps / 5000000) * 100)) + '%'"></div>
                            </div>
                            <div class="flex justify-between text-[10px] text-zinc-400 mt-1">
                                <span x-text="'IP: ' + (user.ip_address || '-')"></span>
                                <span x-text="'Total: ' + (user.total_bytes || '0 B')"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right: Realtime Online Hotspot Users (2 cols) -->
            <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">User Hotspot Aktif Terbaru</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                Live Session
                            </span>
                        </div>
                        <a href="{{ route('users.index') }}" class="text-xs text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 font-medium">Lihat Semua &rarr;</a>
                    </div>

                    <template x-if="recentUsers.length === 0">
                        <div class="py-12 text-center text-xs text-zinc-400">
                            Tidak ada sesi aktif saat ini.
                        </div>
                    </template>

                    <!-- Responsive Table -->
                    <div class="overflow-x-auto mt-2" x-show="recentUsers.length > 0">
                        <table class="w-full text-left text-xs">
                            <thead class="text-[11px] font-semibold text-zinc-400 uppercase border-b border-zinc-100 dark:border-zinc-800/80">
                                <tr>
                                    <th class="py-3 px-2">User / IP</th>
                                    <th class="py-3 px-2">Uptime</th>
                                    <th class="py-3 px-2">Rx / Tx Rate</th>
                                    <th class="py-3 px-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                <template x-for="user in recentUsers" :key="user.id || user.username">
                                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition-colors">
                                        <td class="py-3 px-2">
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100" x-text="user.username"></div>
                                            <div class="text-[10px] text-zinc-400" x-text="(user.ip_address || '-') + ' • ' + (user.mac_address || '-')"></div>
                                        </td>
                                        <td class="py-3 px-2 text-zinc-500 dark:text-zinc-400 font-mono" x-text="user.uptime || '-'"></td>
                                        <td class="py-3 px-2 font-mono text-[11px]">
                                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold" x-text="'↓ ' + (user.current_rx_bps ? (Math.round(user.current_rx_bps/1000) + ' Kbps') : '0 Kbps')"></span>
                                            <span class="text-sky-600 dark:text-sky-400 ml-1" x-text="'↑ ' + (user.current_tx_bps ? (Math.round(user.current_tx_bps/1000) + ' Kbps') : '0 Kbps')"></span>
                                        </td>
                                        <td class="py-3 px-2 text-right">
                                            <a :href="'/users/' + user.username" class="p-1.5 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 inline-block" title="Detail User">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function dashboardLive() {
            return {
                isLiveActive: true,
                pollTimer: null,
                peakRx: 0,
                lastPollTimestamp: null,
                stats: {
                    onlineUsers: {{ $onlineUsersCount ?? 0 }},
                    activeDevices: {{ $activeSessionsCount ?? 0 }},
                    downloadRate: '0.0',
                    uploadRate: '0.0',
                    todayData: '{{ \App\Support\FormatHelper::formatBytes($todayUsage, 1) }}',
                    todayRevenueFormatted: '{{ \App\Support\FormatHelper::formatRupiah($todayRevenue ?? 0) }}',
                    todayVouchersSold: {{ $todayVouchersSold ?? 0 }},
                    cpuLoad: {{ $routerInfo['cpu_load'] ?? 0 }}
                },
                routerInfo: @json($routerInfo ?? []),
                topConsumers: @json($topConsumers ?? []),
                recentUsers: @json($recentSessions ?? []),
                chart: null,
                rxSeries: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                txSeries: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                categories: ['', '', '', '', '', '', '', '', '', '', '', '', '', '', 'Now'],
                init() {
                    this.initChart();
                    this.startLiveStream();
                },
                toggleLiveStream() {
                    this.isLiveActive = !this.isLiveActive;
                    if (this.isLiveActive) {
                        this.startLiveStream();
                        window.showToast('Live stream bandwidth diaktifkan', 'info');
                    } else {
                        if (this.pollTimer) clearInterval(this.pollTimer);
                        window.showToast('Live stream bandwidth dijeda', 'info');
                    }
                },
                initChart() {
                    const isDark = document.documentElement.classList.contains('dark');
                    const options = {
                        series: [
                            { name: 'Download (Rx)', data: this.rxSeries },
                            { name: 'Upload (Tx)', data: this.txSeries }
                        ],
                        chart: {
                            type: 'area',
                            height: 280,
                            toolbar: { show: false },
                            animations: { 
                                enabled: true, 
                                easing: 'linear', 
                                dynamicAnimation: { speed: 1000 } 
                            },
                            fontFamily: 'inherit',
                            background: 'transparent',
                            dropShadow: {
                                enabled: true,
                                opacity: 0.15,
                                blur: 4,
                                left: 0,
                                top: 2
                            }
                        },
                        colors: ['#10b981', '#0ea5e9'],
                        stroke: { 
                            curve: 'smooth', 
                            width: [2.5, 2] 
                        },
                        fill: {
                            type: 'gradient',
                            gradient: { 
                                shadeIntensity: 1, 
                                opacityFrom: 0.35, 
                                opacityTo: 0.02, 
                                stops: [0, 90, 100] 
                            }
                        },
                        dataLabels: { enabled: false },
                        xaxis: {
                            categories: this.categories,
                            labels: { 
                                style: { 
                                    colors: isDark ? '#a1a1aa' : '#71717a',
                                    fontSize: '10px',
                                    fontFamily: 'monospace'
                                } 
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            min: 0,
                            forceNiceScale: true,
                            labels: {
                                formatter: (val) => Number(val).toFixed(1) + ' Mbps',
                                style: { 
                                    colors: isDark ? '#a1a1aa' : '#71717a',
                                    fontSize: '11px',
                                    fontFamily: 'monospace'
                                }
                            }
                        },
                        grid: {
                            borderColor: isDark ? '#27272a' : '#f4f4f5',
                            strokeDashArray: 3
                        },
                        tooltip: {
                            theme: isDark ? 'dark' : 'light',
                            y: {
                                formatter: (val) => val + ' Mbps'
                            }
                        },
                        theme: { mode: isDark ? 'dark' : 'light' },
                        legend: { 
                            show: false
                        }
                    };

                    this.chart = new ApexCharts(document.querySelector("#realtimeTrafficChart"), options);
                    this.chart.render();

                    window.addEventListener('theme-changed', (e) => {
                        this.chart.updateOptions({
                            theme: { mode: e.detail.isDark ? 'dark' : 'light' },
                            tooltip: { theme: e.detail.isDark ? 'dark' : 'light' },
                            xaxis: { labels: { style: { colors: e.detail.isDark ? '#a1a1aa' : '#71717a' } } },
                            yaxis: { labels: { style: { colors: e.detail.isDark ? '#a1a1aa' : '#71717a' } } },
                            grid: { borderColor: e.detail.isDark ? '#27272a' : '#f4f4f5' }
                        });
                    });
                },
                startLiveStream() {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    
                    // Initial immediate fetch
                    this.fetchMetrics();

                    // Poll every 2 seconds
                    this.pollTimer = setInterval(() => {
                        if (this.isLiveActive) {
                            this.fetchMetrics();
                        }
                    }, 2000);
                },
                fetchMetrics() {
                    fetch('{{ route("api.dashboard.live") }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.stats.onlineUsers = data.online_users;
                        this.stats.activeDevices = data.active_devices;
                        this.stats.downloadRate = data.download_rate_mbps;
                        this.stats.uploadRate = data.upload_rate_mbps;
                        if (data.today_usage) this.stats.todayData = data.today_usage;
                        if (data.today_revenue_formatted) this.stats.todayRevenueFormatted = data.today_revenue_formatted;
                        if (data.today_vouchers_sold !== undefined) this.stats.todayVouchersSold = data.today_vouchers_sold;
                        if (data.cpu_load !== undefined) this.stats.cpuLoad = data.cpu_load;
                        if (data.router_info) this.routerInfo = data.router_info;
                        if (data.timestamp) this.lastPollTimestamp = data.timestamp;
                        if (data.top_consumers) this.topConsumers = data.top_consumers;
                        if (data.recent_users) this.recentUsers = data.recent_users;

                        // Track peak download rate
                        if (Number(data.download_rate_mbps) > this.peakRx) {
                            this.peakRx = Number(data.download_rate_mbps).toFixed(2);
                        }

                        // Rolling chart updates
                        this.rxSeries.push(data.download_rate_mbps);
                        this.txSeries.push(data.upload_rate_mbps);
                        this.categories.push(data.timestamp || new Date().toLocaleTimeString('id-ID'));

                        if (this.rxSeries.length > 15) {
                            this.rxSeries.shift();
                            this.txSeries.shift();
                            this.categories.shift();
                        }

                        if (this.chart) {
                            this.chart.updateSeries([
                                { name: 'Download (Rx)', data: this.rxSeries },
                                { name: 'Upload (Tx)', data: this.txSeries }
                            ]);
                            this.chart.updateOptions({
                                xaxis: { categories: this.categories }
                            });
                        }
                    })
                    .catch(err => {
                        // Silent error on background poll
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
