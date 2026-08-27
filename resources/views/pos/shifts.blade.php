<x-layouts.app>
    <x-slot:header>Shift & Kasir</x-slot:header>

    <div class="space-y-6" x-data="shiftsManager()">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Pengelolaan Shift Kasir</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Buka / tutup shift, rekonsiliasi kas fisik, dan deteksi selisih (discrepancy)</p>
            </div>
            <div class="flex items-center gap-2">
                <template x-if="currentShift.status === 'open'">
                    <button @click="openCloseShiftModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-xs">
                        Tutup Shift Sekarang
                    </button>
                </template>
                <template x-if="currentShift.status === 'closed'">
                    <button @click="openNewShiftModal()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900">
                        Buka Shift Baru
                    </button>
                </template>
            </div>
        </div>

        <!-- Current Shift Status Card -->
        <template x-if="currentShift">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100" x-text="'Shift Berjalan: #' + currentShift.id"></h2>
                    </div>
                    <x-badge type="online" dot="true">Sedang Aktif</x-badge>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mt-4 text-xs">
                    <div>
                        <span class="text-zinc-400">Kasir Bertugas:</span>
                        <div class="font-bold text-zinc-900 dark:text-zinc-100 text-sm mt-0.5" x-text="currentShift.user?.name || 'Administrator'"></div>
                    </div>
                    <div>
                        <span class="text-zinc-400">Waktu Buka:</span>
                        <div class="font-mono text-zinc-700 dark:text-zinc-300 mt-0.5" x-text="currentShift.opened_at"></div>
                    </div>
                    <div>
                        <span class="text-zinc-400">Modal Awal Laci:</span>
                        <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-sm mt-0.5" x-text="formatRupiah(currentShift.opening_cash || 0)"></div>
                    </div>
                    <div>
                        <span class="text-zinc-400">Expected Cash (Laci):</span>
                        <div class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-sm mt-0.5" x-text="formatRupiah(currentShift.expected_cash || 0)"></div>
                    </div>
                </div>
            </div>
        </template>

        <!-- No Active Shift State -->
        <template x-if="!currentShift">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Tidak Ada Shift Aktif</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Buka shift kasir baru dengan menginput modal awal untuk mulai mencatat transaksi fisik kas.</p>
                <button @click="openNewShiftModal()" class="mt-4 inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900">
                    + Buka Shift Baru
                </button>
            </div>
        </template>

        <!-- Buka Shift Modal -->
        <x-modal name="open-shift-modal" title="Buka Shift Kasir Baru">
            <form @submit.prevent="confirmOpenShift()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Modal Awal Kas di Laci (Rp) *</label>
                    <input type="number" x-model="openingCash" required min="0" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono font-bold" placeholder="200000">
                </div>
                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="$dispatch('close-modal', 'open-shift-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-semibold">Buka Shift</button>
                </div>
            </form>
        </x-modal>

        <!-- Tutup Shift Modal -->
        <x-modal name="close-shift-modal" title="Tutup Shift & Hitung Kas Fisik">
            <form @submit.prevent="confirmCloseShift()" class="space-y-4 text-xs">
                <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg space-y-1">
                    <div class="flex justify-between">
                        <span>Expected Cash (Seharusnya di Laci):</span>
                        <strong class="font-mono text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(currentShift?.expected_cash || 0)"></strong>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Uang Fisik Kas di Laci Saat Ini (Rp) *</label>
                    <input type="number" x-model="actualCash" @input="calculateDiscrepancy()" required class="w-full px-3 py-2 text-sm font-mono font-bold rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                </div>

                <div class="p-3 rounded-lg border text-xs" :class="discrepancy === 0 ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-700 dark:text-emerald-400' : 'bg-rose-500/10 border-rose-500/20 text-rose-700 dark:text-rose-400'">
                    <div class="font-semibold">Selisih Kas Fisik:</div>
                    <div class="font-mono text-base font-bold mt-1" x-text="formatRupiah(discrepancy)"></div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="$dispatch('close-modal', 'close-shift-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold">Konfirmasi Tutup Shift</button>
                </div>
            </form>
        </x-modal>
    </div>

    @push('scripts')
    <script>
        function shiftsManager() {
            return {
                currentShift: @json($currentShift ?? null),
                pastShifts: @json($pastShifts ?? []),
                openingCash: 0,
                actualCash: 0,
                discrepancy: 0,
                calculateDiscrepancy() {
                    const expected = this.currentShift ? (this.currentShift.expected_cash || 0) : 0;
                    this.discrepancy = (this.actualCash || 0) - expected;
                },
                openNewShiftModal() {
                    this.openingCash = 0;
                    this.$dispatch('open-modal', 'open-shift-modal');
                },
                confirmOpenShift() {
                    fetch('/pos/shifts/open', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ opening_cash: this.openingCash })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.currentShift = data.shift;
                            this.$dispatch('close-modal', 'open-shift-modal');
                            window.showToast('Shift baru berhasil dibuka', 'success');
                        } else {
                            window.showToast(data.message || 'Gagal membuka shift', 'error');
                        }
                    })
                    .catch(err => {
                        window.showToast('Terjadi kesalahan jaringan', 'error');
                    });
                },
                openCloseShiftModal() {
                    this.actualCash = this.currentShift ? (this.currentShift.expected_cash || 0) : 0;
                    this.calculateDiscrepancy();
                    this.$dispatch('open-modal', 'close-shift-modal');
                },
                confirmCloseShift() {
                    fetch('/pos/shifts/close', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ actual_cash: this.actualCash })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.pastShifts.unshift(data.shift);
                            this.currentShift = null;
                            this.$dispatch('close-modal', 'close-shift-modal');
                            window.showToast('Shift telah berhasil ditutup dan direkonsiliasi', 'success');
                        } else {
                            window.showToast(data.message || 'Gagal menutup shift', 'error');
                        }
                    })
                    .catch(err => {
                        window.showToast('Terjadi kesalahan jaringan', 'error');
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
