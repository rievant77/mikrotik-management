<x-layouts.app>
    <x-slot:header>Pemeliharaan & Reset Data</x-slot:header>

    <div class="space-y-6" x-data="maintenanceManager()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Pemeliharaan & Reset Data</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Pembersihan dan reset data operasional, transaksi POS, pengguna hotspot, dan counter internet secara aman</p>
            </div>
            <div>
                <button type="button" @click="refreshCounts()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                    <svg class="w-3.5 h-3.5" :class="isRefreshing ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Refresh Statistik</span>
                </button>
            </div>
        </div>

        <!-- Warning Alert Banner -->
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-800 dark:text-amber-300 text-xs flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <strong class="font-bold">Perhatian Penting:</strong> Tindakan reset data bersifat permanen dan tidak dapat dibatalkan. Pastikan Anda telah mempertimbangkan modul data mana yang ingin dibersihkan sebelum melanjutkan.
            </div>
        </div>

        <!-- Database Records Summary Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <div class="flex items-center justify-between text-xs text-zinc-400 font-bold uppercase">
                    <span>Transaksi POS & Kas</span>
                    <span>💳</span>
                </div>
                <div class="mt-2 text-2xl font-bold font-mono text-zinc-900 dark:text-zinc-100" x-text="((counts.voucher_sales || 0) + (counts.monthly_invoices || 0) + (counts.monthly_payments || 0)) + ' Record'"></div>
                <div class="mt-2 text-[11px] text-zinc-500 flex flex-col gap-0.5">
                    <div class="flex justify-between"><span>Penjualan Voucher:</span> <strong class="font-mono text-zinc-700 dark:text-zinc-300" x-text="counts.voucher_sales || 0"></strong></div>
                    <div class="flex justify-between"><span>Tagihan Bulanan:</span> <strong class="font-mono text-zinc-700 dark:text-zinc-300" x-text="counts.monthly_invoices || 0"></strong></div>
                    <div class="flex justify-between"><span>Pembayaran Masuk:</span> <strong class="font-mono text-zinc-700 dark:text-zinc-300" x-text="counts.monthly_payments || 0"></strong></div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <div class="flex items-center justify-between text-xs text-zinc-400 font-bold uppercase">
                    <span>Pengguna & Pelanggan</span>
                    <span>👥</span>
                </div>
                <div class="mt-2 text-2xl font-bold font-mono text-zinc-900 dark:text-zinc-100" x-text="((counts.hotspot_users || 0) + (counts.monthly_customers || 0)) + ' User'"></div>
                <div class="mt-2 text-[11px] text-zinc-500 flex flex-col gap-0.5">
                    <div class="flex justify-between"><span>Hotspot Users:</span> <strong class="font-mono text-zinc-700 dark:text-zinc-300" x-text="counts.hotspot_users || 0"></strong></div>
                    <div class="flex justify-between"><span>Pelanggan Bulanan:</span> <strong class="font-mono text-zinc-700 dark:text-zinc-300" x-text="counts.monthly_customers || 0"></strong></div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <div class="flex items-center justify-between text-xs text-zinc-400 font-bold uppercase">
                    <span>Traffic & Log FUP</span>
                    <span>⚡</span>
                </div>
                <div class="mt-2 text-2xl font-bold font-mono text-zinc-900 dark:text-zinc-100" x-text="((counts.daily_summaries || 0) + (counts.hourly_samples || 0) + (counts.fup_trackers || 0)) + ' Log'"></div>
                <div class="mt-2 text-[11px] text-zinc-500 flex flex-col gap-0.5">
                    <div class="flex justify-between"><span>Daily Traffic:</span> <strong class="font-mono text-zinc-700 dark:text-zinc-300" x-text="counts.daily_summaries || 0"></strong></div>
                    <div class="flex justify-between"><span>FUP Trackers:</span> <strong class="font-mono text-zinc-700 dark:text-zinc-300" x-text="counts.fup_trackers || 0"></strong></div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <div class="flex items-center justify-between text-xs text-zinc-400 font-bold uppercase">
                    <span>Audit Log Sistem</span>
                    <span>📜</span>
                </div>
                <div class="mt-2 text-2xl font-bold font-mono text-zinc-900 dark:text-zinc-100" x-text="(counts.audit_logs || 0) + ' Log'"></div>
                <div class="mt-2 text-[11px] text-zinc-500">
                    Aktivitas sistem & riwayat aksi admin
                </div>
            </div>
        </div>

        <!-- 4 Modular Action Reset Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 1. Reset Penjualan & POS -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">1. Reset Data Penjualan & Keuangan POS</h3>
                            <p class="text-[11px] text-zinc-400">Hapus riwayat penjualan voucher, tagihan bulanan, dan pembayaran kasir</p>
                        </div>
                    </div>
                    <div class="py-4 space-y-3 text-xs text-zinc-600 dark:text-zinc-400">
                        <p>Membersihkan tabel <code class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 font-mono text-[11px]">voucher_sales</code>, <code class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 font-mono text-[11px]">monthly_invoices</code>, dan <code class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 font-mono text-[11px]">monthly_payments</code> untuk memulai buku kas baru.</p>
                        <div>
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Opsi Tanggal Batas (Opsional)</label>
                            <input type="date" x-model="salesResetDate" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs">
                            <span class="text-[10px] text-zinc-400 mt-1 block">Biarkan kosong untuk menghapus SELURUH riwayat penjualan.</span>
                        </div>
                    </div>
                </div>
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="confirmAction('reset_sales', 'Reset Data Penjualan & POS', 'Menghapus seluruh riwayat penjualan voucher dan pembayaran bulanan.')" class="w-full py-2.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs transition-colors shadow-xs">
                        Reset Data Penjualan & POS
                    </button>
                </div>
            </div>

            <!-- 2. Reset Pengguna Hotspot & Voucher -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">2. Reset Pengguna Hotspot & Voucher</h3>
                            <p class="text-[11px] text-zinc-400">Hapus seluruh user hotspot di database dan router MikroTik</p>
                        </div>
                    </div>
                    <div class="py-4 space-y-3 text-xs text-zinc-600 dark:text-zinc-400">
                        <p>Menghapus user hotspot yang ada di database aplikasi serta menjalankan perintah <code class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 font-mono text-[11px]">/ip/hotspot/user/remove</code> di MikroTik.</p>
                        <div class="space-y-2 pt-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="usersDeleteRouter" class="rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">Hapus juga dari Router MikroTik (Sinkronisasi Langsung)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="usersIncludeMonthly" class="rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">Sertakan juga Pelanggan Bulanan (Master Customers)</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="confirmAction('reset_users', 'Reset Pengguna Hotspot & Voucher', 'Menghapus seluruh akun hotspot user di aplikasi dan MikroTik.')" class="w-full py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition-colors shadow-xs">
                        Reset Pengguna Hotspot
                    </button>
                </div>
            </div>

            <!-- 3. Reset Counter Internet & Kuota FUP -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">3. Reset Counter Internet & Kuota FUP</h3>
                            <p class="text-[11px] text-zinc-400">Kembalikan traffic bytes ke 0 & pulihkan kecepatan normal FUP</p>
                        </div>
                    </div>
                    <div class="py-4 space-y-3 text-xs text-zinc-600 dark:text-zinc-400">
                        <p>Mengeksekusi <code class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 font-mono text-[11px]">/ip/hotspot/user/reset-counters</code> di router MikroTik, membersihkan ringkasan traffic harian, dan mereset status FUP user.</p>
                        <div>
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Target Username Tertentu (Opsional)</label>
                            <input type="text" x-model="targetCounterUsername" placeholder="e.g. budi-rumah-01 (Kosongkan untuk Semua User)" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs">
                        </div>
                    </div>
                </div>
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="confirmAction('reset_counters', 'Reset Counter Internet & FUP', 'Mereset akumulasi bytes traffic pemakaian internet dan kuota FUP ke 0.')" class="w-full py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition-colors shadow-xs">
                        Reset Counter Internet & FUP
                    </button>
                </div>
            </div>

            <!-- 4. Reset Log Audit Sistem -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="p-2.5 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">4. Bersihkan Audit Log Sistem</h3>
                            <p class="text-[11px] text-zinc-400">Hapus riwayat aktivitas dan pencatatan audit log</p>
                        </div>
                    </div>
                    <div class="py-4 space-y-3 text-xs text-zinc-600 dark:text-zinc-400">
                        <p>Membersihkan rekaman riwayat aktivitas admin di tabel <code class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 font-mono text-[11px]">audit_logs</code>.</p>
                        <div>
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Hapus Log Sebelum Tanggal (Opsional)</label>
                            <input type="date" x-model="auditResetDate" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs">
                        </div>
                    </div>
                </div>
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="confirmAction('reset_audit', 'Bersihkan Audit Log', 'Menghapus riwayat catatan aktivitas audit log.')" class="w-full py-2.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs transition-colors shadow-xs">
                        Bersihkan Log Audit
                    </button>
                </div>
            </div>
        </div>

        <!-- Danger Zone: Full Factory Reset -->
        <div class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 rounded-xl p-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-600 text-white uppercase">Zona Bahaya</span>
                        <h3 class="text-base font-bold text-rose-900 dark:text-rose-200">Factory Reset Sistem Total</h3>
                    </div>
                    <p class="text-xs text-rose-700 dark:text-rose-300 max-w-xl">
                        Menghapus dan membersihkan <strong>SELURUH data operasional sekaligus</strong>: Data Penjualan & POS, Semua User Hotspot & Pelanggan Bulanan di DB & MikroTik, Counter Internet, Log Traffic, dan Siklus FUP.
                    </p>
                </div>
                <button type="button" @click="confirmAction('factory_reset', 'FACTORY RESET SISTEM TOTAL', 'PERINGATAN TERTINGGI: Seluruh data operasional penjualan, pelanggan, dan router akan dibersihkan secara total!')" class="px-5 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shrink-0 transition-colors shadow-sm">
                    🔥 Factory Reset Total
                </button>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <x-modal name="confirm-reset-modal" title="Konfirmasi Tindakan Reset">
            <form @submit.prevent="executeConfirmedReset()" class="space-y-4 text-xs">
                <div class="p-3.5 bg-rose-50 dark:bg-rose-950/40 rounded-xl border border-rose-200 dark:border-rose-900/40 space-y-1">
                    <strong class="font-bold text-rose-900 dark:text-rose-200 text-sm block" x-text="activeActionTitle"></strong>
                    <p class="text-rose-700 dark:text-rose-300 text-xs" x-text="activeActionDescription"></p>
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                        Ketik kata <span class="font-mono font-bold text-rose-600 bg-rose-50 dark:bg-rose-950/60 px-1.5 py-0.5 rounded border border-rose-200 dark:border-rose-900/60">RESET</span> untuk mengonfirmasi:
                    </label>
                    <input 
                        type="text" 
                        x-model="confirmationInput" 
                        required 
                        placeholder="Ketik RESET disini..." 
                        class="w-full px-3 py-2.5 text-sm font-mono font-bold rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 uppercase"
                    >
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="$dispatch('close-modal', 'confirm-reset-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-medium">Batal</button>
                    <button 
                        type="submit" 
                        :disabled="confirmationInput.trim().toUpperCase() !== 'RESET' || isProcessing" 
                        :class="confirmationInput.trim().toUpperCase() === 'RESET' && !isProcessing ? 'bg-rose-600 hover:bg-rose-700 text-white font-bold cursor-pointer' : 'bg-zinc-300 dark:bg-zinc-800 text-zinc-400 cursor-not-allowed'"
                        class="px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                    >
                        <span x-show="isProcessing">Memproses Reset...</span>
                        <span x-show="!isProcessing">Lanjutkan Eksekusi Reset</span>
                    </button>
                </div>
            </form>
        </x-modal>
    </div>

    @push('scripts')
    <script>
        function maintenanceManager() {
            return {
                counts: @json($counts ?? []),
                isRefreshing: false,
                isProcessing: false,
                activeAction: '',
                activeActionTitle: '',
                activeActionDescription: '',
                confirmationInput: '',
                salesResetDate: '',
                auditResetDate: '',
                targetCounterUsername: '',
                usersDeleteRouter: true,
                usersIncludeMonthly: false,

                refreshCounts() {
                    this.isRefreshing = true;
                    fetch('{{ route("settings.maintenance") }}', {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isRefreshing = false;
                        if (data.counts) {
                            this.counts = data.counts;
                            window.showToast('Statistik data berhasil diperbarui', 'info');
                        }
                    })
                    .catch(() => {
                        this.isRefreshing = false;
                        window.location.reload();
                    });
                },

                confirmAction(action, title, desc) {
                    this.activeAction = action;
                    this.activeActionTitle = title;
                    this.activeActionDescription = desc;
                    this.confirmationInput = '';
                    this.$dispatch('open-modal', 'confirm-reset-modal');
                },

                executeConfirmedReset() {
                    if (this.confirmationInput.trim().toUpperCase() !== 'RESET') return;

                    this.isProcessing = true;
                    const payload = {
                        action: this.activeAction,
                        confirmation_text: this.confirmationInput.trim().toUpperCase(),
                        before_date: this.activeAction === 'reset_sales' ? this.salesResetDate : (this.activeAction === 'reset_audit' ? this.auditResetDate : null),
                        target_username: this.targetCounterUsername || null,
                        delete_from_router: this.usersDeleteRouter,
                        include_monthly_customers: this.usersIncludeMonthly
                    };

                    fetch('{{ route("settings.maintenance.reset") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isProcessing = false;
                        if (data.success) {
                            if (data.counts) {
                                this.counts = data.counts;
                            }
                            this.$dispatch('close-modal', 'confirm-reset-modal');
                            window.showToast(data.message || 'Operasi reset berhasil dijalankan!', 'success');
                        } else {
                            window.showToast(data.message || 'Gagal menjalankan reset', 'error');
                        }
                    })
                    .catch(() => {
                        this.isProcessing = false;
                        window.showToast('Terjadi kesalahan koneksi saat menjalankan reset', 'error');
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
