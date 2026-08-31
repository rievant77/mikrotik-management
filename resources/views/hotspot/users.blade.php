<x-layouts.app>
    <x-slot:header>Hotspot Users</x-slot:header>

    <div class="space-y-6" x-data="hotspotUsersManager()">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Kelola Akun User Hotspot</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Daftar user Hotspot MikroTik terdaftar beserta kredensial dan profile</p>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    @click="syncUsers()" 
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
                    <span>+ Tambah User</span>
                </button>
                <a href="{{ route('hotspot.generate') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    <span>Generate Massal</span>
                </a>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
            <div class="flex flex-col sm:flex-row gap-3">
                <input type="text" x-model="search" placeholder="Cari username..." class="flex-1 px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                <select x-model="selectedProfile" class="px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    <option value="">Semua Profile</option>
                    <template x-for="p in profiles" :key="p.id">
                        <option :value="p.name" x-text="p.name"></option>
                    </template>
                </select>
                <select x-model="statusFilter" @change="onStatusFilterChange()" class="px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    <option value="active">Aktif Saja (Default)</option>
                    <option value="all">Semua (Termasuk In-Active)</option>
                    <option value="inactive">⚪ In-Active / Non-Aktif</option>
                </select>
            </div>
        </div>

        <!-- Empty State -->
        <template x-if="filteredUsers.length === 0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Belum Ada User Hotspot</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Tidak ada user hotspot yang cocok dengan filter yang dipilih.</p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <button @click="statusFilter = 'all'; search = ''; selectedProfile = '';" class="px-3.5 py-2 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300">
                        Reset Filter
                    </button>
                    <button @click="openCreateModal()" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900">
                        + Tambah User
                    </button>
                </div>
            </div>
        </template>

        <!-- Hotspot Users Table -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs" x-show="filteredUsers.length > 0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3 px-4">Username</th>
                            <th class="py-3 px-4">Password</th>
                            <th class="py-3 px-4">Profile</th>
                            <th class="py-3 px-4">Uptime Limit / Terpakai</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <template x-for="user in filteredUsers" :key="user.id">
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40" :class="user.is_active === false || user.is_active === 0 || user.is_active === '0' ? 'opacity-70 bg-zinc-50/40 dark:bg-zinc-900/40' : ''">
                                <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100" x-text="user.username"></td>
                                <td class="py-3 px-4 font-mono text-zinc-500" x-text="user.password"></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700" x-text="user.profile?.name || user.profile || '-'"></span>
                                </td>
                                <td class="py-3 px-4 font-mono text-zinc-500" x-text="user.uptime_limit || user.uptime || '-'"></td>
                                <td class="py-3 px-4">
                                    <button @click="toggleStatus(user)" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold transition-colors cursor-pointer"
                                        :class="user.is_active !== false && user.is_active !== 0 && user.is_active !== '0' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-zinc-500/10 text-zinc-500 border border-zinc-500/20 hover:bg-zinc-500/20'"
                                        :title="user.is_active !== false && user.is_active !== 0 && user.is_active !== '0' ? 'Klik untuk non-aktifkan' : 'Klik untuk aktifkan'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active !== false && user.is_active !== 0 && user.is_active !== '0' ? 'bg-emerald-500' : 'bg-zinc-400'"></span>
                                        <span x-text="user.is_active !== false && user.is_active !== 0 && user.is_active !== '0' ? 'Aktif' : 'In-Active'"></span>
                                    </button>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button @click="editUser(user)" class="px-2 py-1 rounded text-xs font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer">
                                            Edit
                                        </button>
                                        <button @click="deleteUser(user)" class="px-2 py-1 rounded text-xs font-medium text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors cursor-pointer">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create/Edit Hotspot User Modal -->
        <x-modal name="hotspot-user-modal" title="Form User Hotspot">
            <form @submit.prevent="saveUser()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Username *</label>
                    <input type="text" x-model="userForm.username" required class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100" :disabled="isEditing">
                </div>
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Password *</label>
                    <input type="text" x-model="userForm.password" required class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Profile / Paket</label>
                        <select x-model="userForm.profile_id" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                            <option value="">Tanpa Profile (Default)</option>
                            <template x-for="p in profiles" :key="p.id">
                                <option :value="p.id" x-text="p.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Limit Waktu / Uptime</label>
                        <input type="text" x-model="userForm.uptime_limit" placeholder="e.g. 3h, 24h, 30d (kosongkan jika unlimited)" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono">
                    </div>
                </div>
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Catatan / Komentar</label>
                    <input type="text" x-model="userForm.comment" placeholder="e.g. Voucher retail" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="userIsActive" x-model="userForm.is_active" class="rounded text-zinc-900 focus:ring-zinc-500">
                    <label for="userIsActive" class="font-medium text-zinc-700 dark:text-zinc-300 cursor-pointer">User Aktif (Bisa Login Hotspot)</label>
                </div>
                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="$dispatch('close-modal', 'hotspot-user-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-semibold" x-text="isEditing ? 'Perbarui User' : 'Simpan User'"></button>
                </div>
            </form>
        </x-modal>
    </div>

    @push('scripts')
    <script>
        function hotspotUsersManager() {
            return {
                search: '',
                selectedProfile: '',
                statusFilter: '{{ $status ?? "active" }}',
                isEditing: false,
                isSyncing: false,
                profiles: @json($profiles ?? []),
                users: @json($users?->items() ?? $users ?? []),
                userForm: { id: null, username: '', password: '', profile_id: '', uptime_limit: '3h', comment: '', is_active: true },
                onStatusFilterChange() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('status', this.statusFilter);
                    window.location.href = url.toString();
                },
                get filteredUsers() {
                    return this.users.filter(u => {
                        const matchesSearch = !this.search || u.username.toLowerCase().includes(this.search.toLowerCase());
                        const pName = u.profile?.name || u.profile || '';
                        const matchesProfile = !this.selectedProfile || pName === this.selectedProfile;
                        const isActive = u.is_active !== false && u.is_active !== 0 && u.is_active !== '0';
                        let matchesStatus = true;
                        if (this.statusFilter === 'active') {
                            matchesStatus = isActive;
                        } else if (this.statusFilter === 'inactive') {
                            matchesStatus = !isActive;
                        }
                        return matchesSearch && matchesProfile && matchesStatus;
                    });
                },
                openCreateModal() {
                    this.isEditing = false;
                    this.userForm = { id: null, username: '', password: '', profile_id: (this.profiles[0]?.id || ''), uptime_limit: '3h', comment: '' };
                    this.$dispatch('open-modal', 'hotspot-user-modal');
                },
                editUser(u) {
                    this.isEditing = true;
                    this.userForm = { ...u, profile_id: u.profile_id || (u.profile?.id || '') };
                    this.$dispatch('open-modal', 'hotspot-user-modal');
                },
                saveUser() {
                    const url = this.isEditing ? `/hotspot/users/${this.userForm.id}` : '/hotspot/users';
                    const method = this.isEditing ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.userForm)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (this.isEditing) {
                                const idx = this.users.findIndex(x => x.id === this.userForm.id);
                                if (idx !== -1) this.users[idx] = data.user;
                                window.showToast(`User ${this.userForm.username} berhasil diperbarui!`, 'success');
                            } else {
                                this.users.unshift(data.user);
                                window.showToast(`User ${data.user.username} berhasil ditambahkan!`, 'success');
                            }
                            this.$dispatch('close-modal', 'hotspot-user-modal');
                        } else {
                            window.showToast(data.message || 'Gagal menyimpan user', 'error');
                        }
                    })
                    .catch(err => {
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },
                deleteUser(user) {
                    if (confirm(`Apakah Anda yakin ingin menghapus user "${user.username}" dari MikroTik?`)) {
                        fetch(`/hotspot/users/${user.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.users = this.users.filter(u => u.id !== user.id);
                                window.showToast(`User ${user.username} berhasil dihapus`, 'info');
                            } else {
                                window.showToast(data.message || 'Gagal menghapus user', 'error');
                            }
                        })
                        .catch(err => {
                            window.showToast('Gagal menghapus user', 'error');
                        });
                    }
                },
                toggleStatus(user) {
                    const nextStatus = !(user.is_active !== false && user.is_active !== 0);
                    user.is_active = nextStatus;

                    fetch(`/hotspot/users/${user.id}/toggle`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            user.is_active = data.is_active;
                            window.showToast(data.message, 'success');
                        } else {
                            user.is_active = !nextStatus;
                            window.showToast(data.message || 'Gagal mengubah status', 'error');
                        }
                    })
                    .catch(() => {
                        user.is_active = !nextStatus;
                        window.showToast('Gagal terhubung ke server', 'error');
                    });
                },
                syncUsers() {
                    this.isSyncing = true;
                    fetch('{{ route("hotspot.users.sync") }}', {
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
                            this.users = data.users;
                            window.showToast(data.message, 'success');
                        } else {
                            window.showToast(data.message || 'Gagal sinkronisasi user', 'error');
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
