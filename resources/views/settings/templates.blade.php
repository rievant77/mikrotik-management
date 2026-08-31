<x-layouts.app>
    <x-slot:header>Template Voucher</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6" x-data="templateManager()">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Kustomisasi Template & Branding Cetak Voucher</h1>
            <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Atur nama toko/warkop, domain portal login, dan pesan footer untuk layout cetak A4 dan Thermal</p>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs">
            <form @submit.prevent="saveTemplate()" class="space-y-5 text-xs">
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nama Toko / Nama Hotspot Header *</label>
                    <input type="text" x-model="form.hotspot_name" required class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs focus:ring-2 focus:ring-zinc-400" placeholder="e.g. WARKOP COFFEE HOTSPOT">
                    <span class="text-[10px] text-zinc-500 mt-1 block">Nama brand/usaha yang tercetak di bagian atas lembar voucher</span>
                </div>
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Alamat Domain / IP Login Captive Portal</label>
                    <input type="text" x-model="form.login_url" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs font-mono focus:ring-2 focus:ring-zinc-400" placeholder="e.g. http://hotspot.lan atau 192.168.88.1">
                    <span class="text-[10px] text-zinc-500 mt-1 block">URL halaman login yang tertera pada lembar voucher atau QR Code</span>
                </div>
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Teks Pesan Footer Struk</label>
                    <input type="text" x-model="form.footer_text" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs focus:ring-2 focus:ring-zinc-400" placeholder="e.g. Terima kasih atas kunjungan Anda - Password tidak boleh dishare">
                </div>

                <!-- Preview Card -->
                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700/80">
                    <span class="text-zinc-400 text-[11px] uppercase font-semibold">Live Preview Tampilan Struk / Kartu:</span>
                    <div class="mt-2 text-center p-3 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800 font-mono max-w-sm mx-auto">
                        <div class="font-bold text-xs text-zinc-900 dark:text-zinc-100" x-text="form.hotspot_name || 'MIKROTIK HOTSPOT'"></div>
                        <div class="text-[9px] text-zinc-500 my-1">Kode: <strong>VC-89201</strong> | Paket-24Jam</div>
                        <div class="text-[9px] text-zinc-400">Login: <span x-text="form.login_url || 'http://hotspot.lan'"></span></div>
                        <div class="text-[9px] text-zinc-500 mt-1.5 border-t border-dashed border-zinc-200 dark:border-zinc-700 pt-1" x-text="form.footer_text || 'Terima kasih atas kunjungan Anda'"></div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="submit" :disabled="isSaving" class="px-6 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 font-semibold shadow-xs transition-colors flex items-center gap-2">
                        <svg x-show="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Template Voucher'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function templateManager() {
            return {
                isSaving: false,
                form: {
                    hotspot_name: '{{ $setting->hotspot_name ?? "MIKROTIK HOTSPOT" }}',
                    login_url: '{{ $setting->login_url ?? "http://hotspot.lan" }}',
                    footer_text: '{{ $setting->footer_text ?? "Terima kasih atas kunjungan Anda" }}'
                },
                saveTemplate() {
                    if (this.isSaving) return;
                    this.isSaving = true;

                    fetch('{{ route("settings.templates.update") }}', {
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
                        this.isSaving = false;
                        if (data.success) {
                            window.showToast('Template cetak voucher berhasil disimpan!', 'success');
                        } else {
                            window.showToast(data.message || 'Gagal menyimpan template', 'error');
                        }
                    })
                    .catch(() => {
                        this.isSaving = false;
                        window.showToast('Terjadi kesalahan saat menyimpan template', 'error');
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
