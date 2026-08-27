<x-layouts.app>
    <x-slot:header>Template Voucher</x-slot:header>

    <div class="max-w-4xl mx-auto space-y-6" x-data="templateManager()">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Kustomisasi Template Voucher</h1>
            <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Atur teks header, logo, instruksi login, dan footer struk cetak voucher</p>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6 shadow-xs">
            <form @submit.prevent="saveTemplate()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nama Hotspot / Brand Header *</label>
                    <input type="text" x-model="form.brandName" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                </div>
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Alamat Domain Login Captive Portal</label>
                    <input type="text" x-model="form.loginUrl" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono">
                </div>
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Teks Pesan Footer</label>
                    <input type="text" x-model="form.footerText" class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                </div>
                <div class="flex justify-end pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="submit" class="px-6 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 font-semibold shadow-xs">
                        Simpan Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function templateManager() {
            return {
                form: {
                    brandName: 'MIKROTIK HOTSPOT NETWORK',
                    loginUrl: 'http://hotspot.lan',
                    footerText: 'Terima kasih atas kunjungan Anda'
                },
                saveTemplate() {
                    window.showToast('Template cetak voucher berhasil disimpan', 'success');
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
