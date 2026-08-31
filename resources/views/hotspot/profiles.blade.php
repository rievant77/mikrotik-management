<x-layouts.app>
    <x-slot:header>Hotspot Profiles</x-slot:header>

    <div class="space-y-6" x-data="profilesManager()">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Hotspot Profiles & Paket</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Konfigurasi bandwidth limit, masa berlaku, dan harga jual voucher</p>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    @click="syncProfiles()" 
                    :disabled="isSyncing"
                    class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs"
                >
                    <svg class="w-4 h-4 text-zinc-500" :class="{ 'animate-spin': isSyncing }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="isSyncing ? 'Sinkronisasi...' : 'Sinkron dari Router'"></span>
                </button>
                <button @click="openCreateModal()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>+ Tambah Profile</span>
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <template x-if="profiles.length === 0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Belum Ada Hotspot Profile</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Tambahkan profile baru atau klik tombol sinkronisasi untuk menarik data profile dari MikroTik.</p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <button @click="syncProfiles()" class="px-3.5 py-2 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300">
                        Sinkron dari Router
                    </button>
                    <button @click="openCreateModal()" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900">
                        + Buat Profile Pertama
                    </button>
                </div>
            </div>
        </template>

        <!-- Profiles Grid Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" x-show="profiles.length > 0">
            <template x-for="p in profiles" :key="p.id">
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col justify-between hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-base text-zinc-900 dark:text-zinc-100" x-text="p.name"></h3>
                            <button @click="toggleStatus(p)" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold transition-colors cursor-pointer"
                                :class="p.is_active !== false && p.is_active !== 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-zinc-500/10 text-zinc-500 border border-zinc-500/20 hover:bg-zinc-500/20'"
                                :title="p.is_active !== false && p.is_active !== 0 ? 'Klik untuk non-aktifkan' : 'Klik untuk aktifkan'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="p.is_active !== false && p.is_active !== 0 ? 'bg-emerald-500' : 'bg-zinc-400'"></span>
                                <span x-text="p.is_active !== false && p.is_active !== 0 ? 'Aktif' : 'Non-aktif'"></span>
                            </button>
                        </div>
                        <div class="mt-3 space-y-2 text-xs text-zinc-600 dark:text-zinc-400">
                            <div class="flex justify-between py-1 border-b border-zinc-100 dark:border-zinc-800/80 items-center">
                                <span>Rate Limit (Rx/Tx)</span>
                                <template x-if="!p.rate_limit || p.rate_limit.trim() === ''">
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-500/20 px-1.5 py-0.5 rounded text-[10px]">Unlimited (Tanpa Batas)</span>
                                </template>
                                <template x-if="p.rate_limit && p.rate_limit.trim() !== ''">
                                    <strong class="text-zinc-900 dark:text-zinc-100 font-mono" x-text="p.rate_limit"></strong>
                                </template>
                            </div>
                            <div class="flex justify-between py-1 border-b border-zinc-100 dark:border-zinc-800/80 items-center">
                                <span>Shared Users</span>
                                <template x-if="!p.shared_users || p.shared_users == 0">
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-500/20 px-1.5 py-0.5 rounded text-[10px]">Unlimited (Banyak Device)</span>
                                </template>
                                <template x-if="p.shared_users && p.shared_users > 0">
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200" x-text="p.shared_users + ' Device'"></span>
                                </template>
                            </div>
                            <div class="flex justify-between py-1 border-b border-zinc-100 dark:border-zinc-800/80 items-center">
                                <span>Masa Berlaku (Validity)</span>
                                <template x-if="!p.validity || p.validity.toLowerCase() === 'unlimited' || p.validity.toLowerCase() === 'tanpa batas' || p.validity === '0'">
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-500/20 px-1.5 py-0.5 rounded text-[10px]">Unlimited (Tanpa Batas)</span>
                                </template>
                                <template x-if="p.validity && p.validity.toLowerCase() !== 'unlimited' && p.validity.toLowerCase() !== 'tanpa batas' && p.validity !== '0'">
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200 font-mono" x-text="p.validity"></span>
                                </template>
                            </div>
                            <div class="flex justify-between py-1 border-b border-zinc-100 dark:border-zinc-800/80">
                                <span>Harga Pokok</span>
                                <span class="font-mono text-zinc-500" x-text="formatRupiah(p.cost_price || 0)"></span>
                            </div>
                            <div class="flex justify-between py-1 items-center">
                                <span>Harga Jual</span>
                                <template x-if="!p.selling_price || parseFloat(p.selling_price) === 0">
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-500/20 px-1.5 py-0.5 rounded text-[10px]">Gratis / Fixed (Rp 0)</span>
                                </template>
                                <template x-if="p.selling_price && parseFloat(p.selling_price) > 0">
                                    <strong class="text-emerald-600 dark:text-emerald-400 font-mono text-sm" x-text="formatRupiah(p.selling_price)"></strong>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between text-xs">
                        <div class="flex flex-col gap-1">
                            <span class="text-[11px] text-zinc-400" x-text="'Expired: ' + (p.expired_mode || 'Remove')"></span>
                            <template x-if="p.fup_enabled">
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-1.5 py-0.5 rounded border border-amber-500/20">
                                    🐢 FUP: <span x-text="(p.fup_limit_display || '10GB') + ' ➔ ' + (p.fup_rate_limit || '1M/1M')"></span>
                                </span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="editProfile(p)" class="px-2.5 py-1 rounded-md text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 font-medium transition-colors cursor-pointer">
                                Edit
                            </button>
                            <button @click="deleteProfile(p)" class="px-2.5 py-1 rounded-md text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 font-medium transition-colors cursor-pointer">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Create/Edit Profile Modal -->
        <x-modal name="profile-form-modal" title="Konfigurasi Hotspot Profile">
            <form @submit.prevent="saveProfile()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nama Profile *</label>
                    <input type="text" x-model="form.name" required class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100" placeholder="e.g. Paket-5Jam / Profile-VIP">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300">Rate Limit (Rx/Tx)</label>
                            <button type="button" @click="form.rate_limit = ''" class="text-[10px] text-emerald-600 dark:text-emerald-400 hover:underline">Set Unlimited</button>
                        </div>
                        <input type="text" x-model="form.rate_limit" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono" placeholder="Kosongkan jika Unlimited (e.g. 5M/5M)">
                        <span class="text-[10px] text-zinc-500 mt-1 block">Biarkan kosong jika tidak ada batasan kecepatan.</span>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300">Shared Users (Batas Device)</label>
                            <button type="button" @click="form.shared_users = 0" class="text-[10px] text-emerald-600 dark:text-emerald-400 hover:underline">Set Unlimited</button>
                        </div>
                        <input type="number" x-model="form.shared_users" min="0" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100" placeholder="1 (0 = Unlimited Device)">
                        <span class="text-[10px] text-zinc-500 mt-1 block">Isi <strong>0</strong> atau kosongkan jika voucher dapat dipakai banyak device bersamaan.</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300">Masa Berlaku (Validity)</label>
                            <button type="button" @click="form.validity = 'Unlimited'" class="text-[10px] text-emerald-600 dark:text-emerald-400 hover:underline">Set Unlimited</button>
                        </div>
                        <input type="text" x-model="form.validity" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono" placeholder="e.g. 3 Hari, 30 Hari, Unlimited">
                        <div class="flex flex-wrap gap-1 mt-1.5">
                            <button type="button" @click="form.validity = '24 Jam'" class="px-1.5 py-0.5 rounded text-[10px] bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">24 Jam</button>
                            <button type="button" @click="form.validity = '3 Hari'" class="px-1.5 py-0.5 rounded text-[10px] bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">3 Hari</button>
                            <button type="button" @click="form.validity = '7 Hari'" class="px-1.5 py-0.5 rounded text-[10px] bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">7 Hari</button>
                            <button type="button" @click="form.validity = '30 Hari'" class="px-1.5 py-0.5 rounded text-[10px] bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">30 Hari</button>
                            <button type="button" @click="form.validity = 'Unlimited'" class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 font-semibold">Unlimited</button>
                        </div>
                        <span class="text-[10px] text-zinc-500 mt-1 block">Ketik <strong>Unlimited</strong> atau kosongkan jika berlaku selamanya.</span>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300">Harga Jual</label>
                            <button type="button" @click="form.selling_price = 0" class="text-[10px] text-emerald-600 dark:text-emerald-400 hover:underline">Set Gratis (0)</button>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-semibold text-zinc-400 select-none">Rp</span>
                            <input 
                                type="text" 
                                inputmode="numeric"
                                :value="formatNumber(form.selling_price)" 
                                @input="form.selling_price = parseNumber($event.target.value); $event.target.value = formatNumber(form.selling_price)" 
                                class="w-full pl-9 pr-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono font-bold" 
                                placeholder="0 (Gratis)"
                            >
                        </div>
                        <span class="text-[10px] text-zinc-500 mt-1 block">Isi 0 atau kosongkan jika profile gratis / fixed.</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Harga Pokok</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-semibold text-zinc-400 select-none">Rp</span>
                            <input 
                                type="text" 
                                inputmode="numeric"
                                :value="formatNumber(form.cost_price)" 
                                @input="form.cost_price = parseNumber($event.target.value); $event.target.value = formatNumber(form.cost_price)" 
                                class="w-full pl-9 pr-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono" 
                                placeholder="0"
                            >
                        </div>
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Expired Mode</label>
                        <select x-model="form.expired_mode" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                            <option value="Remove">Remove (Hapus Akun)</option>
                            <option value="Notice">Notice (Peringatan Saja)</option>
                            <option value="Remove & Record">Remove & Record</option>
                        </select>
                    </div>
                </div>

                <!-- FUP (Fair Usage Policy) Section -->
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">Fair Usage Policy (FUP)</span>
                            <p class="text-[10px] text-zinc-500">Turunkan kecepatan secara otomatis saat pemakaian data melebihi kuota wajar.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="form.fup_enabled" class="sr-only peer">
                            <div class="w-9 h-5 bg-zinc-200 peer-focus:outline-none rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-zinc-600 peer-checked:bg-amber-500"></div>
                        </label>
                    </div>

                    <div x-show="form.fup_enabled" class="space-y-3 pt-2 bg-amber-50/50 dark:bg-amber-950/20 p-3 rounded-lg border border-amber-200/50 dark:border-amber-900/30">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Batas Kuota FUP</label>
                                <input type="text" x-model="form.fup_limit_display" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono" placeholder="e.g. 10 GB">
                                <span class="text-[10px] text-zinc-400 mt-0.5 block">Format: 5 GB, 10 GB, 500 MB</span>
                            </div>
                            <div>
                                <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Kecepatan Setelah FUP</label>
                                <input type="text" x-model="form.fup_rate_limit" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono" placeholder="e.g. 1M/1M">
                                <span class="text-[10px] text-zinc-400 mt-0.5 block">Kecepatan turun (Tx/Rx)</span>
                            </div>
                        </div>
                        <div>
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Siklus Reset FUP</label>
                            <select x-model="form.fup_reset_cycle" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                                <option value="daily">Harian (Reset setiap Jam 00:00)</option>
                                <option value="monthly">Bulanan (Reset setiap awal bulan)</option>
                                <option value="unlimited">Per Siklus Voucher (Tidak reset otomatis)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="$dispatch('close-modal', 'profile-form-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-semibold" x-text="isEditing ? 'Perbarui Profile' : 'Simpan Profile'"></button>
                </div>
            </form>
        </x-modal>
    </div>

    @push('scripts')
    <script>
        function profilesManager() {
            return {
                isEditing: false,
                isSyncing: false,
                profiles: @json($profiles ?? []),
                form: { id: null, name: '', rate_limit: '', shared_users: 1, validity: '3 Jam', cost_price: 0, selling_price: 0, expired_mode: 'Remove', fup_enabled: false, fup_limit_display: '10 GB', fup_rate_limit: '1M/1M', fup_reset_cycle: 'daily' },
                openCreateModal() {
                    this.isEditing = false;
                    this.form = { id: null, name: '', rate_limit: '', shared_users: 1, validity: '3 Jam', cost_price: 0, selling_price: 0, expired_mode: 'Remove', fup_enabled: false, fup_limit_display: '10 GB', fup_rate_limit: '1M/1M', fup_reset_cycle: 'daily' };
                    this.$dispatch('open-modal', 'profile-form-modal');
                },
                editProfile(p) {
                    this.isEditing = true;
                    this.form = { 
                        id: p.id,
                        name: p.name,
                        rate_limit: p.rate_limit || '',
                        shared_users: p.shared_users ?? 1,
                        validity: p.validity || 'Unlimited',
                        cost_price: p.cost_price ?? 0,
                        selling_price: p.selling_price ?? 0,
                        expired_mode: p.expired_mode || 'Remove',
                        fup_enabled: !!p.fup_enabled,
                        fup_limit_display: p.fup_limit_display || '10 GB',
                        fup_rate_limit: p.fup_rate_limit || '1M/1M',
                        fup_reset_cycle: p.fup_reset_cycle || 'daily'
                    };
                    this.$dispatch('open-modal', 'profile-form-modal');
                },
                saveProfile() {
                    this.form.selling_price = window.parseNumber(this.form.selling_price);
                    this.form.cost_price = window.parseNumber(this.form.cost_price);
                    this.form.shared_users = this.form.shared_users !== '' ? parseInt(this.form.shared_users, 10) : 0;

                    const url = this.isEditing ? `/hotspot/profiles/${this.form.id}` : '/hotspot/profiles';
                    const method = this.isEditing ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (this.isEditing) {
                                this.profiles = this.profiles.map(x => x.id === this.form.id ? data.profile : x);
                                window.showToast(`Profile ${this.form.name} berhasil diperbarui!`, 'success');
                            } else {
                                this.profiles.push(data.profile);
                                window.showToast(`Profile ${data.profile.name} berhasil ditambahkan!`, 'success');
                            }
                            this.$dispatch('close-modal', 'profile-form-modal');
                        } else {
                            window.showToast(data.message || 'Gagal menyimpan profile', 'error');
                        }
                    })
                    .catch(err => {
                        window.showToast('Terjadi kesalahan jaringan', 'error');
                    });
                },
                deleteProfile(p) {
                    if (confirm(`Apakah Anda yakin ingin menghapus profile "${p.name}"?`)) {
                        fetch(`/hotspot/profiles/${p.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.profiles = this.profiles.filter(x => x.id !== p.id);
                                window.showToast(`Profile ${p.name} berhasil dihapus`, 'info');
                            } else {
                                window.showToast(data.message || 'Gagal menghapus profile', 'error');
                            }
                        })
                        .catch(err => {
                            window.showToast('Gagal menghapus profile', 'error');
                        });
                    }
                },
                toggleStatus(p) {
                    const nextStatus = !(p.is_active !== false && p.is_active !== 0);
                    p.is_active = nextStatus;

                    fetch(`/hotspot/profiles/${p.id}/toggle`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            p.is_active = data.is_active;
                            window.showToast(data.message, 'success');
                        } else {
                            p.is_active = !nextStatus;
                            window.showToast(data.message || 'Gagal mengubah status', 'error');
                        }
                    })
                    .catch(() => {
                        p.is_active = !nextStatus;
                        window.showToast('Gagal terhubung ke server', 'error');
                    });
                },
                syncProfiles() {
                    this.isSyncing = true;
                    fetch('{{ route("hotspot.profiles.sync") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isSyncing = false;
                        if (data.success) {
                            this.profiles = data.profiles;
                            window.showToast(data.message, 'success');
                        } else {
                            window.showToast(data.message || 'Gagal sinkronisasi profile', 'error');
                        }
                    })
                    .catch(err => {
                        this.isSyncing = false;
                        window.showToast('Gagal terhubung ke router MikroTik', 'error');
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
