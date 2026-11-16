<x-layouts.app>
    <x-slot:header>Online Users</x-slot:header>

    <div class="space-y-4 sm:space-y-6" x-data="userLiveMonitoring()">
        <!-- Page Title & Header Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">User Hotspot Online</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1.5"></span>
                        <span x-text="formatNumber(filteredUsers.length)"></span> Aktif
                    </span>
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Pantauan perangkat & konsumsi data user aktif secara realtime</p>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    @click="togglePolling()" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs"
                >
                    <span class="w-2 h-2 rounded-full" :class="isLiveActive ? 'bg-emerald-500 animate-ping' : 'bg-zinc-400'"></span>
                    <span x-text="isLiveActive ? 'Live 2s' : 'Paused'"></span>
                </button>
                <a href="{{ route('users.historical') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Histori</span>
                </a>
            </div>
        </div>

        <!-- 5 KPI Metrics Summary -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <!-- Total Online -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                    <span class="text-xs font-medium uppercase tracking-wider">Total Online</span>
                    <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-2 font-mono" x-text="formatNumber(stats.total || users.length)">
                    {{ \App\Support\FormatHelper::formatNumber($stats['total'] ?? count($sessions)) }}
                </h3>
                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1">Sesi hotspot aktif</p>
            </div>

            <!-- Voucher (Profit) -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400">
                    <span class="text-xs font-medium uppercase tracking-wider flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Voucher
                    </span>
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2 font-mono" x-text="formatNumber(stats.voucher)">
                    {{ \App\Support\FormatHelper::formatNumber($stats['voucher'] ?? 0) }}
                </h3>
                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1">Paket Voucher Profit</p>
            </div>

            <!-- Bulanan (Profit) -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between text-sky-600 dark:text-sky-400">
                    <span class="text-xs font-medium uppercase tracking-wider flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                        Bulanan
                    </span>
                    <svg class="w-4 h-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-sky-600 dark:text-sky-400 mt-2 font-mono" x-text="formatNumber(stats.bulanan)">
                    {{ \App\Support\FormatHelper::formatNumber($stats['bulanan'] ?? 0) }}
                </h3>
                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1">Langganan Rumahan</p>
            </div>

            <!-- Pribadi (Non-Profit) -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between text-purple-600 dark:text-purple-400">
                    <span class="text-xs font-medium uppercase tracking-wider flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        Pribadi
                    </span>
                    <svg class="w-4 h-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-2 font-mono" x-text="formatNumber(stats.pribadi)">
                    {{ \App\Support\FormatHelper::formatNumber($stats['pribadi'] ?? 0) }}
                </h3>
                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1">Owner & Non-Profit</p>
            </div>

            <!-- Total Live Traffic -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs col-span-2 md:col-span-1">
                <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                    <span class="text-xs font-medium uppercase tracking-wider">Trafik Live</span>
                    <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div class="mt-2 font-mono leading-tight">
                    <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold" x-text="'↓ ' + formatRate(stats.total_rx_bps)"></div>
                    <div class="text-[11px] text-sky-600 dark:text-sky-400 font-medium mt-0.5" x-text="'↑ ' + formatRate(stats.total_tx_bps)"></div>
                </div>
                <p class="text-[10px] text-zinc-400 dark:text-zinc-500 mt-1" x-text="'Total: ' + formatBytes(stats.total_bytes)">Akumulasi Sesi</p>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-4 shadow-xs space-y-3">
            <!-- Search, Category & Sort Row -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                <!-- Search Input with Clear Button -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Cari username, nama perangkat, IP, MAC..." 
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                    >
                    <button 
                        type="button" 
                        x-show="search.length > 0" 
                        @click="search = ''" 
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Category Filter Dropdown -->
                <div class="flex items-center gap-1.5 shrink-0">
                    <label class="text-zinc-500 dark:text-zinc-400 text-xs font-medium">Kategori:</label>
                    <select 
                        x-model="categoryFilter" 
                        class="px-2.5 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-medium focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                    >
                        <option value="all">Semua Kategori</option>
                        <option value="voucher">Voucher (Profit)</option>
                        <option value="bulanan">Bulanan (Profit)</option>
                        <option value="pribadi">Pribadi (Non-Profit)</option>
                    </select>
                </div>

                <!-- Sort Dropdown -->
                <div class="flex items-center gap-2">
                    <select 
                        x-model="sortBy" 
                        class="w-full sm:w-auto px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                    >
                        <option value="rate_desc">Speed Tertinggi (Rx)</option>
                        <option value="bytes_desc">Total Kuota Terbanyak</option>
                        <option value="uptime_desc">Uptime Terlama</option>
                        <option value="username_asc">Username (A-Z)</option>
                        <option value="device_asc">Device Name (A-Z)</option>
                    </select>
                </div>
            </div>

            <!-- Horizontal Profile Filter Chips -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 text-xs -mx-1 px-1">
                <!-- All Filter -->
                <button 
                    @click="selectedProfile = ''"
                    type="button"
                    class="shrink-0 px-3 py-1.5 rounded-lg font-medium transition-colors border"
                    :class="selectedProfile === '' 
                        ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 border-zinc-900 dark:border-zinc-100 font-semibold shadow-xs' 
                        : 'bg-zinc-50 dark:bg-zinc-800/80 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700'"
                >
                    Semua Profil (<span x-text="formatNumber(users.length)"></span>)
                </button>

                <!-- Dynamic Profile Chips -->
                @foreach($profiles as $profile)
                    <button 
                        @click="selectedProfile = '{{ $profile->name }}'"
                        type="button"
                        class="shrink-0 px-3 py-1.5 rounded-lg font-medium transition-colors border"
                        :class="selectedProfile === '{{ $profile->name }}' 
                            ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 border-zinc-900 dark:border-zinc-100 font-semibold shadow-xs' 
                            : 'bg-zinc-50 dark:bg-zinc-800/80 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700'"
                    >
                        {{ $profile->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Empty State -->
        <template x-if="filteredUsers.length === 0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-10 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="mt-3 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Tidak Ada User Online</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Tidak ada pengguna aktif yang cocok dengan kata kunci pencarian atau filter paket yang dipilih.</p>
            </div>
        </template>

        <!-- 1. MOBILE VIEW: Interactive Session Cards (block sm:hidden) -->
        <div class="block sm:hidden space-y-3" x-show="filteredUsers.length > 0">
            <template x-for="user in filteredUsers" :key="user.id || user.username">
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs transition-colors space-y-3">
                    <!-- Top: User Header & Profile Badge -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 shrink-0 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-bold text-xs flex items-center justify-center border border-zinc-200/60 dark:border-zinc-700/60">
                                <span x-text="user.username.substring(0,2).toUpperCase()"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <a :href="'/users/' + user.username" class="font-bold text-sm text-zinc-900 dark:text-zinc-100 hover:underline truncate" x-text="user.username"></a>
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0" title="Online"></span>
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold uppercase border"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800': user.category === 'voucher',
                                            'bg-sky-50 text-sky-700 border-sky-300 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800': user.category === 'bulanan',
                                            'bg-purple-50 text-purple-700 border-purple-300 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800': user.category === 'pribadi',
                                        }"
                                        x-text="user.category || 'voucher'"
                                    ></span>
                                </div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400 flex items-center gap-1 font-mono">
                                    <span x-text="user.ip_address || '-'"></span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700" x-text="user.profile || '-'"></span>
                            <div class="text-[10px] text-zinc-400 font-mono mt-0.5" x-text="'⏱ ' + (user.uptime_formatted || user.uptime || '-')"></div>
                        </div>
                    </div>

                    <!-- Middle: Device Info Banner -->
                    <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/70 dark:border-zinc-700/50 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <!-- Device Icon -->
                            <span class="shrink-0 flex items-center justify-center" x-html="getDeviceIcon(user.device_type)"></span>
                            <div class="min-w-0">
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100 truncate text-[11px]" x-text="user.device_display_name || user.device_name || 'Perangkat Hotspot'"></div>
                                <div class="text-[10px] text-zinc-400 font-mono truncate" x-text="'MAC: ' + (user.mac_address || '-')"></div>
                            </div>
                        </div>
                        <span class="text-[10px] uppercase font-semibold px-1.5 py-0.5 rounded bg-zinc-200/70 dark:bg-zinc-700/60 text-zinc-600 dark:text-zinc-300 shrink-0" x-text="user.device_type || 'Device'"></span>
                    </div>

                    <!-- Telemetry Stats Row -->
                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-zinc-100 dark:border-zinc-800/80">
                        <!-- Live Rates -->
                        <div class="space-y-0.5">
                            <span class="text-[10px] text-zinc-400 uppercase font-medium">Live Bandwidth</span>
                            <div class="font-mono text-xs flex items-center gap-1.5">
                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold" x-text="'↓ ' + formatRate(user.current_rx_bps)"></span>
                                <span class="text-sky-600 dark:text-sky-400 text-[11px]" x-text="'↑ ' + formatRate(user.current_tx_bps)"></span>
                            </div>
                        </div>

                        <!-- Total Data -->
                        <div class="space-y-0.5 text-right">
                            <span class="text-[10px] text-zinc-400 uppercase font-medium">Total Kuota Sesi</span>
                            <div class="font-mono text-xs font-bold text-zinc-800 dark:text-zinc-200" x-text="formatBytes(user.total_bytes || (user.total_bytes_in + user.total_bytes_out) || 0)"></div>
                        </div>
                    </div>

                    <!-- Action Buttons Bar (Large Touch Target) -->
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800/80">
                        <a 
                            :href="'/users/' + user.username" 
                            class="flex items-center justify-center gap-1.5 py-2 px-3 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 transition-colors"
                        >
                            <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Detail User</span>
                        </a>
                        <button 
                            type="button" 
                            @click="promptDisconnect(user)" 
                            class="flex items-center justify-center gap-1.5 py-2 px-3 text-xs font-semibold rounded-lg bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50 transition-colors"
                        >
                            <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            <span>Putus / Kick</span>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <!-- 2. DESKTOP VIEW: Tabular Data Table (hidden sm:block) -->
        <div class="hidden sm:block bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs" x-show="filteredUsers.length > 0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3.5 px-4 text-left">User & Perangkat</th>
                            <th class="py-3.5 px-4 text-left">IP / MAC Address</th>
                            <th class="py-3.5 px-4 text-center">Kategori</th>
                            <th class="py-3.5 px-4 text-left">Paket Profil</th>
                            <th class="py-3.5 px-4 text-center">Uptime</th>
                            <th class="py-3.5 px-4 text-right">Live Rate (Rx / Tx)</th>
                            <th class="py-3.5 px-4 text-right">Total Kuota Sesi</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <template x-for="user in filteredUsers" :key="user.id || user.username">
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <!-- User & Device Name Column -->
                                <td class="py-3.5 px-4 align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-bold text-xs flex items-center justify-center shrink-0 border border-zinc-200/60 dark:border-zinc-700/60">
                                            <span x-text="user.username.substring(0,2).toUpperCase()"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <a :href="'/users/' + user.username" class="font-bold text-zinc-900 dark:text-zinc-100 hover:underline truncate" x-text="user.username"></a>
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                            </div>
                                            <div class="text-[11px] text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5 mt-0.5 truncate">
                                                <span class="shrink-0 flex items-center" x-html="getDeviceIcon(user.device_type)"></span>
                                                <span class="truncate" x-text="user.device_display_name || user.device_name || 'Perangkat Hotspot'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- IP & MAC -->
                                <td class="py-3.5 px-4 font-mono text-[11px] align-middle">
                                    <div class="text-zinc-900 dark:text-zinc-100 font-medium flex items-center gap-1.5">
                                        <span x-text="user.ip_address || '-'"></span>
                                        <button type="button" @click="copyText(user.ip_address, 'IP Address')" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors" title="Salin IP">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="text-[10px] text-zinc-400 mt-0.5 flex items-center gap-1">
                                        <span x-text="user.mac_address || '-'"></span>
                                        <button type="button" @click="copyText(user.mac_address, 'MAC Address')" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors" title="Salin MAC">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>

                                <!-- Kategori -->
                                <td class="py-3.5 px-4 text-center align-middle">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800': user.category === 'voucher',
                                            'bg-sky-50 text-sky-700 border-sky-300 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800': user.category === 'bulanan',
                                            'bg-purple-50 text-purple-700 border-purple-300 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800': user.category === 'pribadi',
                                        }"
                                        x-text="user.category || 'voucher'"
                                    ></span>
                                </td>

                                <!-- Profile -->
                                <td class="py-3.5 px-4 align-middle">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700" x-text="user.profile || '-'"></span>
                                </td>

                                <!-- Uptime -->
                                <td class="py-3.5 px-4 text-center align-middle font-mono text-[11px] text-zinc-700 dark:text-zinc-300">
                                    <div class="inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span x-text="user.uptime_formatted || user.uptime || '-'"></span>
                                    </div>
                                </td>

                                <!-- Combined Live Rate (Rx / Tx) Column -->
                                <td class="py-3.5 px-4 text-right align-middle font-mono whitespace-nowrap">
                                    <div class="inline-flex flex-col items-end">
                                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold text-xs" x-text="'↓ ' + formatRate(user.current_rx_bps)"></span>
                                        <span class="text-sky-600 dark:text-sky-400 text-[10px]" x-text="'↑ ' + formatRate(user.current_tx_bps)"></span>
                                    </div>
                                </td>

                                <!-- Total Data -->
                                <td class="py-3.5 px-4 text-right align-middle font-mono font-bold text-zinc-800 dark:text-zinc-200 text-xs" x-text="formatBytes(user.total_bytes || (user.total_bytes_in + user.total_bytes_out) || 0)"></td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-center align-middle">
                                    <div class="inline-flex items-center justify-center gap-1.5">
                                        <!-- Tombol Detail -->
                                        <a 
                                            :href="'/users/' + user.username" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 transition-colors shadow-2xs" 
                                            title="Lihat Detail Profil User"
                                        >
                                            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span>Detail</span>
                                        </a>

                                        <!-- Tombol Putus/Kick Sesi -->
                                        <button 
                                            type="button" 
                                            @click="promptDisconnect(user)" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/50 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50 transition-colors shadow-2xs" 
                                            title="Putuskan Sesi Aktif"
                                        >
                                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            <span>Kick</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Disconnect Confirmation Modal (Mobile Bottom-Sheet & Desktop Centered Modal) -->
        <div 
            x-show="disconnectModalOpen" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            style="display: none;"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-xs transition-opacity" @click="disconnectModalOpen = false"></div>

            <div class="min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4 text-center">
                <div 
                    class="relative bg-white dark:bg-zinc-900 rounded-t-2xl sm:rounded-2xl p-5 sm:p-6 text-left shadow-xl w-full max-w-md border border-zinc-200 dark:border-zinc-800 transition-all"
                    x-trap="disconnectModalOpen"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Putus Sesi Hotspot?</h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Sesi login user akan ditutup dari router MikroTik.</p>
                        </div>
                    </div>

                    <!-- Target User Summary Box -->
                    <template x-if="targetUser">
                        <div class="mt-4 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/70 dark:border-zinc-700/60 space-y-1.5 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-zinc-500 dark:text-zinc-400">Username:</span>
                                <div class="flex items-center gap-1.5">
                                    <strong class="text-zinc-900 dark:text-zinc-100" x-text="targetUser.username"></strong>
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase border"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800': targetUser.category === 'voucher',
                                            'bg-sky-50 text-sky-700 border-sky-300 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800': targetUser.category === 'bulanan',
                                            'bg-purple-50 text-purple-700 border-purple-300 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800': targetUser.category === 'pribadi',
                                        }"
                                        x-text="targetUser.category || 'voucher'"
                                    ></span>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500 dark:text-zinc-400">Paket:</span>
                                <span class="text-zinc-700 dark:text-zinc-300 font-medium" x-text="targetUser.profile || '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500 dark:text-zinc-400">Perangkat:</span>
                                <span class="text-zinc-700 dark:text-zinc-300 font-medium" x-text="targetUser.device_display_name || targetUser.device_name || '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500 dark:text-zinc-400">IP & MAC:</span>
                                <span class="text-zinc-700 dark:text-zinc-300 font-mono" x-text="targetUser.ip_address + ' • ' + (targetUser.mac_address || '-')"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500 dark:text-zinc-400">Durasi Aktif:</span>
                                <span class="text-zinc-700 dark:text-zinc-300 font-mono" x-text="targetUser.uptime_formatted || targetUser.uptime || '-'"></span>
                            </div>
                        </div>
                    </template>

                    <!-- Modal Actions -->
                    <div class="mt-5 flex items-center justify-end gap-2.5">
                        <button 
                            type="button" 
                            @click="disconnectModalOpen = false" 
                            class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="button" 
                            @click="executeDisconnect()" 
                            class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs transition-colors"
                        >
                            Ya, Putus Koneksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function userLiveMonitoring() {
            return {
                search: '',
                categoryFilter: 'all',
                selectedProfile: '',
                sortBy: 'rate_desc',
                isLiveActive: true,
                pollTimer: null,
                disconnectModalOpen: false,
                targetUser: null,
                users: @json($sessions ?? []),
                stats: @json($stats ?? []),
                init() {
                    this.startPolling();
                },
                startPolling() {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    this.pollTimer = setInterval(() => {
                        if (this.isLiveActive && !this.disconnectModalOpen) {
                            fetch('{{ route("users.live") }}?_t=' + Date.now(), {
                                cache: 'no-store',
                                headers: {
                                    'Accept': 'application/json',
                                    'Cache-Control': 'no-cache',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data && Array.isArray(data.sessions)) {
                                    this.users = [...data.sessions];
                                    if (data.stats) {
                                        this.stats = data.stats;
                                    }
                                }
                            })
                            .catch(() => {});
                        }
                    }, 2000);
                },
                togglePolling() {
                    this.isLiveActive = !this.isLiveActive;
                    if (this.isLiveActive) {
                        this.startPolling();
                        window.showToast('Live stream online user diaktifkan', 'info');
                    } else {
                        if (this.pollTimer) clearInterval(this.pollTimer);
                        window.showToast('Live stream dijeda', 'info');
                    }
                },
                getDeviceIcon(type) {
                    if (type === 'phone') {
                        return '<svg class="w-3.5 h-3.5 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>';
                    }
                    if (type === 'laptop' || type === 'pc' || type === 'desktop' || type === 'computer') {
                        return '<svg class="w-3.5 h-3.5 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>';
                    }
                    if (type === 'tablet') {
                        return '<svg class="w-3.5 h-3.5 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>';
                    }
                    return '<svg class="w-3.5 h-3.5 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>';
                },
                formatRate(bps) {
                    if (!bps || bps <= 0) return '0 Kbps';
                    if (bps >= 1000000) return (bps / 1000000).toFixed(2) + ' Mbps';
                    return Math.round(bps / 1000) + ' Kbps';
                },
                formatBytes(bytes) {
                    if (!bytes || bytes <= 0) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },
                get filteredUsers() {
                    let list = this.users.filter(u => {
                        const name = (u.username || '').toLowerCase();
                        const ip = (u.ip_address || u.ip || '').toLowerCase();
                        const mac = (u.mac_address || u.mac || '').toLowerCase();
                        const hostname = (u.hostname || '').toLowerCase();
                        const deviceName = (u.device_name || '').toLowerCase();
                        const deviceDisplayName = (u.device_display_name || '').toLowerCase();

                        const q = this.search.toLowerCase().trim();
                        const matchesSearch = !q || 
                            name.includes(q) || 
                            ip.includes(q) || 
                            mac.includes(q) || 
                            hostname.includes(q) || 
                            deviceName.includes(q) ||
                            deviceDisplayName.includes(q);

                        const profileName = u.profile || u.user?.profile?.name || '';
                        const matchesProfile = !this.selectedProfile || profileName === this.selectedProfile;

                        const userCategory = u.category || 'voucher';
                        const matchesCategory = this.categoryFilter === 'all' || userCategory === this.categoryFilter;

                        return matchesSearch && matchesProfile && matchesCategory;
                    });

                    if (this.sortBy === 'rate_desc') {
                        list.sort((a, b) => (b.current_rx_bps || 0) - (a.current_rx_bps || 0));
                    } else if (this.sortBy === 'bytes_desc') {
                        list.sort((a, b) => ((b.total_bytes || (b.total_bytes_in + b.total_bytes_out)) || 0) - ((a.total_bytes || (a.total_bytes_in + a.total_bytes_out)) || 0));
                    } else if (this.sortBy === 'uptime_desc') {
                        list.sort((a, b) => (b.uptime_seconds || 0) - (a.uptime_seconds || 0));
                    } else if (this.sortBy === 'username_asc') {
                        list.sort((a, b) => (a.username || '').localeCompare(b.username || ''));
                    } else if (this.sortBy === 'device_asc') {
                        list.sort((a, b) => (a.device_display_name || a.device_name || '').localeCompare(b.device_display_name || b.device_name || ''));
                    }

                    return list;
                },
                copyText(text, label) {
                    if (!text || text === '-') return;
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text).then(() => {
                            if (window.showToast) {
                                window.showToast(`${label} disalin!`, 'info');
                            }
                        }).catch(() => {
                            alert(`${label} disalin: ${text}`);
                        });
                    } else {
                        const input = document.createElement('input');
                        input.value = text;
                        document.body.appendChild(input);
                        input.select();
                        document.execCommand('copy');
                        document.body.removeChild(input);
                        if (window.showToast) {
                            window.showToast(`${label} disalin!`, 'info');
                        }
                    }
                },
                promptDisconnect(user) {
                    this.targetUser = user;
                    this.disconnectModalOpen = true;
                },
                executeDisconnect() {
                    if (!this.targetUser) return;
                    const username = this.targetUser.username;
                    this.disconnectModalOpen = false;

                    fetch(`/users/${username}/disconnect`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    }).then(() => {
                        this.users = this.users.filter(u => u.username !== username);
                        window.showToast(`Sesi user ${username} berhasil diputus`, 'success');
                    }).catch(() => {
                        this.users = this.users.filter(u => u.username !== username);
                        window.showToast(`Sesi user ${username} diputus`, 'info');
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
