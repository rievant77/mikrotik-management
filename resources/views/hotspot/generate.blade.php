<x-layouts.app>
    <x-slot:header>Batch Voucher Generator</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6" x-data="voucherGenerator()">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Generate Voucher Massal (Batch)</h1>
            <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Buat batch voucher baru untuk inventory penjualan kasir</p>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs">
            <form @submit.prevent="generateVouchers()" class="space-y-6 text-xs">
                <!-- Row 1: Profile & Quantity -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Pilih Profile / Paket *</label>
                        <select x-model="form.profile" required class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs focus:ring-2 focus:ring-zinc-400">
                            <option value="Paket-3Jam">Paket-3Jam (Rp3.000 / 3 Jam)</option>
                            <option value="Paket-24Jam">Paket-24Jam (Rp10.000 / 24 Jam)</option>
                            <option value="VIP-10M">VIP-10M (Rp250.000 / 30 Hari)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Jumlah Voucher (Qty) *</label>
                        <input type="number" x-model="form.qty" min="1" max="1000" required class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs focus:ring-2 focus:ring-zinc-400">
                    </div>
                </div>

                <!-- Row 2: Credential Format & Character Set -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Format Kredensial *</label>
                        <select x-model="form.credentialMode" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs">
                            <option value="same">Username = Password</option>
                            <option value="different">Username & Password Terpisah</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Tipe Karakter Kode *</label>
                        <select x-model="form.charSet" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs">
                            <option value="numeric">Angka Saja (12345)</option>
                            <option value="lowercase">Huruf Kecil (abcde)</option>
                            <option value="uppercase">Huruf Besar (ABCDE)</option>
                            <option value="mixed">Kombinasi Huruf & Angka (aB3k9)</option>
                        </select>
                    </div>
                </div>

                <!-- Row 3: Prefix & Code Length -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Prefix Kode (Opsional)</label>
                        <input type="text" x-model="form.prefix" placeholder="e.g. NET-" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Panjang Kode Karakter</label>
                        <input type="number" x-model="form.length" min="3" max="12" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs">
                    </div>
                </div>

                <!-- Sample Preview Box -->
                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700/80">
                    <span class="text-zinc-400 text-[11px] uppercase font-semibold">Contoh Tampilan Kode:</span>
                    <div class="mt-2 flex items-center gap-3">
                        <span class="font-mono text-base font-bold text-zinc-900 dark:text-zinc-100 tracking-wider" x-text="sampleCode"></span>
                        <span class="text-zinc-400">|</span>
                        <span class="text-zinc-600 dark:text-zinc-300" x-text="form.profile"></span>
                    </div>
                </div>

                <!-- Submit and Print Action -->
                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <a href="{{ route('vouchers.print.grid') }}" class="w-full sm:w-auto px-4 py-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-medium text-center hover:bg-zinc-200 transition-colors">
                        Buka Layout Cetak
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 font-semibold shadow-xs transition-colors">
                        Simpan & Generate Batch
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function voucherGenerator() {
            return {
                form: {
                    profile: 'Paket-3Jam',
                    qty: 50,
                    credentialMode: 'same',
                    charSet: 'numeric',
                    prefix: 'VC-',
                    length: 5
                },
                get sampleCode() {
                    const pref = this.form.prefix || '';
                    if (this.form.charSet === 'numeric') return pref + '84920';
                    if (this.form.charSet === 'uppercase') return pref + 'KWXPZ';
                    if (this.form.charSet === 'lowercase') return pref + 'mxtya';
                    return pref + '7xK9A';
                },
                generateVouchers() {
                    window.showToast(`${this.form.qty} Voucher berhasil digenerate ke inventory!`, 'success');
                    setTimeout(() => {
                        window.location.href = "{{ route('vouchers.print.grid') }}";
                    }, 1200);
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
