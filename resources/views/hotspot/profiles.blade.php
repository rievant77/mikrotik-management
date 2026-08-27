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
                            <x-badge type="active">Aktif</x-badge>
                        </div>
                        <div class="mt-3 space-y-2 text-xs text-zinc-600 dark:text-zinc-400">
                            <div class="flex justify-between py-1 border-b border-zinc-100 dark:border-zinc-800/80">
                                <span>Rate Limit (Rx/Tx)</span>
                                <strong class="text-zinc-900 dark:text-zinc-100 font-mono" x-text="p.rate_limit || '-'"></strong>
                            </div>
                            <div class="flex justify-between py-1 border-b border-zinc-100 dark:border-zinc-800/80">
                                <span>Shared Users</span>
                                <span class="font-medium text-zinc-800 dark:text-zinc-200" x-text="(p.shared_users || 1) + ' Device'"></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-zinc-100 dark:border-zinc-800/80">
                                <span>Masa Berlaku (Validity)</span>
                                <span class="font-medium text-zinc-800 dark:text-zinc-200" x-text="p.validity || '-'"></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-zinc-100 dark:border-zinc-800/80">
                                <span>Harga Pokok</span>
                                <span class="font-mono text-zinc-500" x-text="formatRupiah(p.cost_price || 0)"></span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span>Harga Jual</span>
                                <strong class="text-emerald-600 dark:text-emerald-400 font-mono text-sm" x-text="formatRupiah(p.selling_price || 0)"></strong>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between text-xs">
                        <span class="text-[11px] text-zinc-400" x-text="'Expired: ' + (p.expired_mode || 'Remove')"></span>
                        <div class="flex items-center gap-2">
                            <button @click="editProfile(p)" class="px-2.5 py-1 rounded-md text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 font-medium transition-colors">
                                Edit
                            </button>
                            <button @click="deleteProfile(p)" class="px-2.5 py-1 rounded-md text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 font-medium transition-colors">
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
                    <input type="text" x-model="form.name" required class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100" placeholder="e.g. Paket-5Jam">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Rate Limit (Rx/Tx) *</label>
                        <input type="text" x-model="form.rate_limit" required class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100" placeholder="e.g. 5M/5M">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Shared Users (Device)</label>
                        <input type="number" x-model="form.shared_users" min="1" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Masa Berlaku (Validity)</label>
                        <input type="text" x-model="form.validity" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100" placeholder="e.g. 3 Jam, 24 Jam, 30 Hari">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Harga Jual (Rp) *</label>
                        <input type="number" x-model="form.selling_price" required class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100" placeholder="5000">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Harga Pokok (Rp)</label>
                        <input type="number" x-model="form.cost_price" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100" placeholder="2500">
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
                form: { id: null, name: '', rate_limit: '5M/5M', shared_users: 1, validity: '3 Jam', cost_price: 1500, selling_price: 3000, expired_mode: 'Remove' },
                openCreateModal() {
                    this.isEditing = false;
                    this.form = { id: null, name: '', rate_limit: '5M/5M', shared_users: 1, validity: '3 Jam', cost_price: 1500, selling_price: 3000, expired_mode: 'Remove' };
                    this.$dispatch('open-modal', 'profile-form-modal');
                },
                editProfile(p) {
                    this.isEditing = true;
                    this.form = { ...p };
                    this.$dispatch('open-modal', 'profile-form-modal');
                },
                saveProfile() {
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
                                const idx = this.profiles.findIndex(x => x.id === this.form.id);
                                if (idx !== -1) this.profiles[idx] = data.profile;
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
