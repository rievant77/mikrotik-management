<x-layouts.app>
    <x-slot:header>Template Cetak & WhatsApp</x-slot:header>

    <div class="max-w-6xl mx-auto space-y-6" x-data="templateManager()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Kustomisasi Template Voucher & WhatsApp</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Atur detail format cetak (Thermal & Grid A4) serta pesan otomatis WhatsApp voucher hotspot
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vouchers.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    <span>Daftar Voucher</span>
                </a>
            </div>
        </div>

        <!-- Main Form Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Column: Form Settings (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                <form @submit.prevent="saveTemplate()" class="space-y-6 text-xs">
                    
                    <!-- Section 1: Identitas & Header Struk -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs space-y-4">
                        <div class="flex items-center gap-2 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                            <span class="w-6 h-6 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center font-bold text-xs text-zinc-700 dark:text-zinc-300">1</span>
                            <div>
                                <h3 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Identitas & Header Voucher</h3>
                                <p class="text-[11px] text-zinc-400">Header yang tercetak di bagian atas kertas struk atau lembar voucher</p>
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                Nama Hotspot / Brand Usaha *
                            </label>
                            <input 
                                type="text" 
                                x-model="form.hotspot_name" 
                                required 
                                class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs focus:ring-2 focus:ring-zinc-400" 
                                placeholder="e.g. WARKOP DIGITAL HOTSPOT"
                            >
                            <span class="text-[10px] text-zinc-400 mt-1 block">Nama utama hotspot yang dicetak tebal di baris paling atas</span>
                        </div>

                        <div>
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                Tagline / Subtitle Header
                            </label>
                            <input 
                                type="text" 
                                x-model="form.header_subtext" 
                                class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs focus:ring-2 focus:ring-zinc-400" 
                                placeholder="e.g. Internet Cepat, Stabil & Hemat"
                            >
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                    URL / Domain Login Hotspot
                                </label>
                                <input 
                                    type="text" 
                                    x-model="form.login_url" 
                                    class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs font-mono focus:ring-2 focus:ring-zinc-400" 
                                    placeholder="http://hotspot.lan"
                                >
                                <span class="text-[10px] text-zinc-400 mt-1 block">Tujuan link pada QR Code dan browser</span>
                            </div>

                            <div>
                                <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                    Kontak CS / WhatsApp Hotline
                                </label>
                                <input 
                                    type="text" 
                                    x-model="form.contact_phone" 
                                    class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs font-mono focus:ring-2 focus:ring-zinc-400" 
                                    placeholder="e.g. 081234567890"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Footer & Ketentuan Penggunaan -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs space-y-4">
                        <div class="flex items-center gap-2 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                            <span class="w-6 h-6 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center font-bold text-xs text-zinc-700 dark:text-zinc-300">2</span>
                            <div>
                                <h3 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Ketentuan & Footer Struk</h3>
                                <p class="text-[11px] text-zinc-400">Pesan penutup, disclaimer keamanan, dan opsi QR Code</p>
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                Ketentuan Singkat Pemakaian
                            </label>
                            <input 
                                type="text" 
                                x-model="form.terms_text" 
                                class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs focus:ring-2 focus:ring-zinc-400" 
                                placeholder="e.g. 1 Kode Login hanya untuk 1 Perangkat"
                            >
                        </div>

                        <div>
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                Pesan Footer Struk (Penutup)
                            </label>
                            <input 
                                type="text" 
                                x-model="form.footer_text" 
                                class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-xs focus:ring-2 focus:ring-zinc-400" 
                                placeholder="e.g. Terima kasih atas kunjungan Anda - Selamat Berinternet!"
                            >
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <input 
                                type="checkbox" 
                                id="showQrCheck" 
                                x-model="form.show_qr" 
                                class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500"
                            >
                            <label for="showQrCheck" class="font-medium text-zinc-700 dark:text-zinc-300 cursor-pointer">
                                Tampilkan QR Code login otomatis pada cetakan
                            </label>
                        </div>
                    </div>

                    <!-- Section 3: Template Pesan WhatsApp -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold text-xs">3</span>
                                <div>
                                    <h3 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Template Chat WhatsApp</h3>
                                    <p class="text-[11px] text-zinc-400">Format teks yang dikirim ke pelanggan saat klik tombol WhatsApp</p>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="resetWaTemplate()" 
                                class="text-[11px] text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 underline cursor-pointer"
                            >
                                Reset ke Default
                            </button>
                        </div>

                        <!-- Variable Pills -->
                        <div>
                            <span class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 block mb-1.5">
                                Klik variabel di bawah untuk menyisipkan ke dalam pesan:
                            </span>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="v in availableVariables" :key="v.tag">
                                    <button 
                                        type="button" 
                                        @click="insertVariable(v.tag)" 
                                        class="px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 font-mono text-[10px] transition-colors cursor-pointer"
                                        :title="v.desc"
                                    >
                                        <span x-text="v.tag"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div>
                            <textarea 
                                id="waTemplateTextarea"
                                x-model="form.wa_template" 
                                rows="10" 
                                class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono text-[11px] leading-relaxed focus:ring-2 focus:ring-emerald-500"
                                placeholder="Tuliskan format template pesan WhatsApp..."
                            ></textarea>
                            <span class="text-[10px] text-zinc-400 mt-1 block">
                                Variabel <code class="text-zinc-600 dark:text-zinc-300 font-bold">{kredensial}</code> otomatis menampilkan "Kode Login: xxx" jika username & password sama.
                            </span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-2">
                        <button 
                            type="submit" 
                            :disabled="isSaving" 
                            class="px-6 py-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 font-semibold shadow-xs transition-colors flex items-center gap-2 cursor-pointer"
                        >
                            <svg x-show="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span x-text="isSaving ? 'Menyimpan Template...' : 'Simpan Semua Template'"></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Interactive Live Preview (5 cols) -->
            <div class="lg:col-span-5 sticky top-24 space-y-4">
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                    
                    <!-- Preview Tabs -->
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800 mb-4">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Live Preview</span>
                        <div class="flex items-center gap-1 p-0.5 bg-zinc-100 dark:bg-zinc-800 rounded-lg text-xs">
                            <button 
                                type="button" 
                                @click="previewTab = 'receipt'" 
                                :class="previewTab === 'receipt' ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 font-semibold shadow-xs' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200'"
                                class="px-2.5 py-1 rounded-md transition-colors cursor-pointer text-[11px]"
                            >
                                Struk Thermal
                            </button>
                            <button 
                                type="button" 
                                @click="previewTab = 'whatsapp'" 
                                :class="previewTab === 'whatsapp' ? 'bg-white dark:bg-zinc-700 text-emerald-600 dark:text-emerald-400 font-semibold shadow-xs' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200'"
                                class="px-2.5 py-1 rounded-md transition-colors cursor-pointer text-[11px]"
                            >
                                Chat WhatsApp
                            </button>
                        </div>
                    </div>

                    <!-- Tab 1: Live Struk Thermal Preview -->
                    <div x-show="previewTab === 'receipt'" class="space-y-3">
                        <div class="w-[60mm] mx-auto bg-white text-zinc-900 p-4 border border-zinc-300 rounded-lg font-mono text-[11px] leading-tight shadow-md text-center">
                            <div class="font-bold text-sm" x-text="form.hotspot_name || 'MIKROTIK HOTSPOT'"></div>
                            <div class="text-[9px] text-zinc-500 mt-0.5" x-text="form.header_subtext || 'Internet Cepat & Stabil'"></div>
                            <div class="my-2 border-t border-dashed border-zinc-400"></div>

                            <div class="text-[10px] flex justify-between">
                                <span>Paket:</span>
                                <strong>Voucher-24Jam</strong>
                            </div>
                            <div class="text-[10px] flex justify-between">
                                <span>Masa Aktif:</span>
                                <strong>24 Jam</strong>
                            </div>
                            <div class="text-[10px] flex justify-between">
                                <span>Harga:</span>
                                <strong>Rp 5.000</strong>
                            </div>

                            <!-- Credential Box -->
                            <div class="my-2 p-1.5 bg-zinc-100 border border-zinc-300 rounded text-center">
                                <div class="text-[9px] text-zinc-500 uppercase">KODE LOGIN</div>
                                <div class="text-sm font-extrabold tracking-wider my-0.5">VC-884920</div>
                            </div>

                            <!-- QR Code Preview Placeholder -->
                            <template x-if="form.show_qr">
                                <div class="my-2 p-2 bg-zinc-50 border border-zinc-200 rounded flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-zinc-900 flex items-center justify-center text-white rounded text-[9px] font-sans">
                                        [QR Code]
                                    </div>
                                    <span class="text-[8px] text-zinc-500 mt-1 font-sans">Scan QR untuk Login</span>
                                </div>
                            </template>

                            <div class="text-[9px] text-zinc-500 mt-1.5">
                                Login di: <span class="font-bold" x-text="form.login_url || 'http://hotspot.lan'"></span>
                            </div>

                            <template x-if="form.contact_phone">
                                <div class="text-[9px] text-zinc-500 mt-0.5">
                                    CS WA: <span x-text="form.contact_phone"></span>
                                </div>
                            </template>

                            <template x-if="form.terms_text">
                                <div class="text-[8px] text-zinc-400 mt-1 border-t border-dashed border-zinc-300 pt-1" x-text="form.terms_text"></div>
                            </template>

                            <div class="text-[9px] text-zinc-600 mt-1 border-t border-zinc-200 pt-1 font-medium" x-text="form.footer_text || 'Terima kasih atas kunjungan Anda'"></div>
                        </div>
                    </div>

                    <!-- Tab 2: Live WhatsApp Chat Preview -->
                    <div x-show="previewTab === 'whatsapp'" class="space-y-3">
                        <div class="bg-emerald-900/10 dark:bg-emerald-950/30 p-3 rounded-xl border border-emerald-500/20">
                            <!-- WhatsApp Simulated Chat Bubble -->
                            <div class="bg-[#e7fedb] dark:bg-[#005c4b] text-zinc-900 dark:text-zinc-100 p-3.5 rounded-2xl rounded-tr-none shadow-xs text-xs font-sans leading-relaxed whitespace-pre-wrap max-w-sm ml-auto border border-emerald-300/40 dark:border-emerald-600/40" x-text="computedWaPreview"></div>
                            <div class="text-right text-[10px] text-zinc-400 mt-1 mr-1">10:45 ✓✓</div>
                        </div>

                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 leading-normal p-2 bg-zinc-50 dark:bg-zinc-800/60 rounded-lg">
                            💡 Format di atas adalah simulasi tampilan pesan saat dikirimkan ke WhatsApp pelanggan.
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function templateManager() {
            return {
                isSaving: false,
                previewTab: 'receipt',
                form: {
                    hotspot_name: '{{ $setting->hotspot_name ?? "MIKROTIK HOTSPOT" }}',
                    header_subtext: '{{ $setting->header_subtext ?? "Internet Cepat & Stabil" }}',
                    login_url: '{{ $setting->login_url ?? "http://hotspot.lan" }}',
                    contact_phone: '{{ $setting->contact_phone ?? "" }}',
                    footer_text: '{{ $setting->footer_text ?? "Terima kasih atas kunjungan Anda" }}',
                    terms_text: '{{ $setting->terms_text ?? "1 Voucher hanya untuk 1 Perangkat" }}',
                    show_qr: {{ ($setting->show_qr ?? true) ? 'true' : 'false' }},
                    wa_template: @json($setting->wa_template ?? '')
                },
                availableVariables: [
                    { tag: '{brand}', desc: 'Nama Hotspot' },
                    { tag: '{paket}', desc: 'Nama Paket Voucher' },
                    { tag: '{durasi}', desc: 'Masa Aktif / Limit' },
                    { tag: '{harga}', desc: 'Harga Jual' },
                    { tag: '{kredensial}', desc: 'Kode Login / User & Pass' },
                    { tag: '{username}', desc: 'Username' },
                    { tag: '{password}', desc: 'Password' },
                    { tag: '{login_url}', desc: 'URL Portal Login' },
                    { tag: '{kontak}', desc: 'Nomor CS' },
                    { tag: '{footer}', desc: 'Pesan Footer' },
                ],
                init() {
                    if (!this.form.wa_template) {
                        this.form.wa_template = this.getDefaultWaTemplate();
                    }
                },
                getDefaultWaTemplate() {
                    return `🌐 *VOUCHER INTERNET - {brand}*\n` +
                           `━━━━━━━━━━━━━━━━━━━━\n` +
                           `🏷️ *Paket:* {paket}\n` +
                           `⏱️ *Masa Aktif:* {durasi}\n` +
                           `💰 *Harga:* {harga}\n\n` +
                           `🔑 *KREDENSIAL LOGIN*\n` +
                           `{kredensial}\n\n` +
                           `📱 *CARA PENGGUNAAN*\n` +
                           `1. Hubungkan WiFi ke *{brand}*\n` +
                           `2. Buka browser: {login_url}\n` +
                           `3. Masukkan kode login di atas\n` +
                           `4. Klik tombol Login / Hubungkan\n\n` +
                           `_Terima kasih telah menggunakan layanan kami!_`;
                },
                resetWaTemplate() {
                    if (confirm('Kembalikan template pesan WhatsApp ke format default rekomendasi?')) {
                        this.form.wa_template = this.getDefaultWaTemplate();
                    }
                },
                insertVariable(tag) {
                    const textarea = document.getElementById('waTemplateTextarea');
                    if (!textarea) {
                        this.form.wa_template += ' ' + tag;
                        return;
                    }
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const text = this.form.wa_template;
                    this.form.wa_template = text.substring(0, start) + tag + text.substring(end);
                    this.$nextTick(() => {
                        textarea.focus();
                        textarea.setSelectionRange(start + tag.length, start + tag.length);
                    });
                },
                get computedWaPreview() {
                    const brand = (this.form.hotspot_name || 'MIKROTIK HOTSPOT').toUpperCase();
                    const paket = 'Voucher 24 Jam';
                    const durasi = '24 Jam';
                    const harga = 'Rp 5.000';
                    const loginUrl = this.form.login_url || 'http://hotspot.lan';
                    const kontak = this.form.contact_phone || '081234567890';
                    const footer = this.form.footer_text || 'Terima kasih atas kunjungan Anda';

                    // Contoh kredensial sama (username === password)
                    const kredensial = '• *Kode Login:* `VC-884920`';
                    const username = 'VC-884920';
                    const password = 'VC-884920';

                    let tpl = this.form.wa_template || this.getDefaultWaTemplate();
                    return tpl
                        .replace(/{brand}/g, brand)
                        .replace(/{paket}/g, paket)
                        .replace(/{durasi}/g, durasi)
                        .replace(/{harga}/g, harga)
                        .replace(/{kredensial}/g, kredensial)
                        .replace(/{username}/g, username)
                        .replace(/{password}/g, password)
                        .replace(/{login_url}/g, loginUrl)
                        .replace(/{kontak}/g, kontak)
                        .replace(/{footer}/g, footer);
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
                            window.showToast('Semua template cetak & WhatsApp berhasil disimpan!', 'success');
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
