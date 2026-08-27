<x-layouts.app>
    <x-slot:header>Router Settings & Diagnostics</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6" x-data="routerSettings()">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Koneksi Router MikroTik</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Konfigurasi single-router RouterOS API (Port 8728 / SSL 8729)</p>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    @click="syncAllData()" 
                    :disabled="syncing" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs"
                >
                    <svg class="w-4 h-4 text-zinc-500" :class="{ 'animate-spin': syncing }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="syncing ? 'Sinkronisasi...' : 'Sinkronkan Semua Data'"></span>
                </button>
                <button @click="testConnection()" :disabled="testing" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                    <svg x-show="!testing" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <svg x-show="testing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="testing ? 'Menguji Koneksi...' : 'Test Connection'"></span>
                </button>
            </div>
        </div>

        <!-- Connection Form -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs">
            <form @submit.prevent="saveSettings()" class="space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nama Router Identity *</label>
                        <input type="text" x-model="form.name" required class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">IP Address / Hostname *</label>
                        <input type="text" x-model="form.host" required class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">API Port *</label>
                        <input type="number" x-model="form.api_port" required class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Username API *</label>
                        <input type="text" x-model="form.username" required class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Password API</label>
                        <input type="password" x-model="form.password" placeholder="Kosongkan jika tidak diubah" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="ssl" x-model="form.use_ssl" class="rounded border-zinc-300 dark:border-zinc-700 text-zinc-900 focus:ring-zinc-500">
                        <label for="ssl" class="font-medium text-zinc-700 dark:text-zinc-300">Gunakan API-SSL (Port 8729)</label>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="active" x-model="form.is_active" class="rounded border-zinc-300 dark:border-zinc-700 text-zinc-900 focus:ring-zinc-500">
                        <label for="active" class="font-medium text-zinc-700 dark:text-zinc-300">Aktifkan Polling Realtime Collector</label>
                    </div>
                </div>

                <!-- Test Connection Diagnostics Box -->
                <template x-if="diagnosticResult">
                    <div class="mt-4 p-4 rounded-xl border text-xs" :class="diagnosticResult.success ? 'bg-zinc-50 dark:bg-zinc-800/50 border-emerald-500/30' : 'bg-rose-50 dark:bg-rose-950/40 border-rose-500/30'">
                        <div class="flex items-center gap-2 font-bold mb-2" :class="diagnosticResult.success ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                            <span x-text="diagnosticResult.success ? '✓ Tes Koneksi Berhasil!' : '✗ Gagal Terhubung ke Router'"></span>
                        </div>
                        <template x-if="diagnosticResult.success">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-zinc-600 dark:text-zinc-300 font-mono text-[11px]">
                                <div>Identity: <strong class="text-zinc-900 dark:text-zinc-100" x-text="diagnosticResult.identity"></strong></div>
                                <div>RouterOS: <strong class="text-zinc-900 dark:text-zinc-100" x-text="diagnosticResult.version"></strong></div>
                                <div>Board: <strong class="text-zinc-900 dark:text-zinc-100" x-text="diagnosticResult.board_name"></strong></div>
                                <div>Latency: <strong class="text-emerald-600" x-text="diagnosticResult.latency_ms + ' ms'"></strong></div>
                            </div>
                        </template>
                        <template x-if="!diagnosticResult.success">
                            <p class="text-rose-600 dark:text-rose-400 text-xs" x-text="diagnosticResult.message"></p>
                        </template>
                    </div>
                </template>

                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="submit" :disabled="saving" class="px-6 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 font-semibold shadow-xs">
                        <span x-text="saving ? 'Menyimpan...' : 'Simpan Konfigurasi'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function routerSettings() {
            return {
                testing: false,
                saving: false,
                syncing: false,
                diagnosticResult: null,
                form: {
                    name: '{{ $setting->name ?? "CCR1009-Core" }}',
                    host: '{{ $setting->host ?? "192.168.88.1" }}',
                    api_port: {{ $setting->api_port ?? 8728 }},
                    username: '{{ $setting->username ?? "admin" }}',
                    password: '',
                    use_ssl: {{ $setting->use_ssl ? 'true' : 'false' }},
                    is_active: {{ $setting->is_active ? 'true' : 'false' }}
                },
                testConnection() {
                    this.testing = true;
                    this.diagnosticResult = null;

                    fetch('{{ route("settings.router.test") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.testing = false;
                        this.diagnosticResult = data;
                        if (data.success) {
                            window.showToast('Koneksi ke MikroTik berhasil diverifikasi', 'success');
                        } else {
                            window.showToast(data.message || 'Gagal terhubung ke router', 'error');
                        }
                    })
                    .catch(err => {
                        this.testing = false;
                        window.showToast('Terjadi kesalahan saat menguji koneksi', 'error');
                    });
                },
                saveSettings() {
                    this.saving = true;
                    fetch('{{ route("settings.router.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.saving = false;
                        if (data.success) {
                            window.showToast('Pengaturan router berhasil disimpan', 'success');
                        } else {
                            window.showToast(data.message || 'Gagal menyimpan pengaturan', 'error');
                        }
                    })
                    .catch(err => {
                        this.saving = false;
                        window.showToast('Gagal menyimpan pengaturan', 'error');
                    });
                },
                syncAllData() {
                    this.syncing = true;
                    fetch('{{ route("settings.router.sync") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.syncing = false;
                        if (data.success) {
                            window.showToast(data.message, 'success');
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            window.showToast(data.message || 'Gagal sinkronisasi data', 'error');
                        }
                    })
                    .catch(err => {
                        this.syncing = false;
                        window.showToast('Gagal terhubung ke router MikroTik', 'error');
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
