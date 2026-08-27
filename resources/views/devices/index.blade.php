<x-layouts.app>
    <x-slot:header>Live Devices & Sessions</x-slot:header>

    <div class="space-y-6" x-data="devicesManager()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Live Devices & Active Sessions</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Daftar perangkat fisik (MAC Address) yang sedang aktif di jaringan Hotspot MikroTik</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-zinc-400">Total Active Sessions: <strong class="text-zinc-800 dark:text-zinc-200" x-text="devices.length"></strong></span>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="relative flex-1 w-full">
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Cari Nama Device, Hostname, MAC Address, IP, atau Username..." 
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Auto Refresh Indicator & Pause Toggle -->
                <div class="flex items-center justify-end text-xs text-zinc-400 gap-2 shrink-0">
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
        <template x-if="filteredDevices.length === 0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Tidak Ada Device / Sesi Aktif</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Saat ini tidak ada MAC Address atau perangkat yang aktif di jaringan hotspot MikroTik.</p>
            </div>
        </template>

        <!-- Devices Table -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs" x-show="filteredDevices.length > 0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3.5 px-4">Hostname Perangkat</th>
                            <th class="py-3.5 px-4">MAC Address</th>
                            <th class="py-3.5 px-4">IP Address</th>
                            <th class="py-3.5 px-4">Username Hotspot</th>
                            <th class="py-3.5 px-4">Interface</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Live Rate (Rx / Tx)</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <template x-for="device in filteredDevices" :key="device.id || device.mac_address">
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <!-- Hostname with Icon -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-600 dark:text-zinc-300 shrink-0">
                                            <!-- Phone Icon -->
                                            <template x-if="device.device_type === 'phone'">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                            </template>
                                            <!-- Laptop Icon -->
                                            <template x-if="device.device_type === 'laptop'">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            </template>
                                            <!-- Desktop Icon -->
                                            <template x-if="device.device_type === 'desktop'">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            </template>
                                            <!-- Router Icon -->
                                            <template x-if="device.device_type === 'router'">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                                </svg>
                                            </template>
                                            <!-- Generic Device Icon -->
                                            <template x-if="device.device_type === 'generic' || !device.device_type">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                            </template>
                                        </div>
                                        <div>
                                            <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-xs" x-text="device.hostname || device.device_display_name || device.device_name || 'Tanpa Hostname'"></div>
                                            <div class="text-[10px] text-zinc-400" x-text="device.hostname ? (device.device_display_name !== device.hostname ? device.device_display_name : 'DHCP Client') : (device.device_display_name || 'Hotspot Client')"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- MAC Address -->
                                <td class="py-3.5 px-4 font-mono font-medium text-zinc-700 dark:text-zinc-300" x-text="device.mac_address || device.mac || '-'"></td>

                                <!-- IP Address -->
                                <td class="py-3.5 px-4 font-mono text-zinc-700 dark:text-zinc-300" x-text="device.ip_address || device.ip || '-'"></td>

                                <!-- Username Hotspot -->
                                <td class="py-3.5 px-4">
                                    <a :href="'/users/' + device.username" class="font-medium text-zinc-900 dark:text-zinc-100 hover:underline inline-flex items-center gap-1" x-text="device.username"></a>
                                </td>

                                <!-- Interface -->
                                <td class="py-3.5 px-4 font-mono text-zinc-500" x-text="device.interface || 'hotspot'"></td>

                                <!-- Status -->
                                <td class="py-3.5 px-4">
                                    <x-badge type="active" dot="true">Active</x-badge>
                                </td>

                                <!-- Combined Live Rate Column -->
                                <td class="py-3.5 px-4 font-mono whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold" x-text="'↓ ' + formatRate(device.current_rx_bps)"></span>
                                        <span class="text-sky-600 dark:text-sky-400" x-text="'↑ ' + formatRate(device.current_tx_bps)"></span>
                                    </div>
                                </td>

                                <!-- Action -->
                                <td class="py-3.5 px-4 text-right">
                                    <button @click="inspectDevice(device)" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors">
                                        Inspect
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Session Inspector Modal -->
        <x-modal name="session-inspector-modal" title="Detail Perangkat & Hostname">
            <template x-if="selectedDevice">
                <div class="space-y-4">
                    <div class="p-3 bg-zinc-100 dark:bg-zinc-800/80 rounded-xl border border-zinc-200 dark:border-zinc-700/80 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-zinc-400">Hostname MikroTik</span>
                            <h4 class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-sm" x-text="selectedDevice.hostname || selectedDevice.device_display_name || selectedDevice.device_name || 'Tanpa Hostname'"></h4>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <span class="text-zinc-400">MAC Address</span>
                            <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100 mt-1" x-text="selectedDevice.mac_address || selectedDevice.mac || '-'"></div>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <span class="text-zinc-400">IP Address</span>
                            <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100 mt-1" x-text="selectedDevice.ip_address || selectedDevice.ip || '-'"></div>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <span class="text-zinc-400">Username Terkait</span>
                            <div class="font-bold text-zinc-900 dark:text-zinc-100 mt-1" x-text="selectedDevice.username"></div>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <span class="text-zinc-400">Interface MikroTik</span>
                            <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100 mt-1" x-text="selectedDevice.interface || 'hotspot'"></div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <button @click="$dispatch('close-modal', 'session-inspector-modal')" class="px-4 py-2 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </template>
        </x-modal>
    </div>

    @push('scripts')
    <script>
        function devicesManager() {
            return {
                search: '',
                selectedDevice: null,
                isLiveActive: true,
                pollTimer: null,
                devices: @json($devices ?? []),
                init() {
                    this.startPolling();
                },
                startPolling() {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    this.pollTimer = setInterval(() => {
                        if (this.isLiveActive) {
                            fetch('{{ route("devices.live") }}?_t=' + Date.now(), {
                                cache: 'no-store',
                                headers: {
                                    'Accept': 'application/json',
                                    'Cache-Control': 'no-cache',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data && Array.isArray(data.devices)) {
                                    this.devices = [...data.devices];
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
                get filteredDevices() {
                    return this.devices.filter(d => {
                        const mac = d.mac_address || d.mac || '';
                        const ip = d.ip_address || d.ip || '';
                        const name = d.username || '';
                        const devName = d.device_display_name || d.device_name || '';
                        const host = d.hostname || '';
                        const s = this.search.toLowerCase();
                        return mac.toLowerCase().includes(s) ||
                               ip.includes(s) ||
                               name.toLowerCase().includes(s) ||
                               devName.toLowerCase().includes(s) ||
                               host.toLowerCase().includes(s);
                    });
                },
                inspectDevice(dev) {
                    this.selectedDevice = dev;
                    this.$dispatch('open-modal', 'session-inspector-modal');
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
