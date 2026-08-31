<x-layouts.app>
    <x-slot:header>Batch Voucher Generator</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6" x-data="voucherGenerator()">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Generate Voucher Massal (Batch)</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Buat batch voucher baru untuk inventory penjualan kasir & router MikroTik</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vouchers.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs">
                    <span>🎟️ Lihat Daftar Voucher</span>
                </a>
                <a href="{{ route('hotspot.users') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs">
                    <span>User Hotspot</span>
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs">
            <form @submit.prevent="generateVouchers()" class="space-y-5 text-xs">
                
                <!-- Row 1: Quantity & Server -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                            1. Jumlah Voucher (Qty) *
                        </label>
                        <input type="number" x-model="form.qty" min="1" max="1000" required class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs focus:ring-2 focus:ring-zinc-400">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                            2. Server Hotspot *
                        </label>
                        <select x-model="form.server" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs focus:ring-2 focus:ring-zinc-400">
                            <option value="all">all (Semua Server Hotspot)</option>
                            <template x-for="srv in servers" :key="srv.name || srv['.id']">
                                <option :value="srv.name" x-text="srv.name + (srv.interface ? ' (' + srv.interface + ')' : '')"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Row 2: User Mode & Name Length -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                            3. User Mode (Format Kredensial) *
                        </label>
                        <select x-model="form.credentialMode" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs">
                            <option value="same">Username = Password</option>
                            <option value="different">Username & Password Terpisah</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                            4. Name Length (Panjang Karakter) *
                        </label>
                        <input type="number" x-model="form.length" min="3" max="12" required class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs">
                    </div>
                </div>

                <!-- Row 3: Prefix & Characters -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                            5. Prefix (Awalan Kode - Opsional)
                        </label>
                        <input type="text" x-model="form.prefix" placeholder="Kosongkan jika tanpa prefix (e.g. VC-)" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                            6. Character (Tipe Karakter Kode) *
                        </label>
                        <select x-model="form.charSet" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs">
                            <option value="numeric">Angka Saja (12345)</option>
                            <option value="lowercase">Huruf Kecil (abcde)</option>
                            <option value="uppercase">Huruf Besar (ABCDE)</option>
                            <option value="mixed">Kombinasi Huruf & Angka (aB3k9)</option>
                        </select>
                    </div>
                </div>

                <!-- Row 4: Profile & Time Limit -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                            7. Profile (Paket Hotspot) *
                        </label>
                        <select x-model="form.profile" @change="onProfileChange()" required class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs focus:ring-2 focus:ring-zinc-400">
                            <template x-for="p in profiles" :key="p.id">
                                <option :value="p.name" x-text="p.name + ' (' + formatRupiah(p.selling_price) + (p.validity ? ' / Masa Aktif: ' + p.validity : '') + ')'"></option>
                            </template>
                        </select>
                        <span class="text-[10px] text-zinc-500 mt-1 block">Masa berlaku paket dihitung sejak pertama login</span>
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                            8. Time Limit (Batas Akumulasi Jam Pemakaian)
                        </label>
                        <input type="text" x-model="form.timeLimit" placeholder="Default dari profile (e.g. 24h, 3h)" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs">
                    </div>
                </div>

                <!-- Info Box: Validity vs Time Limit -->
                <div class="p-3.5 rounded-xl bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200/80 dark:border-blue-900/50 text-blue-900 dark:text-blue-200 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-[11px] leading-relaxed">
                        <strong class="font-semibold">Perbedaan Validity (Profile) vs Time Limit (Pemakaian):</strong>
                        <ul class="list-disc list-inside mt-0.5 space-y-0.5 text-blue-800/90 dark:text-blue-300/90">
                            <li><strong>Validity (Masa Berlaku di Profile):</strong> Masa aktif voucher sejak pertama login (misal: <em>3 Hari</em>). Jika melewati 3 hari, voucher kedaluwarsa.</li>
                            <li><strong>Time Limit (Batas Durasi Online):</strong> Akumulasi jam pemakaian internet (misal: <em>24h</em>). Jika 24 jam pemakaian habis sebelum 3 hari, voucher langsung habis/hangus.</li>
                        </ul>
                    </div>
                </div>

                <!-- Row 5: Data Limit & Comment -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                            9. Data Limit (Batas Kuota Bytes)
                        </label>
                        <input type="text" x-model="form.dataLimit" placeholder="Kosongkan jika Unlimited (e.g. 500M, 2G)" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                            10. Comment / Nama Batch (Opsional)
                        </label>
                        <input type="text" x-model="form.comment" placeholder="Otomatis: Batch YYYY-MM-DD HH:mm Paket" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs">
                    </div>
                </div>

                <!-- Live Preview Voucher Mockup -->
                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700/80 space-y-2">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Live Preview Format Voucher:</span>
                    <div class="flex flex-wrap items-center gap-4 text-xs">
                        <div>
                            <span class="text-zinc-500">Kode Contoh:</span>
                            <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100 ml-1" x-text="sampleCode"></span>
                        </div>
                        <span class="text-zinc-300 dark:text-zinc-700">•</span>
                        <div>
                            <span class="text-zinc-500">Server:</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200" x-text="form.server"></span>
                        </div>
                        <span class="text-zinc-300 dark:text-zinc-700">•</span>
                        <div>
                            <span class="text-zinc-500">Profile:</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200" x-text="form.profile"></span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-mono font-semibold ml-1" x-text="'(' + selectedProfilePrice + ')'"></span>
                        </div>
                    </div>
                </div>

                <!-- Submit and Print Action -->
                <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <a href="{{ route('vouchers.index') }}" class="w-full sm:w-auto px-4 py-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-medium text-center hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                        Buka Daftar Voucher
                    </a>
                    <button type="submit" :disabled="isGenerating" class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 font-semibold shadow-xs transition-colors flex items-center justify-center gap-2">
                        <svg x-show="isGenerating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="isGenerating ? 'Sedang Generate...' : 'Simpan & Generate ' + form.qty + ' Voucher'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function voucherGenerator() {
            return {
                isGenerating: false,
                profiles: @json($profiles ?? []),
                servers: @json($servers ?? []),
                form: {
                    qty: 5,
                    server: 'all',
                    credentialMode: 'same',
                    length: 5,
                    prefix: '',
                    charSet: 'numeric',
                    profile: '{{ $profiles->first()?->name ?? "" }}',
                    timeLimit: '24h',
                    dataLimit: '',
                    comment: ''
                },
                init() {
                    if (!this.form.profile && this.profiles.length > 0) {
                        this.form.profile = this.profiles[0].name;
                        this.onProfileChange();
                    }
                },
                onProfileChange() {
                    const p = this.profiles.find(x => x.name === this.form.profile);
                },
                get selectedProfilePrice() {
                    const p = this.profiles.find(x => x.name === this.form.profile);
                    return p ? formatRupiah(p.selling_price) : '';
                },
                get sampleCode() {
                    const pref = this.form.prefix || '';
                    if (this.form.charSet === 'numeric') return pref + '84920';
                    if (this.form.charSet === 'uppercase') return pref + 'KWXPZ';
                    if (this.form.charSet === 'lowercase') return pref + 'mxtya';
                    return pref + '7xK9A';
                },
                generateVouchers() {
                    if (this.isGenerating) return;
                    this.isGenerating = true;

                    fetch('{{ route("hotspot.generate.batch") }}', {
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
                        this.isGenerating = false;
                        if (data.success) {
                            if (window.dispatchEvent) {
                                window.dispatchEvent(new CustomEvent('toast-notify', { detail: { message: `${data.count} Voucher berhasil digenerate ke inventory!`, type: 'success' } }));
                            }
                            setTimeout(() => {
                                window.location.href = "{{ route('vouchers.index') }}?batch=" + encodeURIComponent(data.batch || '');
                            }, 800);
                        } else {
                            alert(data.message || 'Gagal generate voucher');
                        }
                    })
                    .catch(err => {
                        this.isGenerating = false;
                        alert('Terjadi kesalahan saat generate voucher');
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
