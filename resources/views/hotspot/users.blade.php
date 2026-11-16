<x-layouts.app>
    <x-slot:header>Hotspot Users</x-slot:header>

    <div class="space-y-6" x-data="hotspotUsersManager()">
        <!-- Header Page -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Kelola Akun User Hotspot</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Daftar user Hotspot MikroTik terdaftar beserta kredensial dan profile</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openCreateModal()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Tambah User</span>
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
            <form method="GET" action="{{ route('hotspot.users') }}" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Search Input -->
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 mb-1">Cari Username</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}" 
                                placeholder="Cari nama username..." 
                                class="w-full pl-8 pr-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                            >
                            <svg class="w-4 h-4 text-zinc-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Kategori Filter -->
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 mb-1">Kategori</label>
                        <select 
                            name="category" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                        >
                            <option value="all" {{ request('category', 'all') === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                            <option value="voucher" {{ request('category') === 'voucher' ? 'selected' : '' }}>Voucher (Profit)</option>
                            <option value="bulanan" {{ request('category') === 'bulanan' ? 'selected' : '' }}>Bulanan (Profit)</option>
                            <option value="pribadi" {{ request('category') === 'pribadi' ? 'selected' : '' }}>Pribadi (Non-Profit)</option>
                        </select>
                    </div>

                    <!-- Profile / Paket Filter -->
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 mb-1">Paket / Profile</label>
                        <select 
                            name="profile_id" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                        >
                            <option value="">Semua Profile</option>
                            @foreach($profiles as $p)
                                @php
                                    $catLabel = match($p->category) {
                                        'bulanan' => 'Bulanan',
                                        'pribadi' => 'Pribadi',
                                        default => 'Voucher',
                                    };
                                @endphp
                                <option value="{{ $p->id }}" {{ request('profile_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} [{{ $catLabel }}]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 mb-1">Status User</label>
                        <select 
                            name="status" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                        >
                            <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Aktif Saja (Default)</option>
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Semua (Termasuk In-Active)</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>In-Active / Non-Aktif</option>
                        </select>
                    </div>
                </div>

                @if(request()->hasAny(['search', 'profile_id']) || (request('category') && request('category') !== 'all') || (request('status') && request('status') !== 'active'))
                    <div class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800 text-xs">
                        <span class="text-zinc-500 dark:text-zinc-400">Filter aktif diterapkan.</span>
                        <a href="{{ route('hotspot.users') }}" class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline">
                            Reset Filter
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Hotspot Users Table -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3.5 px-4 text-left">Username</th>
                            <th class="py-3.5 px-4 text-left">Password</th>
                            <th class="py-3.5 px-4 text-left">Kategori</th>
                            <th class="py-3.5 px-4 text-left">Profile</th>
                            <th class="py-3.5 px-4 text-left">Uptime Limit / Sesi</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                        @forelse($users as $user)
                            @php
                                $isOnline = isset($activeUserMap[$user->username]);
                                $cat = $user->profile?->category ?? 'voucher';
                            @endphp
                            <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40 transition-colors {{ !$user->is_active ? 'opacity-70 bg-zinc-50/40 dark:bg-zinc-900/40' : '' }}">
                                <!-- Username -->
                                <td class="py-3 px-4 text-left font-mono">
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('users.show', $user->username) }}" class="font-bold text-zinc-900 dark:text-zinc-100 hover:underline">
                                            {{ $user->username }}
                                        </a>
                                        <button 
                                            type="button" 
                                            @click="copyText('{{ $user->username }}', 'Username')" 
                                            class="p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200"
                                            title="Salin Username"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                    @if($user->comment)
                                        <div class="text-[10px] text-zinc-400 truncate max-w-[180px]" title="{{ $user->comment }}">{{ $user->comment }}</div>
                                    @endif
                                </td>

                                <!-- Password -->
                                <td class="py-3 px-4 text-left font-mono text-zinc-500 dark:text-zinc-400">
                                    {{ $user->password }}
                                </td>

                                <!-- Kategori -->
                                <td class="py-3 px-4 text-left">
                                    @if($cat === 'bulanan')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                            Bulanan
                                        </span>
                                    @elseif($cat === 'pribadi')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-300 dark:border-purple-800">
                                            Pribadi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                            Voucher
                                        </span>
                                    @endif
                                </td>

                                <!-- Profile -->
                                <td class="py-3 px-4 text-left">
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $user->profile?->name ?? 'Default' }}
                                    </span>
                                    @if($user->profile && $user->profile->selling_price > 0)
                                        <div class="text-[10px] text-zinc-400 font-mono">
                                            {{ \App\Support\FormatHelper::formatRupiah($user->profile->selling_price) }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Uptime / Sesi -->
                                <td class="py-3 px-4 text-left font-mono text-zinc-600 dark:text-zinc-400">
                                    @if($isOnline && !empty($activeUserMap[$user->username]))
                                        @php
                                            $liveSes = $activeUserMap[$user->username];
                                            $liveProg = \App\Support\FormatHelper::getUptimeProgress(
                                                is_array($liveSes) ? ($liveSes['uptime'] ?? '0s') : '0s',
                                                $user->uptime_limit ?: ($user->profile?->validity ?: (is_array($liveSes) ? ($liveSes['limit-uptime'] ?? null) : null)),
                                                is_array($liveSes) ? ($liveSes['session-time-left'] ?? null) : null
                                            );
                                        @endphp
                                        @if($liveProg['has_limit'] && $liveProg['remaining_formatted'])
                                            <div class="text-[10px] font-mono text-amber-600 dark:text-amber-400 font-semibold whitespace-nowrap">
                                                Sisa: {{ $liveProg['remaining_formatted'] }} ({{ $liveProg['remaining_percent'] }}%)
                                            </div>
                                            <div class="w-24 bg-zinc-200 dark:bg-zinc-700 rounded-full h-1 mt-1 overflow-hidden">
                                                <div class="bg-amber-500 h-1 rounded-full" style="width: {{ $liveProg['remaining_percent'] }}%"></div>
                                            </div>
                                        @else
                                            <div class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                                Aktif: {{ $liveProg['used_formatted'] }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="font-mono text-zinc-500 dark:text-zinc-400">
                                            {{ $user->uptime_limit ?: ($user->profile?->validity ?: 'Unlimited') }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="py-3 px-4 text-center">
                                    @if($isOnline)
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                                            </span>
                                            <button @click="toggleStatus({{ $user->id }})" class="text-[10px] text-zinc-400 hover:text-zinc-600 hover:underline">
                                                {{ $user->is_active ? 'Aktif' : 'In-Active' }}
                                            </button>
                                        </div>
                                    @else
                                        <button 
                                            @click="toggleStatus({{ $user->id }})" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold transition-colors cursor-pointer"
                                            :class="{{ $user->is_active ? 'true' : 'false' }} ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-zinc-500/10 text-zinc-500 border border-zinc-500/20 hover:bg-zinc-500/20'"
                                            title="{{ $user->is_active ? 'Klik untuk non-aktifkan' : 'Klik untuk aktifkan' }}"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-zinc-400' }}"></span>
                                            <span>{{ $user->is_active ? 'Aktif' : 'In-Active' }}</span>
                                        </button>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button 
                                            @click="editUser({{ json_encode($user) }})" 
                                            class="px-2.5 py-1 rounded text-xs font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            @click="deleteUser({{ $user->id }}, '{{ $user->username }}')" 
                                            class="px-2.5 py-1 rounded text-xs font-medium text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors cursor-pointer"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-zinc-400 text-xs">
                                    Tidak ada user hotspot yang cocok dengan filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs bg-zinc-50/50 dark:bg-zinc-800/30">
                <span class="text-zinc-500 dark:text-zinc-400">
                    Menampilkan {{ \App\Support\FormatHelper::formatNumber($users->firstItem() ?? 0) }} - {{ \App\Support\FormatHelper::formatNumber($users->lastItem() ?? 0) }} dari {{ \App\Support\FormatHelper::formatNumber($users->total()) }} user
                </span>
                <div>
                    {{ $users->links() }}
                </div>
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
                                <option :value="p.id" x-text="p.name + ' [' + (p.category === 'bulanan' ? 'Bulanan' : (p.category === 'pribadi' ? 'Pribadi' : 'Voucher')) + ']'"></option>
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
                isEditing: false,
                profiles: @json($profiles ?? []),
                userForm: { id: null, username: '', password: '', profile_id: '', uptime_limit: '3h', comment: '', is_active: true },
                openCreateModal() {
                    this.isEditing = false;
                    this.userForm = { id: null, username: '', password: '', profile_id: (this.profiles[0]?.id || ''), uptime_limit: '3h', comment: '', is_active: true };
                    this.$dispatch('open-modal', 'hotspot-user-modal');
                },
                editUser(u) {
                    this.isEditing = true;
                    this.userForm = {
                        id: u.id,
                        username: u.username,
                        password: u.password,
                        profile_id: u.profile_id || (u.profile?.id || ''),
                        uptime_limit: u.uptime_limit || '',
                        comment: u.comment || '',
                        is_active: !!u.is_active
                    };
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
                            window.showToast(`User ${this.userForm.username} berhasil disimpan!`, 'success');
                            this.$dispatch('close-modal', 'hotspot-user-modal');
                            setTimeout(() => window.location.reload(), 400);
                        } else {
                            window.showToast(data.message || 'Gagal menyimpan user', 'error');
                        }
                    })
                    .catch(err => {
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },
                deleteUser(id, username) {
                    if (confirm(`Apakah Anda yakin ingin menghapus user "${username}" dari MikroTik?`)) {
                        fetch(`/hotspot/users/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                window.showToast(`User ${username} berhasil dihapus`, 'info');
                                setTimeout(() => window.location.reload(), 400);
                            } else {
                                window.showToast(data.message || 'Gagal menghapus user', 'error');
                            }
                        })
                        .catch(err => {
                            window.showToast('Gagal menghapus user', 'error');
                        });
                    }
                },
                toggleStatus(id) {
                    fetch(`/hotspot/users/${id}/toggle`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.showToast(data.message, 'success');
                            setTimeout(() => window.location.reload(), 400);
                        } else {
                            window.showToast(data.message || 'Gagal mengubah status', 'error');
                        }
                    })
                    .catch(() => {
                        window.showToast('Gagal terhubung ke server', 'error');
                    });
                },
                copyText(text, label) {
                    if (!text) return;
                    navigator.clipboard.writeText(text).then(() => {
                        window.showToast(`${label} disalin ke clipboard!`, 'info');
                    }).catch(() => {
                        window.showToast('Gagal menyalin text', 'error');
                    });
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
