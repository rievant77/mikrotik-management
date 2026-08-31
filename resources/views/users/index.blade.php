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
                        <span x-text="filteredUsers.length"></span> Aktif
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

        <!-- Filter & Search Toolbar -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-4 shadow-xs space-y-3">
            <!-- Search & Sort Row -->
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
                        placeholder="Cari user, nama HP/device, IP, MAC..." 
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

                <!-- Sort Dropdown -->
                <div class="flex items-center gap-2">
                    <select 
                        x-model="sortBy" 
                        class="w-full sm:w-auto px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                    >
                        <option value="rate_desc">Speed Tertinggi (Rx)</option>
                        <option value="bytes_desc">Total Kuota Terbanyak</option>
                        <option value="username_asc">Username (A-Z)</option>
                        <option value="device_asc">Device Name (A-Z)</option>
                    </select>
                </div>
            </div>

            <!-- Horizontal Filter Chips (Touch Friendly Carousel) -->
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
                    Semua (<span x-text="users.length"></span>)
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
                                <div class="flex items-center gap-1.5">
                                    <a :href="'/users/' + user.username" class="font-bold text-sm text-zinc-900 dark:text-zinc-100 hover:underline truncate" x-text="user.username"></a>
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0" title="Online"></span>
                                </div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400 flex items-center gap-1 font-mono">
                                    <span x-text="user.ip_address || '-'"></span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700" x-text="user.profile || '-'"></span>
                            <div class="text-[10px] text-zinc-400 font-mono mt-0.5" x-text="'⏱ ' + (user.uptime || '-')"></div>
                        </div>
                    </div>

                    <!-- Middle: Device Info Banner -->
                    <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/70 dark:border-zinc-700/50 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <!-- Device Icon -->
                            <span class="text-base shrink-0" x-text="getDeviceIcon(user.device_type)"></span>
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
                            class="flex items-center justify-center gap-1.5 py-2 px-3 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 transition-colors"
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
                            <th class="py-3.5 px-4">User & Perangkat</th>
                            <th class="py-3.5 px-4">IP / MAC Address</th>
                            <th class="py-3.5 px-4">Profile</th>
                            <th class="py-3.5 px-4">Uptime</th>
                            <th class="py-3.5 px-4">Live Rate (Rx / Tx)</th>
                            <th class="py-3.5 px-4">Total Data Sesi</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <template x-for="user in filteredUsers" :key="user.id || user.username">
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <!-- User & Device Name Column -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-bold text-xs flex items-center justify-center shrink-0 border border-zinc-200/60 dark:border-zinc-700/60">
                                            <span x-text="user.username.substring(0,2).toUpperCase()"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <a :href="'/users/' + user.username" class="font-bold text-zinc-900 dark:text-zinc-100 hover:underline truncate" x-text="user.username"></a>
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                            </div>
                                            <div class="text-[11px] text-zinc-500 dark:text-zinc-400 flex items-center gap-1 mt-0.5 truncate">
                                                <span x-text="getDeviceIcon(user.device_type)"></span>
                                                <span class="truncate" x-text="user.device_display_name || user.device_name || 'Perangkat Hotspot'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- IP & MAC -->
                                <td class="py-3.5 px-4 font-mono text-[11px]">
                                    <div class="text-zinc-900 dark:text-zinc-100 font-medium" x-text="user.ip_address || '-'"></div>
                                    <div class="text-[10px] text-zinc-400" x-text="user.mac_address || '-'"></div>
                                </td>

                                <!-- Profile -->
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700" x-text="user.profile || '-'"></span>
                                </td>

                                <!-- Uptime -->
                                <td class="py-3.5 px-4 font-mono text-zinc-500 dark:text-zinc-400" x-text="user.uptime || '-'"></td>

                                <!-- Combined Live Rate (Rx / Tx) Column -->
                                <td class="py-3.5 px-4 font-mono whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold" x-text="'↓ ' + formatRate(user.current_rx_bps)"></span>
                                        <span class="text-sky-600 dark:text-sky-400 text-[11px]" x-text="'↑ ' + formatRate(user.current_tx_bps)"></span>
                                    </div>
                                </td>

                                <!-- Total Data -->
                                <td class="py-3.5 px-4 font-mono font-semibold text-zinc-800 dark:text-zinc-200" x-text="formatBytes(user.total_bytes || (user.total_bytes_in + user.total_bytes_out) || 0)"></td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a :href="'/users/' + user.username" class="p-1.5 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" title="Detail User">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <button 
                                            type="button" 
                                            @click="promptDisconnect(user)" 
                                            class="p-1.5 text-rose-500 hover:text-rose-700 dark:hover:text-rose-400 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" 
                                            title="Putus Koneksi"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
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
                            <div class="flex justify-between">
                                <span class="text-zinc-500 dark:text-zinc-400">Username:</span>
                                <strong class="text-zinc-900 dark:text-zinc-100" x-text="targetUser.username"></strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500 dark:text-zinc-400">Perangkat:</span>
                                <span class="text-zinc-700 dark:text-zinc-300 font-medium" x-text="targetUser.device_display_name || targetUser.device_name || '-'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500 dark:text-zinc-400">IP & MAC:</span>
                                <span class="text-zinc-700 dark:text-zinc-300 font-mono" x-text="targetUser.ip_address + ' • ' + (targetUser.mac_address || '-')"></span>
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
                selectedProfile: '',
                sortBy: 'rate_desc',
                isLiveActive: true,
                pollTimer: null,
                disconnectModalOpen: false,
                targetUser: null,
                users: @json($sessions ?? []),
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
                    switch(type) {
                        case 'phone': return '📱';
                        case 'laptop': return '💻';
                        case 'pc': return '🖥️';
                        case 'tablet': return '📟';
                        default: return '📡';
                    }
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

                        return matchesSearch && matchesProfile;
                    });

                    if (this.sortBy === 'rate_desc') {
                        list.sort((a, b) => (b.current_rx_bps || 0) - (a.current_rx_bps || 0));
                    } else if (this.sortBy === 'bytes_desc') {
                        list.sort((a, b) => ((b.total_bytes || (b.total_bytes_in + b.total_bytes_out)) || 0) - ((a.total_bytes || (a.total_bytes_in + a.total_bytes_out)) || 0));
                    } else if (this.sortBy === 'username_asc') {
                        list.sort((a, b) => (a.username || '').localeCompare(b.username || ''));
                    } else if (this.sortBy === 'device_asc') {
                        list.sort((a, b) => (a.device_display_name || a.device_name || '').localeCompare(b.device_display_name || b.device_name || ''));
                    }

                    return list;
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
