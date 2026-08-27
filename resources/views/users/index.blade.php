<x-layouts.app>
    <x-slot:header>Online Users</x-slot:header>

    <div class="space-y-6" x-data="userLiveMonitoring()">
        <!-- Page Title & Header Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">User Hotspot Online</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1.5"></span>
                        <span x-text="filteredUsers.length"></span> Aktif
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Daftar pengguna yang sedang terhubung ke router MikroTik secara realtime</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('users.historical') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Histori Pemakaian</span>
                </a>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
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
                        placeholder="Cari user, IP, atau MAC..." 
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                    >
                </div>

                <!-- Profile Filter Dropdown (Dynamic from Database) -->
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

                <!-- Sort By -->
                <div>
                    <select 
                        x-model="sortBy" 
                        class="w-full px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                    >
                        <option value="rate_desc">Bandwidth Tertinggi (Rx)</option>
                        <option value="bytes_desc">Total Pemakaian Terbanyak</option>
                        <option value="username_asc">Username (A-Z)</option>
                    </select>
                </div>

                <!-- Auto Refresh Indicator & Pause Toggle -->
                <div class="flex items-center justify-end text-xs text-zinc-400 gap-2">
                    <button 
                        @click="togglePolling()" 
                        class="px-2.5 py-1 text-[11px] font-medium rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs"
                    >
                        <span x-text="isLiveActive ? '⏸ Jeda Live' : '▶ Lanjutkan Live'"></span>
                    </button>
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400" x-show="isLiveActive">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> Live 3s
                    </span>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <template x-if="filteredUsers.length === 0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Tidak Ada User Online</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Saat ini belum ada perangkat yang sedang login atau aktif di hotspot MikroTik dengan filter yang dipilih.</p>
            </div>
        </template>

        <!-- Users Table -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs" x-show="filteredUsers.length > 0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3.5 px-4">User & Identitas</th>
                            <th class="py-3.5 px-4">IP / MAC Address</th>
                            <th class="py-3.5 px-4">Profile</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Live Rate (Rx / Tx)</th>
                            <th class="py-3.5 px-4">Total Data Sesi</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <template x-for="user in filteredUsers" :key="user.id || user.username">
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-bold text-xs flex items-center justify-center">
                                            <span x-text="user.username.substring(0,2).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <a :href="'/users/' + user.username" class="font-semibold text-zinc-900 dark:text-zinc-100 hover:underline" x-text="user.username"></a>
                                            <div class="text-[10px] text-zinc-400" x-text="(user.interface || 'hotspot') + ' • ' + (user.uptime || '-')"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[11px]">
                                    <div class="text-zinc-800 dark:text-zinc-200" x-text="user.ip_address || user.ip || '-'"></div>
                                    <div class="text-[10px] text-zinc-400" x-text="user.mac_address || user.mac || '-'"></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700" x-text="user.profile || user.user?.profile?.name || '-'"></span>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-300 font-mono">
                                    <x-badge type="active" dot="true">Aktif</x-badge>
                                </td>
                                <!-- Combined Live Rate (Rx / Tx) Column -->
                                <td class="py-3.5 px-4 font-mono whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold" x-text="'↓ ' + formatRate(user.current_rx_bps)"></span>
                                        <span class="text-sky-600 dark:text-sky-400" x-text="'↑ ' + formatRate(user.current_tx_bps)"></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-zinc-700 dark:text-zinc-300" x-text="formatBytes(user.total_bytes || (user.total_bytes_in + user.total_bytes_out) || 0)"></td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a :href="'/users/' + user.username" class="p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800" title="Detail User">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <button @click="disconnectUser(user.username)" class="p-1 text-rose-500 hover:text-rose-700 dark:hover:text-rose-400 rounded hover:bg-rose-50 dark:hover:bg-rose-950/40" title="Putus Koneksi">
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
                users: @json($sessions ?? []),
                init() {
                    this.startPolling();
                },
                startPolling() {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    this.pollTimer = setInterval(() => {
                        if (this.isLiveActive) {
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
                        const name = u.username || '';
                        const ip = u.ip_address || u.ip || '';
                        const mac = u.mac_address || u.mac || '';
                        const matchesSearch = name.toLowerCase().includes(this.search.toLowerCase()) ||
                                              ip.includes(this.search) ||
                                              mac.toLowerCase().includes(this.search.toLowerCase());
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
                    }

                    return list;
                },
                disconnectUser(username) {
                    if (confirm(`Putus sesi hotspot user ${username}?`)) {
                        fetch(`/users/${username}/disconnect`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        }).then(() => {
                            this.users = this.users.filter(u => u.username !== username);
                            window.showToast(`User ${username} berhasil diputus`, 'success');
                        }).catch(() => {
                            this.users = this.users.filter(u => u.username !== username);
                            window.showToast(`User ${username} diputus`, 'info');
                        });
                    }
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
