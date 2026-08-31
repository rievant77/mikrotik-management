<div 
    x-data="commandPalette()" 
    x-on:open-search.window="openSearch()"
    x-on:keydown.window.ctrl.k.prevent="openSearch()"
    x-on:keydown.window.cmd.k.prevent="openSearch()"
    x-on:keydown.escape.window="closeSearch()"
    x-show="isOpen" 
    class="relative z-50"
    style="display: none;"
>
    <!-- Backdrop Blur Overlay -->
    <div 
        x-show="isOpen" 
        x-transition:enter="ease-out duration-200" 
        x-transition:enter-start="opacity-0" 
        x-transition:enter-end="opacity-100" 
        x-transition:leave="ease-in duration-150" 
        x-transition:leave-start="opacity-100" 
        x-transition:leave-end="opacity-0" 
        class="fixed inset-0 bg-zinc-950/70 backdrop-blur-xs transition-opacity" 
        @click="closeSearch()"
    ></div>

    <!-- Modal Dialog -->
    <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20">
        <div 
            x-show="isOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0 scale-95 translate-y-2" 
            x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
            x-transition:leave="ease-in duration-150" 
            x-transition:leave-start="opacity-100 scale-100 translate-y-0" 
            x-transition:leave-end="opacity-0 scale-95 translate-y-2" 
            @click.away="closeSearch()"
            class="mx-auto max-w-2xl transform divide-y divide-zinc-200 dark:divide-zinc-800 overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 shadow-2xl ring-1 ring-black/5 transition-all border border-zinc-200 dark:border-zinc-800"
        >
            <!-- Search Input Header -->
            <div class="relative flex items-center px-4 py-3 sm:px-6">
                <svg class="pointer-events-none h-5 w-5 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input 
                    type="text" 
                    x-ref="searchInput"
                    x-model="searchQuery" 
                    @input.debounce.200ms="performSearch()"
                    placeholder="Cari user hotspot, IP, MAC address, invoice, atau menu..." 
                    class="h-10 w-full bg-transparent border-0 pl-3 pr-10 text-sm text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 focus:outline-none focus:ring-0"
                >
                <div class="flex items-center gap-2">
                    <!-- Loading Spinner -->
                    <svg x-show="isLoading" class="animate-spin h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <button @click="closeSearch()" class="text-xs px-2 py-1 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-500 font-mono border border-zinc-200 dark:border-zinc-700">ESC</button>
                </div>
            </div>

            <!-- Results Body -->
            <div class="max-h-96 overflow-y-auto px-4 py-4 sm:px-6 space-y-4">
                <!-- Group 1: Hotspot Users -->
                <template x-if="results.users && results.users.length > 0">
                    <div>
                        <div class="px-2 pb-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-wider flex items-center justify-between">
                            <span>👥 Pengguna Hotspot</span>
                            <span class="text-[10px] lowercase" x-text="results.users.length + ' ditemukan'"></span>
                        </div>
                        <div class="space-y-1">
                            <template x-for="user in results.users" :key="user.id || user.title">
                                <a :href="user.url" class="group flex items-center justify-between px-3 py-2 rounded-xl text-xs hover:bg-zinc-100 dark:hover:bg-zinc-800/80 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">
                                            <span x-text="user.title.substring(0, 2).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 block" x-text="user.title"></span>
                                            <span class="text-[11px] text-zinc-400 block" x-text="user.subtitle"></span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 font-medium">Buka Profil ➔</span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Group 2: Devices & DHCP Leases -->
                <template x-if="results.devices && results.devices.length > 0">
                    <div>
                        <div class="px-2 pb-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-wider flex items-center justify-between">
                            <span>🖥️ Perangkat Jaringan (IP / MAC)</span>
                            <span class="text-[10px] lowercase" x-text="results.devices.length + ' ditemukan'"></span>
                        </div>
                        <div class="space-y-1">
                            <template x-for="device in results.devices" :key="device.title">
                                <a :href="device.url" class="group flex items-center justify-between px-3 py-2 rounded-xl text-xs hover:bg-zinc-100 dark:hover:bg-zinc-800/80 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 block" x-text="device.title"></span>
                                            <span class="text-[11px] text-zinc-400 block font-mono" x-text="device.subtitle"></span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 font-medium">Lihat di Live Devices ➔</span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Group 3: Customers & Invoices -->
                <template x-if="results.customers && results.customers.length > 0">
                    <div>
                        <div class="px-2 pb-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-wider flex items-center justify-between">
                            <span>🧾 Pelanggan & Tagihan Bulanan</span>
                            <span class="text-[10px] lowercase" x-text="results.customers.length + ' ditemukan'"></span>
                        </div>
                        <div class="space-y-1">
                            <template x-for="item in results.customers" :key="item.id || item.title">
                                <a :href="item.url" class="group flex items-center justify-between px-3 py-2 rounded-xl text-xs hover:bg-zinc-100 dark:hover:bg-zinc-800/80 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 block" x-text="item.title"></span>
                                            <span class="text-[11px] text-zinc-400 block" x-text="item.subtitle"></span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 font-medium">Buka Kasir ➔</span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Group 4: Navigation / Quick Jump -->
                <template x-if="results.navigation && results.navigation.length > 0">
                    <div>
                        <div class="px-2 pb-1.5 text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">
                            <span>⚡ Menu & Navigasi Cepat</span>
                        </div>
                        <div class="space-y-1">
                            <template x-for="nav in results.navigation" :key="nav.url">
                                <a :href="nav.url" class="group flex items-center justify-between px-3 py-2 rounded-xl text-xs hover:bg-zinc-100 dark:hover:bg-zinc-800/80 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 group-hover:bg-zinc-900 group-hover:text-white dark:group-hover:bg-zinc-100 dark:group-hover:text-zinc-900 flex items-center justify-center shrink-0 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="font-bold text-zinc-900 dark:text-zinc-100 block" x-text="nav.title"></span>
                                            <span class="text-[11px] text-zinc-400 block" x-text="nav.description"></span>
                                        </div>
                                    </div>
                                    <span class="text-zinc-400 text-xs group-hover:text-zinc-600 dark:group-hover:text-zinc-200">↵</span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <template x-if="searchQuery.trim() !== '' && !isLoading && isAllEmpty()">
                    <div class="py-12 text-center">
                        <svg class="mx-auto h-8 w-8 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300">Tidak ada hasil ditemukan</p>
                        <p class="text-[11px] text-zinc-400 mt-0.5">Coba cari dengan kata kunci lain seperti username, alamat IP, nomor invoice, atau nama menu</p>
                    </div>
                </template>
            </div>

            <!-- Footer Hints -->
            <div class="flex items-center justify-between px-4 py-2.5 sm:px-6 bg-zinc-50 dark:bg-zinc-800/50 text-[11px] text-zinc-400">
                <div class="flex items-center gap-4">
                    <span>Tekan <kbd class="font-mono bg-white dark:bg-zinc-700 px-1.5 py-0.5 rounded border border-zinc-200 dark:border-zinc-600 text-zinc-600 dark:text-zinc-300">↵</kbd> untuk memilih</span>
                    <span><kbd class="font-mono bg-white dark:bg-zinc-700 px-1.5 py-0.5 rounded border border-zinc-200 dark:border-zinc-600 text-zinc-600 dark:text-zinc-300">ESC</kbd> untuk menutup</span>
                </div>
                <span class="hidden sm:inline">Pencarian Universal MikroTik</span>
            </div>
        </div>
    </div>
</div>

<script>
    function commandPalette() {
        return {
            isOpen: false,
            searchQuery: '',
            isLoading: false,
            results: {
                navigation: [],
                users: [],
                customers: [],
                devices: []
            },

            init() {
                // Fetch initial default navigation
                this.performSearch();
            },

            openSearch() {
                this.isOpen = true;
                this.$nextTick(() => {
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                        this.$refs.searchInput.select();
                    }
                });
            },

            closeSearch() {
                this.isOpen = false;
            },

            isAllEmpty() {
                return (!this.results.users || this.results.users.length === 0) &&
                       (!this.results.customers || this.results.customers.length === 0) &&
                       (!this.results.devices || this.results.devices.length === 0) &&
                       (!this.results.navigation || this.results.navigation.length === 0);
            },

            performSearch() {
                this.isLoading = true;
                fetch('{{ route("api.search.global") }}?q=' + encodeURIComponent(this.searchQuery), {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    this.isLoading = false;
                    if (data.results) {
                        this.results = data.results;
                    }
                })
                .catch(() => {
                    this.isLoading = false;
                });
            }
        };
    }
</script>
