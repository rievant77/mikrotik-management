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
                            <template x-if="isOnline">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Online
                                </span>
                            </template>
                            <template x-if="!isOnline">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                                    Offline
                                </span>
                            </template>
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
                        <div class="font-mono font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5" x-text="isOnline ? liveUptime : '0s (Offline)'">
                            {{ $primaryLiveSession['uptime'] ?? '0s (Offline)' }}
                        </div>
                        <template x-if="isOnline && hasLimit && remainingUptime">
                            <div class="mt-1">
                                <div class="flex items-center justify-between text-[10px] font-mono text-amber-600 dark:text-amber-400 font-semibold">
                                    <span>⏳ Sisa: <span x-text="remainingUptime"></span></span>
                                    <span class="text-zinc-400 font-normal" x-text="remainingPercent + '%'"></span>
                                </div>
                                <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-1 mt-0.5 overflow-hidden">
                                    <div class="bg-amber-500 h-1 rounded-full transition-all duration-500" :style="'width: ' + remainingPercent + '%'"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Live Transfer Rate -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-zinc-400 uppercase">Kecepatan Realtime</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500" :class="isOnline ? 'animate-ping' : 'opacity-0'"></span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <template x-if="isOnline">
                        <div class="flex items-baseline gap-2">
                            <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400 font-mono whitespace-nowrap" x-text="'↓ ' + downloadSpeed">
                                ↓ {{ \App\Support\FormatHelper::formatBytes((($primaryLiveSession['rx-rate'] ?? 0) / 8), 1) }}/s
                            </span>
                            <span class="text-xs text-sky-600 dark:text-sky-400 font-mono whitespace-nowrap" x-text="'↑ ' + uploadSpeed">
                                ↑ {{ \App\Support\FormatHelper::formatBytes((($primaryLiveSession['tx-rate'] ?? 0) / 8), 1) }}/s
                            </span>
                        </div>
                    </template>
                    <template x-if="!isOnline">
                        <div class="flex items-baseline gap-2">
                            <span class="text-xl font-bold text-zinc-400 font-mono">0 B/s</span>
                            <span class="text-xs text-zinc-400">Offline</span>
                        </div>
                    </template>
                </div>
                <p class="text-[11px] text-zinc-400 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800" x-text="isOnline ? 'Throughput aktif live MikroTik' : 'Tidak ada transfer data'">
                    {{ $isOnline ? 'Throughput aktif saat ini' : 'Tidak ada transfer data' }}
                </p>
            </div>

            <!-- Today Usage -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Pemakaian Hari Ini</span>
                <div class="mt-2">
                    <span class="text-xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                        {{ \App\Support\FormatHelper::formatBytes($todayUsage, 2) }}
                    </span>
                </div>
                <p class="text-[11px] text-zinc-400 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    Akumulasi data hari ini
                </p>
            </div>

            <!-- Month Usage -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Pemakaian Bulan Ini</span>
                <div class="mt-2">
                    <span class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                        {{ \App\Support\FormatHelper::formatBytes($monthUsage, 2) }}
                    </span>
                </div>
                <p class="text-[11px] text-zinc-400 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    {{ \App\Support\FormatHelper::formatMonthIndo(now()) }}
                </p>
            </div>

            <!-- All-Time Usage -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Total Seluruh Waktu</span>
                <div class="mt-2">
                    <span class="text-xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">
                        {{ \App\Support\FormatHelper::formatBytes($totalBytesAllTime, 2) }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-zinc-400 mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <span>Uptime: {{ \App\Support\FormatHelper::formatUptime($totalUptimeSeconds) }}</span>
                    <span>{{ number_format($totalSessionsCount) }} Sesi</span>
                </div>
            </div>
        </div>

        <!-- FUP (Fair Usage Policy) Status Card (Rendered if FUP is configured) -->
        <template x-if="fup && fup.fup_enabled">
            <div class="rounded-xl border p-4 shadow-xs transition-colors"
                :class="fup.fup_active ? 'bg-amber-500/10 border-amber-500/30 dark:bg-amber-950/30 dark:border-amber-800/40' : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800'">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg" :class="fup.fup_active ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'">
                            <span class="text-xl" x-text="fup.fup_active ? '🐢' : '🚀'"></span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">
                                    Fair Usage Policy (FUP)
                                </h3>
                                <template x-if="fup.fup_active">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/30 animate-pulse">
                                        FUP Throttled (<span x-text="fup.fup_rate_limit"></span>)
                                    </span>
                                </template>
                                <template x-if="!fup.fup_active">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        Kecepatan Normal (<span x-text="fup.normal_rate_limit"></span>)
                                    </span>
                                </template>
                            </div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                <template x-if="fup.fup_active">
                                    <span>Pemakaian telah melebihi batas <strong class="text-amber-600 dark:text-amber-400" x-text="fup.limit_formatted"></strong>. Kecepatan diturunkan otomatis ke <strong class="font-mono text-zinc-800 dark:text-zinc-200" x-text="fup.fup_rate_limit"></strong>.</span>
                                </template>
                                <template x-if="!fup.fup_active">
                                    <span>Kuota FUP: <strong class="text-zinc-800 dark:text-zinc-200" x-text="fup.limit_formatted"></strong> • Kecepatan diturunkan ke <strong class="font-mono" x-text="fup.fup_rate_limit"></strong> jika kuota habis. Siklus reset: <span x-text="fup.reset_cycle"></span>.</span>
                                </template>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 sm:self-center">
                        <div class="text-right">
                            <span class="text-[11px] text-zinc-400 block">Pemakaian Kuota FUP</span>
                            <span class="font-mono font-bold text-xs" :class="fup.fup_active ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-800 dark:text-zinc-200'">
                                <span x-text="fup.usage_formatted"></span> / <span x-text="fup.limit_formatted"></span> (<span x-text="fup.usage_percent + '%'"></span>)
                            </span>
                        </div>
                        <template x-if="fup.fup_active || fup.usage_bytes > 0">
                            <button 
                                @click="resetFup()"
                                :disabled="isResettingFup"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 transition-colors flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                            >
                                <span>🔄</span>
                                <span x-text="isResettingFup ? 'Mereset...' : 'Reset FUP User'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5 mt-3 overflow-hidden">
                    <div 
                        class="h-1.5 rounded-full transition-all duration-500" 
                        :class="fup.fup_active ? 'bg-amber-500' : 'bg-emerald-500'" 
                        :style="'width: ' + Math.min(100, fup.usage_percent) + '%'"
                    ></div>
                </div>
            </div>
        </template>

        <!-- Filter & Periode Toolbar Card -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs space-y-3">
            <form method="GET" action="{{ route('users.show', $username) }}" class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-2.5">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">Filter Periode & Riwayat</h3>
                    </div>
                    <span class="text-[11px] text-zinc-400">Menyesuaikan grafik, ringkasan, dan riwayat sesi</span>
                </div>

                <!-- Period Preset Chips -->
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 text-xs -mx-1 px-1">
                    @php
                        $presets = [
                            'today' => 'Hari Ini',
                            'yesterday' => 'Kemarin',
                            '7days' => '7 Hari',
                            '14days' => '14 Hari',
                            '30days' => '30 Hari',
                            'this_month' => 'Bulan Ini',
                            'all' => 'Semua Waktu',
                            'custom' => 'Kustom Tanggal',
                        ];
                    @endphp
                    @foreach($presets as $key => $label)
                        <a 
                            href="{{ route('users.show', array_merge(request()->except(['period', 'page']), ['username' => $username, 'period' => $key])) }}"
                            class="shrink-0 px-3 py-1.5 rounded-lg font-medium transition-colors border {{ ($period ?? '14days') === $key ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 border-zinc-900 dark:border-zinc-100 font-semibold shadow-xs' : 'bg-zinc-50 dark:bg-zinc-800/80 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700' }}"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <!-- Custom Date & Session Search Row (If Custom selected or always collapsible) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 pt-1">
                    <div>
                        <label class="block text-[10px] uppercase font-semibold text-zinc-400 mb-1">Dari Tanggal</label>
                        <input 
                            type="date" 
                            name="start_date" 
                            value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}"
                            class="w-full px-2.5 py-1.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                        >
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-semibold text-zinc-400 mb-1">Sampai Tanggal</label>
                        <input 
                            type="date" 
                            name="end_date" 
                            value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}"
                            class="w-full px-2.5 py-1.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                        >
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-semibold text-zinc-400 mb-1">Cari Device / IP / MAC</label>
                        <input 
                            type="text" 
                            name="session_search" 
                            value="{{ $sessionSearch ?? '' }}"
                            placeholder="Nama HP, IP, atau MAC..."
                            class="w-full px-2.5 py-1.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                        >
                    </div>
                    <div class="flex items-end gap-2">
                        <input type="hidden" name="period" value="custom">
                        <button 
                            type="submit" 
                            class="flex-1 py-1.5 px-3 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-zinc-200 text-white dark:text-zinc-900 shadow-xs transition-colors"
                        >
                            Terapkan
                        </button>
                        @if(request()->hasAny(['start_date', 'end_date', 'session_search', 'session_status']) || ($period && $period !== '14days'))
                            <a 
                                href="{{ route('users.show', $username) }}" 
                                class="py-1.5 px-2.5 text-xs font-medium rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
                                title="Reset Filter"
                            >
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- User Bandwidth Usage Chart -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800 gap-2">
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                        @if($isHourlyChart ?? false)
                            Grafik Konsumsi Kuota User (24 Jam: 00:00 - 23:00)
                        @else
                            Grafik Konsumsi Kuota User ({{ count($chartCategories) }} Titik Waktu)
                        @endif
                    </h3>
                    <p class="text-[11px] text-zinc-400 mt-0.5">
                        @if($isHourlyChart ?? false)
                            Distribusi volume unduh (Rx) dan unggah (Tx) per-jam pada tanggal {{ ($startDate ?? now())->format('d M Y') }}
                        @else
                            Riwayat volume download dan upload per hari sesuai filter periode
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
                <div id="userDailyTrendChart" class="w-full h-64"></div>
            </div>
        </div>

        <!-- Live Active Session Section -->
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
                <!-- Mobile Cards for Live Sessions -->
                <div class="block sm:hidden divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($liveSessions as $live)
                        <div class="p-4 space-y-2.5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="font-bold text-sm text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                        <span>{{ ($live['device_type'] ?? '') === 'phone' ? '📱' : (($live['device_type'] ?? '') === 'laptop' ? '💻' : '📡') }}</span>
                                        <span>{{ $live['device_display_name'] ?? ($live['device_name'] ?? 'Perangkat Hotspot') }}</span>
                                    </div>
                                    <div class="text-[11px] font-mono text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        {{ $live['address'] ?? '-' }} • {{ $live['mac-address'] ?? '-' }}
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                    ● Live
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800/80 text-xs">
                                <div>
                                    <span class="text-[10px] text-zinc-400 block">Live Speed</span>
                                    <span class="font-mono text-emerald-600 dark:text-emerald-400 font-semibold">↓ {{ \App\Support\FormatHelper::formatBytes((($live['rx-rate'] ?? 0) / 8), 1) }}/s</span>
                                    <span class="font-mono text-sky-600 dark:text-sky-400 text-[11px] ml-1">↑ {{ \App\Support\FormatHelper::formatBytes((($live['tx-rate'] ?? 0) / 8), 1) }}/s</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-zinc-400 block">Total Kuota / Uptime</span>
                                    <span class="font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ \App\Support\FormatHelper::formatBytes(((int)($live['bytes-in'] ?? 0)) + ((int)($live['bytes-out'] ?? 0)), 1) }}</span>
                                    <div class="text-[10px] text-zinc-400 font-mono">{{ $live['uptime'] ?? '0s' }}</div>
                                    @php
                                        $liveProg = \App\Support\FormatHelper::getUptimeProgress($live['uptime'] ?? '0s', $user?->uptime_limit ?: ($user?->profile?->validity ?: ($live['limit-uptime'] ?? null)), $live['session-time-left'] ?? null);
                                    @endphp
                                    @if($liveProg['has_limit'] && $liveProg['remaining_formatted'])
                                        <div class="text-[10px] font-mono text-amber-600 dark:text-amber-400 font-semibold">
                                            ⏳ Sisa: {{ $liveProg['remaining_formatted'] }} ({{ $liveProg['remaining_percent'] }}%)
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800/80">
                                <button 
                                    @click="disconnectUser('{{ $username }}')" 
                                    class="w-full py-2 text-xs font-semibold rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50 hover:bg-rose-100 transition-colors"
                                >
                                    Putus Sesi Ini
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table for Live Sessions -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="py-3.5 px-4">Perangkat / Session ID</th>
                                <th class="py-3.5 px-4">IP / MAC Address</th>
                                <th class="py-3.5 px-4">Uptime & Sisa Waktu</th>
                                <th class="py-3.5 px-4">Live Rate (Rx / Tx)</th>
                                <th class="py-3.5 px-4">Total Data Sesi</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($liveSessions as $live)
                                @php
                                    $liveProg = \App\Support\FormatHelper::getUptimeProgress($live['uptime'] ?? '0s', $user?->uptime_limit ?: ($user?->profile?->validity ?: ($live['limit-uptime'] ?? null)), $live['session-time-left'] ?? null);
                                @endphp
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                            <span>{{ ($live['device_type'] ?? '') === 'phone' ? '📱' : (($live['device_type'] ?? '') === 'laptop' ? '💻' : '📡') }}</span>
                                            <span>{{ $live['device_display_name'] ?? ($live['device_name'] ?? 'Perangkat Hotspot') }}</span>
                                        </div>
                                        <div class="text-[10px] text-zinc-400 font-mono">{{ $live['.id'] ?? $username }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono">
                                        <div class="text-zinc-800 dark:text-zinc-200">{{ $live['address'] ?? '-' }}</div>
                                        <div class="text-[10px] text-zinc-400">{{ $live['mac-address'] ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono">
                                        <div class="font-medium text-emerald-600 dark:text-emerald-400">
                                            {{ $live['uptime'] ?? '0s' }}
                                        </div>
                                        @if($liveProg['has_limit'] && $liveProg['remaining_formatted'])
                                            <div class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 mt-0.5">
                                                ⏳ Sisa: {{ $liveProg['remaining_formatted'] }} ({{ $liveProg['remaining_percent'] }}%)
                                            </div>
                                            <div class="w-24 bg-zinc-200 dark:bg-zinc-700 rounded-full h-1 mt-1 overflow-hidden">
                                                <div class="bg-amber-500 h-1 rounded-full" style="width: {{ $liveProg['remaining_percent'] }}%"></div>
                                            </div>
                                        @endif
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

        <!-- Daily Usage History Section -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
            <div class="px-5 py-3.5 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/20">
                <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">Histori Pemakaian Harian</h3>
                <span class="text-[11px] text-zinc-400">{{ count($dailySummaries) }} Hari Terfilter</span>
            </div>

            @if(count($dailySummaries) > 0)
                <!-- Mobile Cards for Daily Summaries -->
                <div class="block sm:hidden divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($dailySummaries as $day)
                        <div class="p-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 font-bold text-sm text-zinc-900 dark:text-zinc-100">
                                    <span>{{ \Carbon\Carbon::parse($day->usage_date)->format('d M Y') }}</span>
                                    @if(\Carbon\Carbon::parse($day->usage_date)->isToday())
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">Hari Ini</span>
                                    @endif
                                </div>
                                <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ \App\Support\FormatHelper::formatBytes($day->total_bytes, 1) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-zinc-100 dark:border-zinc-800/80">
                                <div>
                                    <span class="text-[10px] text-zinc-400 block">Throughput Rx / Tx</span>
                                    <span class="font-mono text-emerald-600 dark:text-emerald-400">↓ {{ \App\Support\FormatHelper::formatBytes($day->total_bytes_out, 1) }}</span>
                                    <span class="font-mono text-sky-600 dark:text-sky-400 ml-1">↑ {{ \App\Support\FormatHelper::formatBytes($day->total_bytes_in, 1) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-zinc-400 block">Sesi & Durasi</span>
                                    <span class="text-zinc-700 dark:text-zinc-300 font-medium">{{ $day->session_count ?: 1 }} Sesi • {{ \App\Support\FormatHelper::formatUptime($day->total_uptime_seconds) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table for Daily Summaries -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="py-3.5 px-4">Tanggal</th>
                                <th class="py-3.5 px-4">Jumlah Sesi</th>
                                <th class="py-3.5 px-4">Durasi Uptime</th>
                                <th class="py-3.5 px-4">Upload (Tx)</th>
                                <th class="py-3.5 px-4">Download (Rx)</th>
                                <th class="py-3.5 px-4 font-bold">Total Kuota</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($dailySummaries as $day)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="py-3.5 px-4 font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ \Carbon\Carbon::parse($day->usage_date)->format('d M Y') }}
                                        @if(\Carbon\Carbon::parse($day->usage_date)->isToday())
                                            <span class="ml-1.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">Hari Ini</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-zinc-700 dark:text-zinc-300">
                                        {{ $day->session_count ?: 1 }} Sesi
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-zinc-500">
                                        {{ \App\Support\FormatHelper::formatUptime($day->total_uptime_seconds) }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-sky-600 dark:text-sky-400">
                                        ↑ {{ \App\Support\FormatHelper::formatBytes($day->total_bytes_in, 1) }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-emerald-600 dark:text-emerald-400">
                                        ↓ {{ \App\Support\FormatHelper::formatBytes($day->total_bytes_out, 1) }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ \App\Support\FormatHelper::formatBytes($day->total_bytes, 1) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-xs text-zinc-400">
                    Belum ada catatan riwayat pemakaian harian pada periode ini.
                </div>
            @endif
        </div>

        <!-- Session History Ledger Section -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
            <div class="px-5 py-3.5 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/20">
                <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">Riwayat Sesi Login Sebelumnya</h3>
                <span class="text-[11px] text-zinc-400">{{ count($pastSessions) }} Sesi Ditemukan</span>
            </div>

            @if(count($pastSessions) > 0)
                <!-- Mobile Cards for Past Sessions -->
                <div class="block sm:hidden divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach($pastSessions as $session)
                        <div class="p-4 space-y-2.5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="font-bold text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $session->hostname ?: ($session->device_name ?: 'Unknown Device') }}
                                    </div>
                                    <div class="text-[11px] font-mono text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        {{ $session->ip_address ?: '-' }} • {{ $session->mac_address ?: '-' }}
                                    </div>
                                </div>
                                <div>
                                    @if($session->status === 'active')
                                        <x-badge type="active" dot="true">Aktif</x-badge>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                                            Ended
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs pt-1 border-t border-zinc-100 dark:border-zinc-800/80">
                                <div>
                                    <span class="text-[10px] text-zinc-400 block">Waktu Sesi</span>
                                    <div class="text-zinc-700 dark:text-zinc-300">
                                        {{ $session->started_at ? \Carbon\Carbon::parse($session->started_at)->format('d M, H:i') : '-' }}
                                        &rarr;
                                        {{ $session->ended_at ? \Carbon\Carbon::parse($session->ended_at)->format('H:i') : 'Now' }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-zinc-400 block">Rx / Tx</span>
                                    <span class="font-mono text-emerald-600 dark:text-emerald-400">↓ {{ \App\Support\FormatHelper::formatBytes($session->total_bytes_out, 1) }}</span>
                                    <span class="font-mono text-sky-600 dark:text-sky-400 ml-1">↑ {{ \App\Support\FormatHelper::formatBytes($session->total_bytes_in, 1) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop Table for Past Sessions -->
                <div class="hidden sm:block overflow-x-auto">
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
                    Belum ada riwayat sesi login yang cocok dengan filter.
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function userDetailComponent() {
            return {
                isOnline: @json($isOnline),
                downloadSpeed: '{{ \App\Support\FormatHelper::formatBytes((($primaryLiveSession["rx-rate"] ?? 0) / 8), 1) }}/s',
                uploadSpeed: '{{ \App\Support\FormatHelper::formatBytes((($primaryLiveSession["tx-rate"] ?? 0) / 8), 1) }}/s',
                liveUptime: '{{ $primaryLiveSession["uptime"] ?? "0s (Offline)" }}',
                hasLimit: @json($uptimeProgress['has_limit'] ?? false),
                limitUptime: '{{ $uptimeProgress["limit_formatted"] ?? "" }}',
                remainingUptime: '{{ $uptimeProgress["remaining_formatted"] ?? "" }}',
                remainingPercent: {{ $uptimeProgress['remaining_percent'] ?? 100 }},
                fup: @json($fupProgress ?? null),
                isResettingFup: false,
                pollTimer: null,
                chartCategories: @json($chartCategories ?? []),
                chartRxSeries: @json($chartRxSeries ?? []),
                chartTxSeries: @json($chartTxSeries ?? []),
                init() {
                    this.renderChart();
                    this.startPolling();
                },
                startPolling() {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    this.pollTimer = setInterval(() => {
                        fetch('{{ route("users.user.live", $username) }}?_t=' + Date.now(), {
                            cache: 'no-store',
                            headers: {
                                'Accept': 'application/json',
                                'Cache-Control': 'no-cache',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data) {
                                this.isOnline = data.is_online;
                                this.downloadSpeed = data.download_speed;
                                this.uploadSpeed = data.upload_speed;
                                this.liveUptime = data.uptime;
                                this.hasLimit = data.has_limit;
                                this.limitUptime = data.limit_uptime;
                                this.remainingUptime = data.remaining_uptime;
                                this.remainingPercent = data.remaining_percentage;
                                if (data.fup) {
                                    this.fup = data.fup;
                                }
                            }
                        })
                        .catch(() => {});
                    }, 2500);
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
                },
                resetFup() {
                    if (!confirm('Apakah Anda yakin ingin mereset kuota FUP dan mengembalikan kecepatan user ini ke normal?')) return;
                    this.isResettingFup = true;
                    fetch('{{ route("users.reset-fup", $username) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isResettingFup = false;
                        if (data.success) {
                            if (window.showToast) {
                                window.showToast(data.message, 'success');
                            } else {
                                alert(data.message);
                            }
                            if (this.fup) {
                                this.fup.fup_active = false;
                                this.fup.usage_bytes = 0;
                                this.fup.usage_formatted = '0 B';
                                this.fup.usage_percent = 0;
                            }
                        } else {
                            if (window.showToast) {
                                window.showToast(data.message || 'Gagal mereset FUP', 'error');
                            } else {
                                alert(data.message || 'Gagal mereset FUP');
                            }
                        }
                    })
                    .catch(() => {
                        this.isResettingFup = false;
                        if (window.showToast) {
                            window.showToast('Gagal mereset FUP', 'error');
                        } else {
                            alert('Gagal mereset FUP');
                        }
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
