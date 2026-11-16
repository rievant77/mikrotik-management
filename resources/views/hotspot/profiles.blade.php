<x-layouts.app>
    <x-slot:header>Hotspot Profiles</x-slot:header>

    <div class="space-y-6" x-data="profilesManager()">
        <!-- Header & Top Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Hotspot Profiles & Paket</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Konfigurasi bandwidth limit, masa berlaku, dan harga jual voucher</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openCreateModal()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Tambah Profile</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3 sm:p-4 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <div class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[200px]">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Cari nama profile atau masa berlaku..." 
                        class="w-full pl-9 pr-8 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                    >
                    <button 
                        type="button" 
                        x-show="search" 
                        @click="search = ''" 
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Filter Kategori -->
                <div class="flex items-center gap-1.5">
                    <label class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 whitespace-nowrap">Kategori:</label>
                    <select 
                        x-model="filterCategory" 
                        class="px-2.5 py-1.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 focus:outline-none"
                    >
                        <option value="all">Semua Kategori</option>
                        <option value="voucher">Voucher (Profit)</option>
                        <option value="bulanan">Bulanan (Profit)</option>
                        <option value="pribadi">Pribadi (Non-Profit)</option>
                    </select>
                </div>

                <!-- Filter Status -->
                <div class="flex items-center gap-1.5">
                    <label class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 whitespace-nowrap">Status:</label>
                    <select 
                        x-model="filterStatus" 
                        class="px-2.5 py-1.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 focus:outline-none"
                    >
                        <option value="all">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Non-Aktif</option>
                    </select>
                </div>
            </div>

            <!-- View Toggle: Grid Cards vs Table Rows -->
            <div class="flex items-center justify-between sm:justify-end gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-zinc-100 dark:border-zinc-800">
                <span class="text-[11px] text-zinc-500 dark:text-zinc-400" x-text="formatNumber(filteredProfiles.length) + ' profile ditemukan'"></span>
                <div class="inline-flex rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 p-0.5">
                    <button 
                        type="button" 
                        @click="setViewMode('grid')" 
                        :class="viewMode === 'grid' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 shadow-xs' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'" 
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-all"
                        title="Tampilan Grid Card"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span class="hidden sm:inline">Grid</span>
                    </button>
                    <button 
                        type="button" 
                        @click="setViewMode('table')" 
                        :class="viewMode === 'table' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 shadow-xs' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'" 
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-medium transition-all"
                        title="Tampilan Baris Tabel"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        <span class="hidden sm:inline">Tabel</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State (No Profiles at all) -->
        <template x-if="profiles.length === 0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Belum Ada Hotspot Profile</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Tambahkan profile baru atau gunakan tombol Sync Router pada navigasi atas untuk menarik data profile dari MikroTik.</p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <button @click="openCreateModal()" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900">
                        Buat Profile Pertama
                    </button>
                </div>
            </div>
        </template>

        <!-- Empty Filter State (Search or Filter result 0) -->
        <template x-if="profiles.length > 0 && filteredProfiles.length === 0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-8 text-center shadow-xs">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Tidak ada profile yang sesuai dengan filter atau kata kunci pencarian.</p>
                <button @click="resetFilters()" class="mt-2 text-xs font-semibold text-zinc-900 dark:text-zinc-100 underline hover:no-underline">
                    Reset Filter
                </button>
            </div>
        </template>

        <!-- 1. Profiles Grid Cards View -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" x-show="viewMode === 'grid' && filteredProfiles.length > 0">
            <template x-for="p in filteredProfiles" :key="p.id">
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col justify-between hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <h3 class="font-bold text-base text-zinc-900 dark:text-zinc-100 truncate" x-text="p.name"></h3>
                                <template x-if="p.category === 'bulanan'">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800 shrink-0">
                                        Bulanan
                                    </span>
                                </template>
                                <template x-if="p.category === 'pribadi'">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-300 dark:border-purple-800 shrink-0">
                                        Pribadi
                                    </span>
                                </template>
                                <template x-if="p.category !== 'bulanan' && p.category !== 'pribadi'">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 shrink-0">
                                        Voucher
                                    </span>
                                </template>
                            </div>
                            <button @click="toggleStatus(p)" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold transition-colors cursor-pointer shrink-0"
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
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-500/20 px-1.5 py-0.5 rounded text-[10px]">Unlimited Device</span>
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
                            <div class="flex justify-between py-1 border-b border-zinc-100 dark:border-zinc-800/80 items-center">
                                <span>Harga Pokok</span>
                                <div class="text-right">
                                    <span class="font-mono text-zinc-500" x-text="formatRupiah(p.cost_price || 0)"></span>
                                    <template x-if="p.cost_price_type === 'percentage' && p.cost_price_percent">
                                        <span class="block text-[10px] text-zinc-400" x-text="'(' + p.cost_price_percent + '% dari jual)'"></span>
                                    </template>
                                </div>
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
                                    <span>FUP:</span>
                                    <span x-text="(p.fup_limit_display || '10GB') + ' &rarr; ' + (p.fup_rate_limit || '1M/1M')"></span>
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

        <!-- 2. Profiles Table View -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs" x-show="viewMode === 'table' && filteredProfiles.length > 0">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400 font-medium">
                            <th class="py-3 px-4">Nama Profile</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Rate Limit</th>
                            <th class="py-3 px-4">Shared</th>
                            <th class="py-3 px-4">Validity</th>
                            <th class="py-3 px-4">Harga Pokok</th>
                            <th class="py-3 px-4">Harga Jual</th>
                            <th class="py-3 px-4">Expired / FUP</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                        <template x-for="p in filteredProfiles" :key="p.id">
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100" x-text="p.name"></td>
                                <td class="py-3 px-4">
                                    <template x-if="p.category === 'bulanan'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                            Bulanan
                                        </span>
                                    </template>
                                    <template x-if="p.category === 'pribadi'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-300 dark:border-purple-800">
                                            Pribadi
                                        </span>
                                    </template>
                                    <template x-if="p.category !== 'bulanan' && p.category !== 'pribadi'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">
                                            Voucher
                                        </span>
                                    </template>
                                </td>
                                <td class="py-3 px-4 font-mono text-zinc-700 dark:text-zinc-300">
                                    <span x-text="p.rate_limit || 'Unlimited'"></span>
                                </td>
                                <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400">
                                    <span x-text="(!p.shared_users || p.shared_users == 0) ? 'Unlimited' : (p.shared_users + ' Device')"></span>
                                </td>
                                <td class="py-3 px-4 font-mono text-zinc-700 dark:text-zinc-300" x-text="p.validity || 'Unlimited'"></td>
                                <td class="py-3 px-4 font-mono text-zinc-500">
                                    <span x-text="formatRupiah(p.cost_price || 0)"></span>
                                    <template x-if="p.cost_price_type === 'percentage' && p.cost_price_percent">
                                        <span class="text-[10px] text-zinc-400 block" x-text="'(' + p.cost_price_percent + '%)'"></span>
                                    </template>
                                </td>
                                <td class="py-3 px-4 font-mono font-semibold text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(p.selling_price || 0)"></td>
                                <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="text-[11px]" x-text="p.expired_mode || 'Remove'"></span>
                                        <template x-if="p.fup_enabled">
                                            <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold" x-text="'FUP: ' + (p.fup_limit_display || '10GB') + ' &rarr; ' + (p.fup_rate_limit || '-')"></span>
                                        </template>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button @click="toggleStatus(p)" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold transition-colors cursor-pointer"
                                        :class="p.is_active !== false && p.is_active !== 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-zinc-500/10 text-zinc-500 border border-zinc-500/20'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="p.is_active !== false && p.is_active !== 0 ? 'bg-emerald-500' : 'bg-zinc-400'"></span>
                                        <span x-text="p.is_active !== false && p.is_active !== 0 ? 'Aktif' : 'Non-aktif'"></span>
                                    </button>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button @click="editProfile(p)" class="px-2 py-1 rounded text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors font-medium">
                                            Edit
                                        </button>
                                        <button @click="deleteProfile(p)" class="px-2 py-1 rounded text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors font-medium">
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

        <!-- Create/Edit Profile Modal -->
        <x-modal name="profile-form-modal" title="Konfigurasi Hotspot Profile">
            <form @submit.prevent="saveProfile()" class="space-y-4 text-xs">
                <!-- Profile Category Selection -->
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Kategori Profile *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <label 
                            class="relative flex flex-col p-3 rounded-lg border cursor-pointer transition-all"
                            :class="form.category === 'voucher' ? 'border-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 dark:border-emerald-500 ring-1 ring-emerald-500' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 hover:border-zinc-300 dark:hover:border-zinc-600'"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Voucher
                                </span>
                                <input type="radio" name="category" value="voucher" x-model="form.category" class="sr-only">
                                <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center" :class="form.category === 'voucher' ? 'border-emerald-600 bg-emerald-600' : 'border-zinc-300 dark:border-zinc-600'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="form.category === 'voucher'"></span>
                                </span>
                            </div>
                            <span class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">Paket jam/harian. Tampil di generator voucher & POS.</span>
                        </label>
                        <label 
                            class="relative flex flex-col p-3 rounded-lg border cursor-pointer transition-all"
                            :class="form.category === 'bulanan' ? 'border-blue-500 bg-blue-50/40 dark:bg-blue-950/20 dark:border-blue-500 ring-1 ring-blue-500' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 hover:border-zinc-300 dark:hover:border-zinc-600'"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    Bulanan
                                </span>
                                <input type="radio" name="category" value="bulanan" x-model="form.category" class="sr-only">
                                <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center" :class="form.category === 'bulanan' ? 'border-blue-600 bg-blue-600' : 'border-zinc-300 dark:border-zinc-600'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="form.category === 'bulanan'"></span>
                                </span>
                            </div>
                            <span class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">Langganan tetap bulanan. Dipilih pada data pelanggan POS.</span>
                        </label>
                        <label 
                            class="relative flex flex-col p-3 rounded-lg border cursor-pointer transition-all"
                            :class="form.category === 'pribadi' ? 'border-purple-500 bg-purple-50/40 dark:bg-purple-950/20 dark:border-purple-500 ring-1 ring-purple-500' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 hover:border-zinc-300 dark:hover:border-zinc-600'"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                    Pribadi
                                </span>
                                <input type="radio" name="category" value="pribadi" x-model="form.category" class="sr-only">
                                <span class="w-3.5 h-3.5 rounded-full border flex items-center justify-center" :class="form.category === 'pribadi' ? 'border-purple-600 bg-purple-600' : 'border-zinc-300 dark:border-zinc-600'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="form.category === 'pribadi'"></span>
                                </span>
                            </div>
                            <span class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">Pemakaian pribadi / internal. Dikecualikan dari omset.</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nama Profile *</label>
                    <input type="text" x-model="form.name" required class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100" placeholder="e.g. Paket-12Jam / VIP-Office">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300">Rate Limit (Rx/Tx)</label>
                            <button type="button" @click="form.rate_limit = ''" class="text-[10px] text-emerald-600 dark:text-emerald-400 hover:underline">Set Unlimited</button>
                        </div>
                        <input type="text" x-model="form.rate_limit" @input="updateFupPreview()" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono" placeholder="Kosongkan jika Unlimited (e.g. 10M/10M)">
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

                <!-- Validity & Presets (Includes 12h) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300">Masa Berlaku (Validity)</label>
                        <button type="button" @click="form.validity = 'Unlimited'" class="text-[10px] text-emerald-600 dark:text-emerald-400 hover:underline">Set Unlimited</button>
                    </div>
                    <input type="text" x-model="form.validity" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono" placeholder="e.g. 12h, 24 Jam, 3 Hari, Unlimited">
                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                        <button type="button" @click="form.validity = '12h'" class="px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200">12 Jam (12h)</button>
                        <button type="button" @click="form.validity = '24 Jam'" class="px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200">24 Jam</button>
                        <button type="button" @click="form.validity = '3 Hari'" class="px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200">3 Hari</button>
                        <button type="button" @click="form.validity = '7 Hari'" class="px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200">7 Hari</button>
                        <button type="button" @click="form.validity = '30 Hari'" class="px-2 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200">30 Hari</button>
                        <button type="button" @click="form.validity = 'Unlimited'" class="px-2 py-0.5 rounded text-[10px] bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 font-semibold">Unlimited</button>
                    </div>
                </div>

                <!-- Harga Jual & Harga Pokok (HPP) Mode -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300">Harga Jual *</label>
                            <button type="button" @click="form.selling_price = 0; updateCostPriceFromType()" class="text-[10px] text-emerald-600 dark:text-emerald-400 hover:underline">Set Gratis (0)</button>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-semibold text-zinc-400 select-none">Rp</span>
                            <input 
                                type="text" 
                                inputmode="numeric"
                                :value="formatNumber(form.selling_price)" 
                                @input="form.selling_price = parseNumber($event.target.value); $event.target.value = formatNumber(form.selling_price); updateCostPriceFromType()" 
                                class="w-full pl-9 pr-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono font-bold" 
                                placeholder="0 (Gratis)"
                            >
                        </div>
                        <span class="text-[10px] text-zinc-500 mt-1 block">Harga voucher saat dijual ke pembeli.</span>
                    </div>

                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Metode Harga Pokok (HPP)</label>
                        <select x-model="form.cost_price_type" @change="updateCostPriceFromType()" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                            <option value="custom">Nominal Manual (Custom)</option>
                            <option value="percentage">Persentase (%) dari Harga Jual</option>
                            <option value="fixed">Fixed (Nilai Tetap)</option>
                        </select>
                    </div>
                </div>

                <!-- HPP Value Input / Percentage Calculator -->
                <div class="bg-zinc-50 dark:bg-zinc-800/40 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700/80">
                    <template x-if="form.cost_price_type === 'percentage'">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="font-medium text-zinc-700 dark:text-zinc-300">Persentase HPP (%)</label>
                                <span class="font-mono text-zinc-600 dark:text-zinc-400 text-[11px]" x-text="'HPP Terhitung: ' + formatRupiah(form.cost_price)"></span>
                            </div>
                            <div class="relative">
                                <input 
                                    type="number" 
                                    x-model="form.cost_price_percent" 
                                    @input="updateCostPriceFromType()" 
                                    min="0" 
                                    max="100" 
                                    step="0.5" 
                                    placeholder="e.g. 25" 
                                    class="w-full px-3 py-2 pr-8 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono"
                                >
                                <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs font-semibold text-zinc-400">%</span>
                            </div>
                            <div class="flex gap-1.5">
                                <button type="button" @click="form.cost_price_percent = 10; updateCostPriceFromType()" class="px-2 py-0.5 rounded text-[10px] bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">10%</button>
                                <button type="button" @click="form.cost_price_percent = 20; updateCostPriceFromType()" class="px-2 py-0.5 rounded text-[10px] bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">20%</button>
                                <button type="button" @click="form.cost_price_percent = 30; updateCostPriceFromType()" class="px-2 py-0.5 rounded text-[10px] bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">30%</button>
                                <button type="button" @click="form.cost_price_percent = 50; updateCostPriceFromType()" class="px-2 py-0.5 rounded text-[10px] bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">50%</button>
                            </div>
                        </div>
                    </template>

                    <template x-if="form.cost_price_type !== 'percentage'">
                        <div>
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1" x-text="form.cost_price_type === 'fixed' ? 'Nominal Tetap HPP (Rp)' : 'Nominal Harga Pokok Manual (Rp)'"></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-semibold text-zinc-400 select-none">Rp</span>
                                <input 
                                    type="text" 
                                    inputmode="numeric"
                                    :value="formatNumber(form.cost_price)" 
                                    @input="form.cost_price = parseNumber($event.target.value); $event.target.value = formatNumber(form.cost_price)" 
                                    class="w-full pl-9 pr-3 py-2 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono" 
                                    placeholder="0"
                                >
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Expired Mode (Includes Notice & Record) -->
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Expired Mode</label>
                    <select x-model="form.expired_mode" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                        <option value="Remove">Remove (Hapus Akun)</option>
                        <option value="Notice">Notice (Peringatan Saja)</option>
                        <option value="Notice & Record">Notice & Record (Peringatan & Catat Otomatis)</option>
                        <option value="Remove & Record">Remove & Record (Hapus & Catat)</option>
                    </select>
                    <span class="text-[10px] text-zinc-500 mt-1 block">Mode penanganan saat masa berlaku voucher habis di RouterOS.</span>
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
                        <div>
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Batas Kuota FUP</label>
                            <input type="text" x-model="form.fup_limit_display" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono" placeholder="e.g. 5 GB, 10 GB">
                            <span class="text-[10px] text-zinc-400 mt-0.5 block">Format: 5 GB, 10 GB, 500 MB</span>
                        </div>

                        <!-- FUP Speed Calculation via Percentage of Base Speed -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-medium text-zinc-700 dark:text-zinc-300">Persentase Kecepatan FUP (% dari Speed Awal)</label>
                                <span class="text-[10px] font-semibold text-amber-700 dark:text-amber-400 font-mono" x-show="form.fup_rate_limit" x-text="'Hasil: ' + form.fup_rate_limit"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 items-center">
                                <div class="col-span-1 relative">
                                    <input 
                                        type="number" 
                                        x-model="form.fup_rate_percent" 
                                        @input="updateFupPreview()" 
                                        min="1" 
                                        max="99" 
                                        class="w-full px-3 py-2 pr-8 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono" 
                                        placeholder="50"
                                    >
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs font-semibold text-zinc-400">%</span>
                                </div>
                                <div class="col-span-2">
                                    <input 
                                        type="text" 
                                        x-model="form.fup_rate_limit" 
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono text-xs" 
                                        placeholder="e.g. 5M/5M"
                                    >
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1.5 mt-1.5">
                                <button type="button" @click="form.fup_rate_percent = 10; updateFupPreview()" class="px-2 py-0.5 rounded text-[10px] bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">10%</button>
                                <button type="button" @click="form.fup_rate_percent = 25; updateFupPreview()" class="px-2 py-0.5 rounded text-[10px] bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">25%</button>
                                <button type="button" @click="form.fup_rate_percent = 50; updateFupPreview()" class="px-2 py-0.5 rounded text-[10px] bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">50%</button>
                                <button type="button" @click="form.fup_rate_percent = 75; updateFupPreview()" class="px-2 py-0.5 rounded text-[10px] bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">75%</button>
                            </div>
                            <span class="text-[10px] text-zinc-500 mt-1 block">Contoh: 50% dari speed awal <strong>10M/10M</strong> otomatis menjadi <strong>5M/5M</strong>.</span>
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
                    <button type="button" @click="$dispatch('close-modal', 'profile-form-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-semibold hover:bg-zinc-800 transition-colors" x-text="isEditing ? 'Perbarui Profile' : 'Simpan Profile'"></button>
                </div>
            </form>
        </x-modal>
    </div>

    @push('scripts')
    <script>
        function profilesManager() {
            return {
                isEditing: false,
                viewMode: localStorage.getItem('hotspot_profiles_view_mode') || 'grid',
                search: '',
                filterCategory: 'all',
                filterStatus: 'all',
                profiles: @json($profiles ?? []),
                form: { 
                    id: null, 
                    name: '', 
                    category: 'voucher',
                    rate_limit: '', 
                    shared_users: 1, 
                    validity: '12h', 
                    cost_price_type: 'custom',
                    cost_price_percent: null,
                    cost_price: 0, 
                    selling_price: 0, 
                    expired_mode: 'Remove', 
                    fup_enabled: false, 
                    fup_limit_display: '10 GB', 
                    fup_rate_percent: 50,
                    fup_rate_limit: '1M/1M', 
                    fup_reset_cycle: 'daily' 
                },
                setViewMode(mode) {
                    this.viewMode = mode;
                    localStorage.setItem('hotspot_profiles_view_mode', mode);
                },
                resetFilters() {
                    this.search = '';
                    this.filterCategory = 'all';
                    this.filterStatus = 'all';
                },
                get filteredProfiles() {
                    return this.profiles.filter(p => {
                        // Search
                        if (this.search && this.search.trim() !== '') {
                            const q = this.search.trim().toLowerCase();
                            const nameMatch = (p.name || '').toLowerCase().includes(q);
                            const validityMatch = (p.validity || '').toLowerCase().includes(q);
                            const rateMatch = (p.rate_limit || '').toLowerCase().includes(q);
                            if (!nameMatch && !validityMatch && !rateMatch) return false;
                        }

                        // Category
                        if (this.filterCategory !== 'all') {
                            const cat = (p.category === 'profit' ? 'voucher' : p.category) || 'voucher';
                            if (cat !== this.filterCategory) return false;
                        }

                        // Status
                        if (this.filterStatus !== 'all') {
                            const isActive = p.is_active !== false && p.is_active !== 0;
                            if (this.filterStatus === 'active' && !isActive) return false;
                            if (this.filterStatus === 'inactive' && isActive) return false;
                        }

                        return true;
                    });
                },
                calculateFupSpeed(baseRate, percent) {
                    if (!baseRate || !percent || percent <= 0) return '';
                    const multiplier = percent / 100;
                    if (multiplier >= 1.0) return baseRate.trim();

                    const parts = baseRate.trim().split('/');
                    const calculated = parts.map(part => {
                        const m = part.trim().match(/^(\d+(?:\.\d+)?)\s*([kKmMgG])?$/);
                        if (!m) return part.trim();
                        const val = parseFloat(m[1]);
                        const unit = m[2] ? m[2].toLowerCase() : '';
                        let kbps = unit === 'g' ? val * 1000000 : (unit === 'm' ? val * 1000 : (unit === 'k' ? val : val / 1000));
                        let throttled = kbps * multiplier;
                        if (throttled >= 1000 && throttled % 1000 === 0) {
                            return (throttled / 1000) + 'M';
                        }
                        return Math.max(1, Math.round(throttled)) + 'k';
                    });
                    return calculated.join('/');
                },
                updateFupPreview() {
                    if (this.form.rate_limit && this.form.fup_rate_percent) {
                        const calculated = this.calculateFupSpeed(this.form.rate_limit, parseFloat(this.form.fup_rate_percent));
                        if (calculated) {
                            this.form.fup_rate_limit = calculated;
                        }
                    }
                },
                updateCostPriceFromType() {
                    if (this.form.cost_price_type === 'percentage') {
                        const selling = parseFloat(this.form.selling_price) || 0;
                        const pct = parseFloat(this.form.cost_price_percent) || 0;
                        this.form.cost_price = Math.round(selling * (pct / 100));
                    }
                },
                openCreateModal() {
                    this.isEditing = false;
                    this.form = { 
                        id: null, 
                        name: '', 
                        category: 'voucher',
                        rate_limit: '', 
                        shared_users: 1, 
                        validity: '12h', 
                        cost_price_type: 'custom',
                        cost_price_percent: null,
                        cost_price: 0, 
                        selling_price: 0, 
                        expired_mode: 'Remove', 
                        fup_enabled: false, 
                        fup_limit_display: '10 GB', 
                        fup_rate_percent: 50,
                        fup_rate_limit: '', 
                        fup_reset_cycle: 'daily' 
                    };
                    this.$dispatch('open-modal', 'profile-form-modal');
                },
                editProfile(p) {
                    this.isEditing = true;
                    this.form = { 
                        id: p.id,
                        name: p.name,
                        category: (p.category === 'profit' ? 'voucher' : p.category) || 'voucher',
                        rate_limit: p.rate_limit || '',
                        shared_users: p.shared_users ?? 1,
                        validity: p.validity || 'Unlimited',
                        cost_price_type: p.cost_price_type || 'custom',
                        cost_price_percent: p.cost_price_percent ?? null,
                        cost_price: p.cost_price ?? 0,
                        selling_price: p.selling_price ?? 0,
                        expired_mode: p.expired_mode || 'Remove',
                        fup_enabled: !!p.fup_enabled,
                        fup_limit_display: p.fup_limit_display || '10 GB',
                        fup_rate_percent: p.fup_rate_percent ?? 50,
                        fup_rate_limit: p.fup_rate_limit || '',
                        fup_reset_cycle: p.fup_reset_cycle || 'daily'
                    };
                    this.$dispatch('open-modal', 'profile-form-modal');
                },
                saveProfile() {
                    this.updateCostPriceFromType();
                    this.form.selling_price = window.parseNumber(this.form.selling_price);
                    this.form.cost_price = window.parseNumber(this.form.cost_price);
                    this.form.shared_users = this.form.shared_users !== '' ? parseInt(this.form.shared_users, 10) : 0;
                    if (this.form.cost_price_percent !== null && this.form.cost_price_percent !== '') {
                        this.form.cost_price_percent = parseFloat(this.form.cost_price_percent);
                    }
                    if (this.form.fup_rate_percent !== null && this.form.fup_rate_percent !== '') {
                        this.form.fup_rate_percent = parseInt(this.form.fup_rate_percent, 10);
                    }

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
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
