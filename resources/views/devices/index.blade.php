<x-layouts.app>
    <x-slot:header>Live Devices & Connections</x-slot:header>

    <div class="space-y-6" x-data="devicesManager()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Live Devices & Koneksi Jaringan</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Daftar inventaris perangkat fisik (MAC Address) yang terhubung di router MikroTik</p>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    @click="togglePolling()" 
                    type="button"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs flex items-center gap-1.5"
                >
                    <span x-text="isLiveActive ? '⏸ Jeda Live' : '▶ Lanjutkan Live'"></span>
                </button>
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/50 px-2.5 py-1 rounded-lg" x-show="isLiveActive">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> Live 2s
                </span>
            </div>
        </div>

        <!-- 4 KPI Metrics Summary -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Total Devices -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                    <span class="text-xs font-medium uppercase tracking-wider">Total Perangkat</span>
                    <span class="text-base">📡</span>
                </div>
                <h3 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-2 font-mono" x-text="stats.total || devices.length">
                    {{ $stats['total'] ?? count($devices) }}
                </h3>
                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1">Terdata di DHCP Lease</p>
            </div>

            <!-- Online Hotspot Active -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400">
                    <span class="text-xs font-medium uppercase tracking-wider flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Online Hotspot
                    </span>
                    <span class="text-base">⚡</span>
                </div>
                <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-2 font-mono" x-text="stats.online">
                    {{ $stats['online'] ?? 0 }}
                </h3>
                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1">Sedang aktif berinternet</p>
            </div>

            <!-- Standby (Connected Not Logged In) -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between text-amber-600 dark:text-amber-400">
                    <span class="text-xs font-medium uppercase tracking-wider">Standby</span>
                    <span class="text-base">⏳</span>
                </div>
                <h3 class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-2 font-mono" x-text="stats.standby">
                    {{ $stats['standby'] ?? 0 }}
                </h3>
                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1">Terhubung WiFi, belum login</p>
            </div>

            <!-- Offline / Expired -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
                <div class="flex items-center justify-between text-zinc-400 dark:text-zinc-500">
                    <span class="text-xs font-medium uppercase tracking-wider">Offline / Idle</span>
                    <span class="text-base">🔌</span>
                </div>
                <h3 class="text-2xl font-bold text-zinc-600 dark:text-zinc-300 mt-2 font-mono" x-text="stats.offline">
                    {{ $stats['offline'] ?? 0 }}
                </h3>
                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1">Sewa IP expired / tidak aktif</p>
            </div>
        </div>

        <!-- Filter Toolbar & Touch-Friendly Chips -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs space-y-3">
            <!-- Search Bar -->
            <div class="relative w-full">
                <input 
                    type="text" 
                    x-model="search" 
                    placeholder="Cari Hostname, Brand (Samsung/Xiaomi/Apple), MAC, IP, atau User..." 
                    class="w-full pl-9 pr-8 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400 dark:text-zinc-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <button 
                    type="button"
                    x-show="search.length > 0" 
                    @click="search = ''" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-xs font-bold"
                >
                    ✕
                </button>
            </div>

            <!-- Filter Chips Carousel (Status & Device Type) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pt-1 border-t border-zinc-100 dark:border-zinc-800">
                <!-- Status Filter Chips -->
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 text-xs -mx-1 px-1">
                    <button 
                        @click="statusFilter = 'all'"
                        type="button"
                        class="shrink-0 px-3 py-1.5 rounded-lg border transition-colors shadow-xs"
                        :class="statusFilter === 'all' 
                            ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 border-zinc-900 dark:border-zinc-100 font-semibold' 
                            : 'bg-zinc-50 dark:bg-zinc-800/80 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700'"
                    >
                        Semua (<span x-text="devices.length"></span>)
                    </button>
                    <button 
                        @click="statusFilter = 'online'"
                        type="button"
                        class="shrink-0 px-3 py-1.5 rounded-lg border transition-colors shadow-xs flex items-center gap-1"
                        :class="statusFilter === 'online' 
                            ? 'bg-emerald-600 text-white dark:bg-emerald-500 dark:text-zinc-950 font-semibold border-emerald-600 dark:border-emerald-500' 
                            : 'bg-zinc-50 dark:bg-zinc-800/80 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700'"
                    >
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Online (<span x-text="stats.online"></span>)
                    </button>
                    <button 
                        @click="statusFilter = 'standby'"
                        type="button"
                        class="shrink-0 px-3 py-1.5 rounded-lg border transition-colors shadow-xs flex items-center gap-1"
                        :class="statusFilter === 'standby' 
                            ? 'bg-amber-600 text-white dark:bg-amber-500 dark:text-zinc-950 font-semibold border-amber-600 dark:border-amber-500' 
                            : 'bg-zinc-50 dark:bg-zinc-800/80 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700'"
                    >
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        Standby (<span x-text="stats.standby"></span>)
                    </button>
                    <button 
                        @click="statusFilter = 'offline'"
                        type="button"
                        class="shrink-0 px-3 py-1.5 rounded-lg border transition-colors shadow-xs flex items-center gap-1"
                        :class="statusFilter === 'offline' 
                            ? 'bg-zinc-800 text-white dark:bg-zinc-200 dark:text-zinc-900 font-semibold border-zinc-800 dark:border-zinc-200' 
                            : 'bg-zinc-50 dark:bg-zinc-800/80 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700'"
                    >
                        <span class="w-2 h-2 rounded-full bg-zinc-400"></span>
                        Offline (<span x-text="stats.offline"></span>)
                    </button>
                </div>

                <!-- Device Type Filter Chips -->
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 text-xs -mx-1 px-1">
                    <button 
                        @click="typeFilter = 'all'"
                        type="button"
                        class="shrink-0 px-2.5 py-1 text-xs rounded-md font-medium transition-colors"
                        :class="typeFilter === 'all' ? 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 font-semibold' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200'"
                    >
                        Semua Tipe
                    </button>
                    <button 
                        @click="typeFilter = 'phone'"
                        type="button"
                        class="shrink-0 px-2.5 py-1 text-xs rounded-md font-medium transition-colors flex items-center gap-1"
                        :class="typeFilter === 'phone' ? 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 font-semibold' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200'"
                    >
                        📱 HP / Tablet
                    </button>
                    <button 
                        @click="typeFilter = 'computer'"
                        type="button"
                        class="shrink-0 px-2.5 py-1 text-xs rounded-md font-medium transition-colors flex items-center gap-1"
                        :class="typeFilter === 'computer' ? 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 font-semibold' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200'"
                    >
                        💻 Komputer / Laptop
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <template x-if="filteredDevices.length === 0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400 dark:text-zinc-500 text-xl">
                    🔍
                </div>
                <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Tidak Ada Perangkat yang Cocok</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Coba ubah kata kunci pencarian atau sesuaikan filter status.</p>
                <button 
                    @click="search = ''; statusFilter = 'all'; typeFilter = 'all'" 
                    type="button"
                    class="mt-4 px-3 py-1.5 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors border border-zinc-200 dark:border-zinc-700"
                >
                    Reset Filter
                </button>
            </div>
        </template>

        <!-- 1. MOBILE VIEW: Interactive Device Cards (< 640px) -->
        <div class="block sm:hidden space-y-3" x-show="filteredDevices.length > 0">
            <template x-for="device in filteredDevices" :key="device.id || device.mac_address">
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs space-y-3 transition-colors">
                    <!-- Header Card: Icon + Name + Status Badge -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-lg shrink-0 border border-zinc-200/60 dark:border-zinc-700/60">
                                <span x-text="getDeviceIcon(device.device_type)"></span>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-mono font-bold text-sm text-zinc-900 dark:text-zinc-100 truncate" x-text="device.hostname || device.device_display_name || device.device_name || 'Tanpa Hostname'"></h4>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate" x-text="device.vendor !== 'Unknown Vendor' ? device.vendor : (device.device_display_name || 'Perangkat DHCP')"></p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="shrink-0">
                            <template x-if="device.status === 'online'">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800/80">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                                </span>
                            </template>
                            <template x-if="device.status === 'standby'">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-800/80">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Standby
                                </span>
                            </template>
                            <template x-if="device.status === 'offline'">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                    ○ Offline
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Details Body -->
                    <div class="bg-zinc-50/80 dark:bg-zinc-800/50 rounded-lg p-3 text-xs space-y-2 border border-zinc-200/70 dark:border-zinc-700/60">
                        <!-- User Hotspot Line -->
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500 dark:text-zinc-400 text-[11px]">User Hotspot</span>
                            <div>
                                <template x-if="device.username">
                                    <a :href="'/users/' + device.username" class="font-bold text-zinc-900 dark:text-zinc-100 hover:underline inline-flex items-center gap-1 text-xs">
                                        <span x-text="device.username"></span>
                                        <span class="text-[10px] text-zinc-400 dark:text-zinc-500">→</span>
                                    </a>
                                </template>
                                <template x-if="!device.username">
                                    <span class="text-[11px] text-amber-600 dark:text-amber-400 font-medium">Belum Login (No User)</span>
                                </template>
                            </div>
                        </div>

                        <!-- IP Address & Copy -->
                        <div class="flex items-center justify-between pt-1.5 border-t border-zinc-200/80 dark:border-zinc-700/80">
                            <span class="text-zinc-500 dark:text-zinc-400 text-[11px]">IP Address</span>
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono text-zinc-800 dark:text-zinc-200 text-xs" x-text="device.ip_address || '-'"></span>
                                <button 
                                    type="button"
                                    @click="copyText(device.ip_address, 'IP Address')" 
                                    class="p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors"
                                    title="Salin IP"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- MAC Address & Copy -->
                        <div class="flex items-center justify-between pt-1.5 border-t border-zinc-200/80 dark:border-zinc-700/80">
                            <span class="text-zinc-500 dark:text-zinc-400 text-[11px]">MAC Address</span>
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono text-zinc-800 dark:text-zinc-200 text-xs" x-text="device.mac_address || '-'"></span>
                                <button 
                                    type="button"
                                    @click="copyText(device.mac_address, 'MAC Address')" 
                                    class="p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors"
                                    title="Salin MAC"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button 
                            @click="inspectDevice(device)" 
                            type="button"
                            class="py-2 px-3 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 transition-colors text-center"
                        >
                            🔍 Inspect Detail
                        </button>
                        <template x-if="device.username">
                            <a 
                                :href="'/users/' + device.username" 
                                class="py-2 px-3 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-zinc-200 text-white dark:text-zinc-900 transition-colors text-center"
                            >
                                👤 Detail User
                            </a>
                        </template>
                        <template x-if="!device.username">
                            <div class="py-2 px-3 text-xs text-center text-zinc-400 dark:text-zinc-500 rounded-lg bg-zinc-100/50 dark:bg-zinc-800/30 border border-zinc-200/50 dark:border-zinc-800/50 flex items-center justify-center">
                                Tanpa Akun
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- 2. DESKTOP VIEW: Data Table (>= 640px) -->
        <div class="hidden sm:block bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs" x-show="filteredDevices.length > 0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3.5 px-4">Perangkat & Vendor</th>
                            <th class="py-3.5 px-4">MAC Address</th>
                            <th class="py-3.5 px-4">IP Address</th>
                            <th class="py-3.5 px-4">User Hotspot</th>
                            <th class="py-3.5 px-4">Interface</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <template x-for="device in filteredDevices" :key="device.id || device.mac_address">
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <!-- Hostname & Vendor with Icon -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-base shrink-0 border border-zinc-200/60 dark:border-zinc-700/60">
                                            <span x-text="getDeviceIcon(device.device_type)"></span>
                                        </div>
                                        <div>
                                            <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-xs" x-text="device.hostname || device.device_display_name || device.device_name || 'Tanpa Hostname'"></div>
                                            <div class="text-[10px] text-zinc-500 dark:text-zinc-400" x-text="device.vendor !== 'Unknown Vendor' ? device.vendor : (device.device_display_name || 'DHCP Client')"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- MAC Address -->
                                <td class="py-3.5 px-4 font-mono font-medium text-zinc-700 dark:text-zinc-300">
                                    <div class="flex items-center gap-1.5">
                                        <span x-text="device.mac_address || '-'"></span>
                                        <button 
                                            type="button"
                                            @click="copyText(device.mac_address, 'MAC Address')" 
                                            class="p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors"
                                            title="Salin MAC"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>

                                <!-- IP Address -->
                                <td class="py-3.5 px-4 font-mono text-zinc-700 dark:text-zinc-300">
                                    <div class="flex items-center gap-1.5">
                                        <span x-text="device.ip_address || '-'"></span>
                                        <button 
                                            type="button"
                                            @click="copyText(device.ip_address, 'IP Address')" 
                                            class="p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors"
                                            title="Salin IP"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>

                                <!-- User Hotspot -->
                                <td class="py-3.5 px-4">
                                    <template x-if="device.username">
                                        <a :href="'/users/' + device.username" class="font-bold text-zinc-900 dark:text-zinc-100 hover:underline inline-flex items-center gap-1" x-text="device.username"></a>
                                    </template>
                                    <template x-if="!device.username">
                                        <span class="text-amber-600 dark:text-amber-400 font-medium text-[11px]">Belum Login</span>
                                    </template>
                                </td>

                                <!-- Interface -->
                                <td class="py-3.5 px-4 font-mono text-zinc-500 dark:text-zinc-400" x-text="device.interface || 'hotspot'"></td>

                                <!-- Status -->
                                <td class="py-3.5 px-4">
                                    <template x-if="device.status === 'online'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800/80">
                                            ● Online
                                        </span>
                                    </template>
                                    <template x-if="device.status === 'standby'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-800/80">
                                            ● Standby
                                        </span>
                                    </template>
                                    <template x-if="device.status === 'offline'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                            ○ Offline
                                        </span>
                                    </template>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button 
                                            @click="inspectDevice(device)" 
                                            type="button"
                                            class="px-2.5 py-1 text-xs font-medium rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 transition-colors"
                                        >
                                            Inspect
                                        </button>
                                        <template x-if="device.username">
                                            <a :href="'/users/' + device.username" class="px-2.5 py-1 text-xs font-medium rounded-lg bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-zinc-200 text-white dark:text-zinc-900 transition-colors">
                                                Detail
                                            </a>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Enhanced Session Inspector & Device Management Modal -->
        <x-modal name="session-inspector-modal" title="Detail Perangkat & Manajemen" maxWidth="2xl">
            <template x-if="selectedDevice">
                <div class="space-y-4">
                    <!-- Modal Header Box -->
                    <div class="p-3.5 bg-zinc-50 dark:bg-zinc-800/80 rounded-xl border border-zinc-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-zinc-200/80 dark:bg-zinc-700 flex items-center justify-center text-2xl shrink-0 border border-zinc-300/60 dark:border-zinc-600">
                                <span x-text="getDeviceIcon(selectedDevice.device_type)"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-sm truncate" x-text="selectedDevice.hostname || selectedDevice.device_display_name || selectedDevice.device_name || 'Tanpa Hostname'"></h4>
                                    <!-- IP Binding Badge if any -->
                                    <template x-if="deviceDetail && deviceDetail.ip_binding">
                                        <span 
                                            :class="deviceDetail.ip_binding.type === 'bypassed' ? 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800' : 'bg-red-100 dark:bg-red-950/80 text-red-700 dark:text-red-300 border-red-300 dark:border-red-800'"
                                            class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider"
                                            x-text="'🛡️ ' + deviceDetail.ip_binding.type"
                                        ></span>
                                    </template>
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 truncate" x-text="selectedDevice.vendor !== 'Unknown Vendor' ? selectedDevice.vendor : 'Vendor tidak diketahui'"></p>
                            </div>
                        </div>

                        <!-- Status Badge Pill -->
                        <div class="shrink-0 flex items-center gap-1.5">
                            <template x-if="selectedDevice.status === 'online'">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Online Hotspot
                                </span>
                            </template>
                            <template x-if="selectedDevice.status === 'standby'">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Standby
                                </span>
                            </template>
                            <template x-if="selectedDevice.status === 'offline'">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                    ○ Offline
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- 3 Nav Tabs -->
                    <div class="flex items-center border-b border-zinc-200 dark:border-zinc-700/80 text-xs">
                        <button 
                            type="button"
                            @click="activeModalTab = 'info'" 
                            :class="activeModalTab === 'info' ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100 font-bold' : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200'"
                            class="py-2 px-3.5 border-b-2 transition-colors flex items-center gap-1.5"
                        >
                            <span>📋</span> Info & Jaringan
                        </button>
                        <button 
                            type="button"
                            @click="activeModalTab = 'history'" 
                            :class="activeModalTab === 'history' ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100 font-bold' : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200'"
                            class="py-2 px-3.5 border-b-2 transition-colors flex items-center gap-1.5"
                        >
                            <span>👥</span> Histori User & Kuota
                            <span 
                                x-show="deviceDetail && deviceDetail.session_history"
                                class="px-1.5 py-0.2 rounded-full text-[10px] bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-semibold"
                                x-text="deviceDetail ? deviceDetail.session_history.length : ''"
                            ></span>
                        </button>
                        <button 
                            type="button"
                            @click="activeModalTab = 'web'; loadWebActivity()" 
                            :class="activeModalTab === 'web' ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100 font-bold' : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200'"
                            class="py-2 px-3.5 border-b-2 transition-colors flex items-center gap-1.5"
                        >
                            <span>🌐</span> Aktivitas Web
                            <span 
                                x-show="webActivityData && webActivityData.live_count > 0"
                                class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 font-semibold"
                                x-text="webActivityData ? webActivityData.live_count : ''"
                            ></span>
                        </button>
                        <button 
                            type="button"
                            @click="activeModalTab = 'actions'" 
                            :class="activeModalTab === 'actions' ? 'border-zinc-900 text-zinc-900 dark:border-zinc-100 dark:text-zinc-100 font-bold' : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200'"
                            class="py-2 px-3.5 border-b-2 transition-colors flex items-center gap-1.5"
                        >
                            <span>⚡</span> Aksi Cepat
                        </button>
                    </div>

                    <!-- TAB 1: Info & Jaringan -->
                    <div x-show="activeModalTab === 'info'" class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                            <div class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <span class="text-zinc-500 dark:text-zinc-400 text-[11px]">User Hotspot Aktif</span>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100 mt-1 flex items-center justify-between">
                                    <span x-text="selectedDevice.username || 'Belum Login'"></span>
                                    <template x-if="selectedDevice.username">
                                        <a :href="'/users/' + selectedDevice.username" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline">Profil User →</a>
                                    </template>
                                </div>
                            </div>
                            <div class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <span class="text-zinc-500 dark:text-zinc-400 text-[11px]">Tipe Alokasi IP DHCP</span>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100 mt-1 flex items-center justify-between">
                                    <span x-text="selectedDevice.dynamic ? 'Dynamic Lease' : 'Static Lease'"></span>
                                    <template x-if="selectedDevice.dynamic">
                                        <button 
                                            type="button" 
                                            @click="activeModalTab = 'actions'" 
                                            class="text-[11px] font-semibold text-sky-600 dark:text-sky-400 hover:underline"
                                        >
                                            + Jadikan Statis
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <div class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <span class="text-zinc-500 dark:text-zinc-400 text-[11px]">IP Address</span>
                                <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100 mt-1 flex items-center justify-between">
                                    <span x-text="selectedDevice.ip_address || '-'"></span>
                                    <button type="button" @click="copyText(selectedDevice.ip_address, 'IP Address')" class="text-xs text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">Salin</button>
                                </div>
                            </div>
                            <div class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <span class="text-zinc-500 dark:text-zinc-400 text-[11px]">MAC Address</span>
                                <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100 mt-1 flex items-center justify-between">
                                    <span x-text="selectedDevice.mac_address || '-'"></span>
                                    <button type="button" @click="copyText(selectedDevice.mac_address, 'MAC Address')" class="text-xs text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200">Salin</button>
                                </div>
                            </div>
                            <div class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <span class="text-zinc-500 dark:text-zinc-400 text-[11px]">Interface MikroTik</span>
                                <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100 mt-1" x-text="selectedDevice.interface || 'hotspot'"></div>
                            </div>
                            <div class="bg-zinc-50 dark:bg-zinc-800/50 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <span class="text-zinc-500 dark:text-zinc-400 text-[11px]">Sisa Sewa IP (Expires After)</span>
                                <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100 mt-1" x-text="selectedDevice.expires_after || 'Permanen (Static)'"></div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Histori User & Akumulasi Kuota -->
                    <div x-show="activeModalTab === 'history'" class="space-y-3 text-xs">
                        <!-- Loading State -->
                        <div x-show="loadingDetail" class="py-8 text-center text-zinc-400">
                            <span class="inline-block animate-spin text-lg">⏳</span>
                            <p class="mt-2 text-xs">Mengambil riwayat sesi perangkat...</p>
                        </div>

                        <!-- Content when Loaded -->
                        <div x-show="!loadingDetail && deviceDetail" class="space-y-3">
                            <!-- Lifetime Quota Summary Card -->
                            <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-3.5 border border-zinc-200 dark:border-zinc-700">
                                <span class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Akumulasi Total Kuota Perangkat Ini</span>
                                <div class="grid grid-cols-3 gap-2 mt-2 pt-2 border-t border-zinc-200/80 dark:border-zinc-700/80 text-center">
                                    <div>
                                        <div class="text-[10px] text-zinc-400">Total Terpakai</div>
                                        <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100 font-mono mt-0.5" x-text="deviceDetail?.quota_stats?.total_bytes_formatted || '0 B'"></div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-zinc-400">Download (Rx)</div>
                                        <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400 font-mono mt-0.5" x-text="deviceDetail?.quota_stats?.download_formatted || '0 B'"></div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-zinc-400">Upload (Tx)</div>
                                        <div class="text-sm font-bold text-sky-600 dark:text-sky-400 font-mono mt-0.5" x-text="deviceDetail?.quota_stats?.upload_formatted || '0 B'"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- List of Users that used this Device -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Daftar Sesi & Voucher Terkait:</span>
                                    <span class="text-[11px] text-zinc-400" x-text="(deviceDetail?.quota_stats?.session_count || 0) + ' sesi terekam'"></span>
                                </div>

                                <div class="max-h-56 overflow-y-auto space-y-1.5 pr-0.5 no-scrollbar">
                                    <template x-for="item in (deviceDetail?.session_history || [])" :key="item.id">
                                        <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/80 flex items-center justify-between text-xs">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <a :href="'/users/' + item.username" class="font-bold text-zinc-900 dark:text-zinc-100 hover:underline inline-flex items-center gap-1" x-text="item.username"></a>
                                                    <span 
                                                        :class="item.status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300' : 'bg-zinc-200 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400'"
                                                        class="px-1.5 py-0.2 rounded text-[10px] font-semibold" 
                                                        x-text="item.status"
                                                    ></span>
                                                </div>
                                                <div class="text-[10px] text-zinc-400 mt-0.5" x-text="item.started_at + ' • Durasi: ' + item.duration"></div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-mono font-bold text-zinc-800 dark:text-zinc-200 text-xs" x-text="item.total_bytes_formatted"></div>
                                                <div class="text-[10px] text-zinc-400" x-text="'↓' + item.bytes_out_formatted + ' ↑' + item.bytes_in_formatted"></div>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!deviceDetail?.session_history || deviceDetail.session_history.length === 0">
                                        <div class="py-6 text-center text-zinc-400 bg-zinc-50 dark:bg-zinc-800/20 rounded-lg border border-dashed border-zinc-200 dark:border-zinc-700">
                                            Belum ada riwayat sesi login yang tersimpan untuk perangkat ini.
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Aksi Cepat (Quick Actions) -->
                    <div x-show="activeModalTab === 'actions'" class="space-y-3 text-xs">
                        <!-- Action 1: Kick Sesi -->
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700 flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                    <span>🚫</span> Putuskan / Kick Sesi Hotspot
                                </div>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Memutuskan user aktif di perangkat ini secara paksa.</p>
                            </div>
                            <button 
                                type="button"
                                @click="doKick()"
                                :disabled="actionLoading || selectedDevice.status !== 'online'"
                                :class="selectedDevice.status === 'online' ? 'bg-red-600 hover:bg-red-700 text-white shadow-xs' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-400 cursor-not-allowed'"
                                class="px-3.5 py-2 rounded-lg font-semibold text-xs shrink-0 transition-colors"
                            >
                                <span x-text="actionLoading ? 'Memproses...' : 'Kick Sesi'"></span>
                            </button>
                        </div>

                        <!-- Action 2: Make Static Lease -->
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700 flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                    <span>📌</span> Jadikan IP Statis (Make Static)
                                </div>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Kunci IP <code class="font-mono text-zinc-800 dark:text-zinc-200 font-bold" x-text="selectedDevice.ip_address"></code> di DHCP agar tidak pernah berubah.</p>
                            </div>
                            <button 
                                type="button"
                                @click="doMakeStatic()"
                                :disabled="actionLoading || !selectedDevice.dynamic"
                                :class="selectedDevice.dynamic ? 'bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-zinc-200 text-white dark:text-zinc-900 shadow-xs' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-400 cursor-not-allowed'"
                                class="px-3.5 py-2 rounded-lg font-semibold text-xs shrink-0 transition-colors"
                            >
                                <span x-text="!selectedDevice.dynamic ? 'Sudah Statis' : (actionLoading ? 'Memproses...' : 'Jadikan Statis')"></span>
                            </button>
                        </div>

                        <!-- Action 3: IP Binding / Bypass Hotspot -->
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-2.5">
                            <div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                    <span>🛡️</span> Bypass Hotspot (IP-Binding)
                                </div>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Izinkan akses internet langsung tanpa harus login voucher (untuk CCTV/Printer/HP Owner) atau Blokir.</p>
                            </div>
                            <div class="flex items-center gap-2 pt-1">
                                <button 
                                    type="button"
                                    @click="doSetIpBinding('bypassed')"
                                    :disabled="actionLoading"
                                    class="flex-1 py-1.5 px-3 rounded-lg font-semibold text-xs bg-emerald-600 hover:bg-emerald-700 text-white transition-colors text-center"
                                >
                                    🟢 Bypass (Tanpa Login)
                                </button>
                                <button 
                                    type="button"
                                    @click="doSetIpBinding('regular')"
                                    :disabled="actionLoading"
                                    class="py-1.5 px-3 rounded-lg font-semibold text-xs bg-zinc-200 dark:bg-zinc-700 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-300 transition-colors text-center"
                                >
                                    Normal (Voucher)
                                </button>
                                <button 
                                    type="button"
                                    @click="doSetIpBinding('blocked')"
                                    :disabled="actionLoading"
                                    class="py-1.5 px-3 rounded-lg font-semibold text-xs bg-red-600 hover:bg-red-700 text-white transition-colors text-center"
                                >
                                    🔴 Blokir
                                </button>
                            </div>
                        </div>

                        <!-- Action 4: Set Device Alias / Comment -->
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-2">
                            <div class="font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                <span>🏷️</span> Beri Nama Alias / Catatan
                            </div>
                            <div class="flex items-center gap-2">
                                <input 
                                    type="text" 
                                    x-model="deviceAliasInput" 
                                    placeholder="Contoh: HP Kasir 1 / Laptop Admin" 
                                    class="flex-1 px-3 py-1.5 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                                >
                                <button 
                                    type="button" 
                                    @click="doSaveComment()" 
                                    :disabled="actionLoading || !deviceAliasInput"
                                    class="px-3.5 py-1.5 rounded-lg font-semibold text-xs bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-zinc-200 text-white dark:text-zinc-900 transition-colors shrink-0"
                                >
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: Aktivitas & Riwayat Web -->
                    <div x-show="activeModalTab === 'web'" class="space-y-4 text-xs">
                        <!-- Loading State -->
                        <div x-show="loadingWebActivity" class="py-8 text-center text-zinc-400">
                            <span class="inline-block animate-spin text-lg">⏳</span>
                            <p class="mt-2 text-xs">Mengambil data koneksi & riwayat web dari MikroTik...</p>
                        </div>

                        <div x-show="!loadingWebActivity && webActivityData" class="space-y-4">
                            <!-- Section 1: Live Active Web Connections -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span class="font-bold text-zinc-900 dark:text-zinc-100">Koneksi & Web Sedang Terhubung:</span>
                                    </div>
                                    <button 
                                        type="button" 
                                        @click="loadWebActivity()" 
                                        class="text-[11px] font-semibold text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1"
                                    >
                                        <span>🔄</span> Segarkan Live
                                    </button>
                                </div>

                                <div class="max-h-48 overflow-y-auto space-y-1.5 pr-0.5 no-scrollbar">
                                    <template x-for="(conn, idx) in (webActivityData?.live_connections || [])" :key="idx">
                                        <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/80 flex items-center justify-between gap-3 text-xs">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-7 h-7 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-sm shrink-0">
                                                    <span x-text="conn.icon"></span>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="font-bold text-zinc-900 dark:text-zinc-100 truncate" x-text="conn.domain"></span>
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300" x-text="conn.category_label"></span>
                                                    </div>
                                                    <div class="text-[10px] text-zinc-400 font-mono" x-text="conn.dst_ip + ':' + conn.port + ' (' + conn.protocol + ' ' + conn.state + ')'"></div>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <div class="font-mono font-bold text-zinc-800 dark:text-zinc-200 text-xs" x-text="conn.total_bytes_formatted"></div>
                                                <div class="text-[10px] text-zinc-400" x-text="'↓' + conn.bytes_out_formatted + ' ↑' + conn.bytes_in_formatted"></div>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!webActivityData?.live_connections || webActivityData.live_connections.length === 0">
                                        <div class="py-4 text-center text-zinc-400 bg-zinc-50 dark:bg-zinc-800/20 rounded-lg border border-dashed border-zinc-200 dark:border-zinc-700 text-xs">
                                            Tidak ada koneksi web aktif yang terdeteksi saat ini.
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Section 2: Visited Web History -->
                            <div class="space-y-2 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100">📜 Riwayat Website yang Pernah Diakses:</span>
                                    <input 
                                        type="text" 
                                        x-model="webSearch" 
                                        placeholder="Cari domain..." 
                                        class="px-2.5 py-1 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none w-36 sm:w-44"
                                    >
                                </div>

                                <div class="max-h-56 overflow-y-auto space-y-1.5 pr-0.5 no-scrollbar">
                                    <template x-for="item in filteredWebHistory" :key="item.id">
                                        <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/80 flex items-center justify-between gap-3 text-xs">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-7 h-7 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-sm shrink-0">
                                                    <span x-text="item.icon"></span>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="font-bold text-zinc-900 dark:text-zinc-100 truncate" x-text="item.domain"></span>
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300" x-text="item.category_label"></span>
                                                    </div>
                                                    <div class="text-[10px] text-zinc-400 mt-0.5" x-text="'Terakhir: ' + item.last_seen_formatted + ' (' + item.last_seen_datetime + ')'"></div>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <div class="font-semibold text-zinc-800 dark:text-zinc-200 text-xs" x-text="item.hit_count + 'x diakses'"></div>
                                                <div class="text-[10px] text-zinc-400 font-mono" x-text="item.total_bytes_formatted"></div>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="filteredWebHistory.length === 0">
                                        <div class="py-6 text-center text-zinc-400 bg-zinc-50 dark:bg-zinc-800/20 rounded-lg border border-dashed border-zinc-200 dark:border-zinc-700 text-xs">
                                            Belum ada riwayat web yang tersimpan untuk perangkat ini.
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-between items-center pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="text-[11px] text-zinc-400">
                            MAC: <span class="font-mono font-medium text-zinc-600 dark:text-zinc-300" x-text="selectedDevice.mac_address"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="selectedDevice.username">
                                <a :href="'/users/' + selectedDevice.username" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
                                    Detail User
                                </a>
                            </template>
                            <button @click="$dispatch('close-modal', 'session-inspector-modal')" type="button" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors border border-zinc-200 dark:border-zinc-700">
                                Tutup
                            </button>
                        </div>
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
                statusFilter: 'all',
                typeFilter: 'all',
                selectedDevice: null,
                deviceDetail: null,
                loadingDetail: false,
                activeModalTab: 'info',
                deviceAliasInput: '',
                actionLoading: false,
                isLiveActive: true,
                pollTimer: null,
                webActivityData: null,
                loadingWebActivity: false,
                webSearch: '',
                devices: @json($devices),
                stats: @json($stats),
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
                                    if (data.stats) {
                                        this.stats = data.stats;
                                    }
                                }
                            })
                            .catch(() => {});
                        }
                    }, 2500);
                },
                togglePolling() {
                    this.isLiveActive = !this.isLiveActive;
                },
                getDeviceIcon(type) {
                    if (type === 'phone') return '📱';
                    if (type === 'laptop' || type === 'desktop' || type === 'computer') return '💻';
                    if (type === 'router') return '📡';
                    return '📟';
                },
                get filteredDevices() {
                    return this.devices.filter(d => {
                        // 1. Status Filter
                        if (this.statusFilter !== 'all' && d.status !== this.statusFilter) {
                            return false;
                        }

                        // 2. Device Type Filter
                        if (this.typeFilter === 'phone' && d.device_type !== 'phone') {
                            return false;
                        }
                        if (this.typeFilter === 'computer' && d.device_type !== 'laptop' && d.device_type !== 'desktop' && d.device_type !== 'computer') {
                            return false;
                        }

                        // 3. Search Query
                        if (!this.search) return true;

                        const mac = d.mac_address || '';
                        const ip = d.ip_address || '';
                        const name = d.username || '';
                        const devName = d.device_display_name || d.device_name || '';
                        const vendor = d.vendor || '';
                        const host = d.hostname || '';
                        const s = this.search.toLowerCase();

                        return mac.toLowerCase().includes(s) ||
                               ip.includes(s) ||
                               name.toLowerCase().includes(s) ||
                               devName.toLowerCase().includes(s) ||
                               vendor.toLowerCase().includes(s) ||
                               host.toLowerCase().includes(s);
                    });
                },
                inspectDevice(dev) {
                    this.selectedDevice = dev;
                    this.deviceDetail = null;
                    this.activeModalTab = 'info';
                    this.deviceAliasInput = dev.hostname || dev.device_display_name || '';
                    this.$dispatch('open-modal', 'session-inspector-modal');

                    // Fetch detail async
                    if (dev.mac_address && dev.mac_address !== '-') {
                        this.loadingDetail = true;
                        fetch(`/api/devices/${encodeURIComponent(dev.mac_address)}/detail?_t=` + Date.now(), {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.loadingDetail = false;
                            if (data && data.success) {
                                this.deviceDetail = data;
                            }
                        })
                        .catch(() => {
                            this.loadingDetail = false;
                        });
                    }
                    // Reset and preload web activity data
                    this.webActivityData = null;
                    this.webSearch = '';
                    this.loadWebActivity();
                },
                get filteredWebHistory() {
                    if (!this.webActivityData || !Array.isArray(this.webActivityData.history)) {
                        return [];
                    }
                    if (!this.webSearch) {
                        return this.webActivityData.history;
                    }
                    const q = this.webSearch.toLowerCase();
                    return this.webActivityData.history.filter(h => {
                        return (h.domain && h.domain.toLowerCase().includes(q)) ||
                               (h.site_name && h.site_name.toLowerCase().includes(q)) ||
                               (h.category_label && h.category_label.toLowerCase().includes(q));
                    });
                },
                loadWebActivity() {
                    if (!this.selectedDevice) return;
                    this.loadingWebActivity = true;

                    const mac = encodeURIComponent(this.selectedDevice.mac_address || '');
                    const ip = encodeURIComponent(this.selectedDevice.ip_address || '');
                    const user = encodeURIComponent(this.selectedDevice.username || '');

                    fetch(`/api/devices/${mac}/web-activity?ip=${ip}&username=${user}&_t=` + Date.now(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.loadingWebActivity = false;
                        if (data && data.success) {
                            this.webActivityData = data.data;
                        }
                    })
                    .catch(() => {
                        this.loadingWebActivity = false;
                    });
                },
                doMakeStatic() {
                    if (!this.selectedDevice || this.actionLoading) return;
                    this.actionLoading = true;

                    fetch('{{ route("devices.action.make-static") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            mac_address: this.selectedDevice.mac_address,
                            ip_address: this.selectedDevice.ip_address,
                            comment: this.deviceAliasInput || this.selectedDevice.hostname
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.actionLoading = false;
                        this.selectedDevice.dynamic = false;
                        alert(data.message);
                    })
                    .catch(() => {
                        this.actionLoading = false;
                        alert('Gagal mengeksekusi Make Static.');
                    });
                },
                doSetIpBinding(type) {
                    if (!this.selectedDevice || this.actionLoading) return;
                    this.actionLoading = true;

                    fetch('{{ route("devices.action.ip-binding") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            mac_address: this.selectedDevice.mac_address,
                            type: type,
                            comment: this.deviceAliasInput || this.selectedDevice.hostname
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.actionLoading = false;
                        if (!this.deviceDetail) this.deviceDetail = {};
                        this.deviceDetail.ip_binding = { type: type };
                        alert(data.message);
                    })
                    .catch(() => {
                        this.actionLoading = false;
                        alert('Gagal menyetel IP Binding.');
                    });
                },
                doKick() {
                    if (!this.selectedDevice || this.actionLoading) return;
                    if (!confirm('Putuskan sesi aktif user di perangkat ini?')) return;
                    this.actionLoading = true;

                    fetch('{{ route("devices.action.kick") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            mac_address: this.selectedDevice.mac_address,
                            username: this.selectedDevice.username
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.actionLoading = false;
                        this.selectedDevice.status = 'standby';
                        this.selectedDevice.username = null;
                        alert(data.message);
                    })
                    .catch(() => {
                        this.actionLoading = false;
                        alert('Gagal memutuskan sesi.');
                    });
                },
                doSaveComment() {
                    if (!this.selectedDevice || this.actionLoading || !this.deviceAliasInput) return;
                    this.actionLoading = true;

                    fetch('{{ route("devices.action.comment") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            mac_address: this.selectedDevice.mac_address,
                            comment: this.deviceAliasInput
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.actionLoading = false;
                        this.selectedDevice.hostname = this.deviceAliasInput;
                        alert(data.message);
                    })
                    .catch(() => {
                        this.actionLoading = false;
                        alert('Gagal menyimpan catatan perangkat.');
                    });
                },
                copyText(text, label) {
                    if (!text || text === '-') return;
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text).then(() => {
                            if (window.dispatchEvent) {
                                window.dispatchEvent(new CustomEvent('toast', { detail: { message: `${label} disalin!` } }));
                            }
                        }).catch(() => {
                            alert(`${label} disalin: ${text}`);
                        });
                    } else {
                        // Fallback
                        const input = document.createElement('input');
                        input.value = text;
                        document.body.appendChild(input);
                        input.select();
                        document.execCommand('copy');
                        document.body.removeChild(input);
                        alert(`${label} disalin: ${text}`);
                    }
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
