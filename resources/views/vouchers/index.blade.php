<x-layouts.app>
    <x-slot:header>Kelola User & Voucher</x-slot:header>

    <div class="space-y-6" x-data="vouchersManager()">
        <!-- Header Page -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight flex items-center gap-2.5">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    <span>Kelola User & Voucher Hotspot</span>
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Daftar & Cetak Voucher, kelola akun hotspot MikroTik, filter batch, dan cetak struk thermal atau grid.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button 
                    type="button" 
                    @click="openCreateModal()" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 shadow-xs transition-colors cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Tambah User</span>
                </button>
                <a 
                    href="{{ route('hotspot.generate') }}" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs"
                >
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Generate Massal</span>
                </a>
                <button 
                    type="button" 
                    @click="openPrintPreview('58mm')" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs cursor-pointer"
                >
                    <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak</span>
                </button>
            </div>
        </div>

        @php
            $currentStatus = request('status', 'active');
        @endphp

        <!-- 5 KPI Summary Cards (Interactive Status Filters) -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <!-- 1. Total Voucher (Semua) -->
            <a 
                href="{{ route('vouchers.index', array_merge(request()->query(), ['status' => 'all'])) }}" 
                class="p-4 rounded-xl bg-white dark:bg-zinc-900 border shadow-xs transition-all duration-150 hover:shadow-sm block text-left cursor-pointer {{ $currentStatus === 'all' ? 'ring-2 ring-zinc-900 dark:ring-zinc-100 border-transparent bg-zinc-50/60 dark:bg-zinc-800/50' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700' }}"
                title="Klik untuk melihat semua voucher tanpa filter status"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total Voucher</span>
                    <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-extrabold text-zinc-900 dark:text-zinc-100 font-mono">
                    {{ \App\Support\FormatHelper::formatNumber($stats['total']) }}
                </div>
                <div class="flex items-center justify-between mt-1">
                    <span class="text-[11px] text-zinc-500 dark:text-zinc-400">Semua Tersimpan</span>
                    @if($currentStatus === 'all')
                        <span class="text-[10px] font-semibold text-zinc-700 dark:text-zinc-300">Semua</span>
                    @endif
                </div>
            </a>

            <!-- 2. Sedang Online -->
            <a 
                href="{{ route('vouchers.index', array_merge(request()->query(), ['status' => 'online'])) }}" 
                class="p-4 rounded-xl bg-white dark:bg-zinc-900 border shadow-xs transition-all duration-150 hover:shadow-sm block text-left cursor-pointer {{ $currentStatus === 'online' ? 'ring-2 ring-emerald-500 border-transparent bg-emerald-50/30 dark:bg-emerald-950/20' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700' }}"
                title="Klik untuk memfilter voucher yang sedang online"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Sedang Online</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">
                    {{ \App\Support\FormatHelper::formatNumber($stats['online']) }}
                </div>
                <div class="flex items-center justify-between mt-1">
                    <span class="text-[11px] text-zinc-500 dark:text-zinc-400">Aktif di Router</span>
                    @if($currentStatus === 'online')
                        <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">✓ Filter Aktif</span>
                    @endif
                </div>
            </a>

            <!-- 3. Voucher Aktif / Ready -->
            <a 
                href="{{ route('vouchers.index', array_merge(request()->query(), ['status' => 'active'])) }}" 
                class="p-4 rounded-xl bg-white dark:bg-zinc-900 border shadow-xs transition-all duration-150 hover:shadow-sm block text-left cursor-pointer {{ $currentStatus === 'active' ? 'ring-2 ring-sky-500 border-transparent bg-sky-50/30 dark:bg-sky-950/20' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700' }}"
                title="Klik untuk memfilter voucher aktif / siap pakai"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-sky-600 dark:text-sky-400">Voucher Aktif</span>
                    <svg class="w-4 h-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-extrabold text-sky-600 dark:text-sky-400 font-mono">
                    {{ \App\Support\FormatHelper::formatNumber($stats['active'] ?? $stats['total']) }}
                </div>
                <div class="flex items-center justify-between mt-1">
                    <span class="text-[11px] text-zinc-500 dark:text-zinc-400">Ready / Siap Pakai</span>
                    @if($currentStatus === 'active')
                        <span class="text-[10px] font-semibold text-sky-600 dark:text-sky-400">✓ Filter Aktif</span>
                    @endif
                </div>
            </a>

            <!-- 4. Non-Aktif / Expired -->
            <a 
                href="{{ route('vouchers.index', array_merge(request()->query(), ['status' => 'inactive'])) }}" 
                class="p-4 rounded-xl bg-white dark:bg-zinc-900 border shadow-xs transition-all duration-150 hover:shadow-sm block text-left cursor-pointer {{ in_array($currentStatus, ['inactive', 'disabled']) ? 'ring-2 ring-rose-500 border-transparent bg-rose-50/30 dark:bg-rose-950/20' : 'border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700' }}"
                title="Klik untuk memfilter voucher non-aktif atau expired"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-rose-600 dark:text-rose-400">Non-Aktif / Expired</span>
                    <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-extrabold text-rose-600 dark:text-rose-400 font-mono">
                    {{ \App\Support\FormatHelper::formatNumber($stats['inactive'] ?? 0) }}
                </div>
                <div class="flex items-center justify-between mt-1">
                    <span class="text-[11px] text-zinc-500 dark:text-zinc-400">Habis / Dinonaktifkan</span>
                    @if(in_array($currentStatus, ['inactive', 'disabled']))
                        <span class="text-[10px] font-semibold text-rose-600 dark:text-rose-400">✓ Filter Aktif</span>
                    @endif
                </div>
            </a>

            <!-- 5. Nilai Inventory -->
            <div class="p-4 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs col-span-2 md:col-span-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Nilai Inventory</span>
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-extrabold text-zinc-900 dark:text-zinc-100 font-mono">
                    {{ \App\Support\FormatHelper::formatRupiah($stats['inventory_value']) }}
                </div>
                <span class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 block truncate">
                    @if(request('category') === 'pribadi')
                        Non-profit (dikecualikan)
                    @else
                        Potensi omzet kasir
                    @endif
                </span>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-4 shadow-xs">
            <form method="GET" action="{{ route('vouchers.index') }}" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <!-- Search Input -->
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 mb-1">Cari Voucher</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}" 
                                placeholder="Kode username, pass, batch..." 
                                class="w-full pl-8 pr-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600"
                            >
                            <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            <option value="voucher" {{ request('category') === 'voucher' ? 'selected' : '' }}>Voucher (Komersial)</option>
                            <option value="bulanan" {{ request('category') === 'bulanan' ? 'selected' : '' }}>Bulanan (Langganan)</option>
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
                            <option value="">Semua Paket Hotspot</option>
                            @foreach($profiles as $p)
                                @php
                                    $catLabel = match($p->category) {
                                        'bulanan' => 'Bulanan',
                                        'pribadi' => 'Pribadi',
                                        default => 'Voucher',
                                    };
                                @endphp
                                <option value="{{ $p->id }}" {{ request('profile_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} [{{ $catLabel }}] ({{ \App\Support\FormatHelper::formatRupiah($p->selling_price) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Batch Tag Filter -->
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 mb-1">Batch Generate</label>
                        <select 
                            name="batch" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                        >
                            <option value="">Semua Batch</option>
                            @foreach($batches as $b)
                                <option value="{{ $b }}" {{ request('batch') == $b ? 'selected' : '' }}>
                                    {{ Str::limit($b, 35) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 mb-1">Status Voucher</label>
                        <select 
                            name="status" 
                            onchange="this.form.submit()" 
                            class="w-full px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                        >
                            <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Aktif / Ready (Default)</option>
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Semua (Termasuk In-Active)</option>
                            <option value="online" {{ request('status') === 'online' ? 'selected' : '' }}>Sedang Online</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>In-Active / Non-Aktif</option>
                        </select>
                    </div>
                </div>

                @if(request()->hasAny(['search', 'profile_id', 'batch']) || (request('category') && request('category') !== 'all') || (request('status') && request('status') !== 'active'))
                    <div class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800 text-xs">
                        <span class="text-zinc-500 dark:text-zinc-400">Filter aktif diterapkan.</span>
                        <a href="{{ route('vouchers.index') }}" class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline">
                            Reset Filter
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Master Checkbox & Selection Bar (Mobile & Desktop) -->
        <div class="flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/60 p-3 rounded-xl border border-zinc-200 dark:border-zinc-700 text-xs">
            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input 
                    type="checkbox" 
                    @change="toggleSelectAll({{ json_encode($vouchers->pluck('id')) }})" 
                    :checked="isAllSelected({{ json_encode($vouchers->pluck('id')) }})"
                    class="rounded border-zinc-300 dark:border-zinc-700 text-zinc-900 focus:ring-zinc-900 dark:bg-zinc-800"
                >
                <span class="font-bold text-zinc-800 dark:text-zinc-200">Pilih Semua di Halaman Ini ({{ \App\Support\FormatHelper::formatNumber($vouchers->count()) }} voucher)</span>
            </label>

            <div class="flex items-center gap-2">
                <span class="text-zinc-500 dark:text-zinc-400" x-text="formatNumber(selectedVouchers.length) + ' terpilih'"></span>
                <template x-if="selectedVouchers.length > 0">
                    <button 
                        type="button" 
                        @click="selectedVouchers = []" 
                        class="text-[11px] font-semibold text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 underline ml-1"
                    >
                        Batal
                    </button>
                </template>
            </div>
        </div>

        <!-- 1. MOBILE VIEW: Responsive Cards (< 640px) -->
        <div class="block sm:hidden space-y-3">
            @forelse($vouchers as $v)
                @php
                    $isOnline = isset($activeUserMap[$v->username]);
                @endphp
                <div 
                    :class="isSelected({{ $v->id }}) ? 'border-zinc-900 dark:border-zinc-100 ring-1 ring-zinc-900 dark:ring-zinc-100 bg-zinc-50/80 dark:bg-zinc-800/80' : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900'"
                    class="p-4 rounded-xl border transition-all shadow-xs {{ !$v->is_active ? 'opacity-70 bg-zinc-100/50 dark:bg-zinc-900/50' : '' }}"
                >
                    <!-- Top Row: Checkbox, Code & Status -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <input 
                                type="checkbox" 
                                value="{{ $v->id }}" 
                                @change="toggleSelect({{ $v->id }})" 
                                :checked="isSelected({{ $v->id }})"
                                class="rounded border-zinc-300 dark:border-zinc-700 text-zinc-900 focus:ring-zinc-900 dark:bg-zinc-800 mt-0.5"
                            >
                            <div>
                                <span class="text-[10px] text-zinc-500 dark:text-zinc-400 block font-medium">
                                    {{ ($v->password && $v->password !== $v->username) ? 'Username' : 'Kode Login' }}
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-extrabold text-base text-zinc-900 dark:text-zinc-100 tracking-wider">
                                        {{ $v->username }}
                                    </span>
                                    <button 
                                        type="button" 
                                        @click="copyText('{{ $v->username }}', '{{ ($v->password && $v->password !== $v->username) ? 'Username' : 'Kode Login' }}')" 
                                        class="p-1 text-zinc-400 hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-200"
                                        title="Salin {{ ($v->password && $v->password !== $v->username) ? 'Username' : 'Kode Login' }}"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </div>
                                @if($v->password && $v->password !== $v->username)
                                    <div class="text-[11px] font-mono text-zinc-500 dark:text-zinc-400">
                                        Pass: <strong class="text-zinc-800 dark:text-zinc-200">{{ $v->password }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div>
                            @if($isOnline)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                                </span>
                            @elseif($v->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                    Ready
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-zinc-200 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 border border-zinc-300 dark:border-zinc-600">
                                    In-Active
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Meta Details -->
                    <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-[10px] text-zinc-500 dark:text-zinc-400 block">Paket & Harga:</span>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $v->profile?->name ?? 'Default' }}</span>
                                @php
                                    $cat = $v->profile?->category ?? 'voucher';
                                @endphp
                                @if($cat === 'bulanan')
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                        Bulanan
                                    </span>
                                @elseif($cat === 'pribadi')
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-semibold bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-300 dark:border-purple-800">
                                        Pribadi
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                        Voucher
                                    </span>
                                @endif
                            </div>
                            <span class="text-emerald-600 dark:text-emerald-400 font-mono font-semibold">
                                ({{ \App\Support\FormatHelper::formatRupiah($v->profile?->selling_price ?? 3000) }})
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-zinc-500 dark:text-zinc-400 block">Masa Aktif / Limit:</span>
                            <span class="font-mono text-zinc-700 dark:text-zinc-300">{{ $v->profile?->validity ?? ($v->uptime_limit ?: '-') }}</span>
                            @if($isOnline && !empty($activeUserMap[$v->username]))
                                @php
                                    $liveSes = $activeUserMap[$v->username];
                                    $liveProg = \App\Support\FormatHelper::getUptimeProgress(is_array($liveSes) ? ($liveSes['uptime'] ?? '0s') : '0s', $v->uptime_limit ?: ($v->profile?->validity ?: (is_array($liveSes) ? ($liveSes['limit-uptime'] ?? null) : null)), is_array($liveSes) ? ($liveSes['session-time-left'] ?? null) : null);
                                @endphp
                                @if($liveProg['has_limit'] && $liveProg['remaining_formatted'])
                                    <div class="text-[10px] font-mono text-amber-600 dark:text-amber-400 font-semibold mt-0.5">
                                        Sisa: {{ $liveProg['remaining_formatted'] }} ({{ $liveProg['remaining_percent'] }}%)
                                    </div>
                                @elseif($isOnline)
                                    <div class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">
                                        Aktif: {{ $liveProg['used_formatted'] }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    @if($v->comment)
                        <div class="mt-2 text-[10px] text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800/50 px-2 py-1 rounded truncate">
                            {{ $v->comment }}
                        </div>
                    @endif

                    <!-- Action Buttons per Voucher -->
                    <div class="mt-3 pt-2.5 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button 
                                type="button" 
                                @click="openPrintPreview('58mm', [{{ $v->id }}])" 
                                class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors flex items-center gap-1 cursor-pointer"
                                title="Pratinjau & Cetak Voucher"
                            >
                                <svg class="w-3.5 h-3.5 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                <span>Cetak</span>
                            </button>
                            <button 
                                type="button" 
                                @click="openWhatsAppModal({{ json_encode($v) }})" 
                                class="px-2 py-1 text-[11px] font-semibold rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-1 cursor-pointer"
                                title="Kirim detail voucher ke WhatsApp"
                            >
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.087-.177.181-.076.355.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.087.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                                </svg>
                                <span>WA</span>
                            </button>
                            <button 
                                type="button" 
                                @click="editUser({{ json_encode($v) }})" 
                                class="px-2 py-1 text-[11px] font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors cursor-pointer"
                            >
                                Edit
                            </button>
                            <button 
                                type="button" 
                                @click="deleteSingleUser({{ $v->id }}, '{{ $v->username }}')" 
                                class="px-2 py-1 text-[11px] font-semibold rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors cursor-pointer"
                            >
                                Hapus
                            </button>
                        </div>
                        <a 
                            href="{{ route('users.show', $v->username) }}" 
                            class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 transition-colors shrink-0"
                        >
                            Detail →
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400 text-xs">
                    Tidak ada voucher yang sesuai dengan filter pencarian.
                </div>
            @endforelse
        </div>

        <!-- 2. DESKTOP VIEW: Data Table (>= 640px) -->
        <div class="hidden sm:block bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400">
                        <tr>
                            <th class="py-3 px-4 w-10 text-center">
                                <input 
                                    type="checkbox" 
                                    @change="toggleSelectAll({{ json_encode($vouchers->pluck('id')) }})" 
                                    :checked="isAllSelected({{ json_encode($vouchers->pluck('id')) }})"
                                    class="rounded border-zinc-300 dark:border-zinc-700 text-zinc-900 focus:ring-zinc-900 dark:bg-zinc-800"
                                >
                            </th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[11px]">Kode Login / User</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[11px]">Paket & Harga</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[11px]">Limit Waktu</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[11px]">Batch / Komentar</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[11px]">Status</th>
                            <th class="py-3 px-4 font-semibold uppercase tracking-wider text-[11px] text-right">Aksi Cetak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                        @forelse($vouchers as $v)
                            @php
                                $isOnline = isset($activeUserMap[$v->username]);
                            @endphp
                            <tr 
                                :class="isSelected({{ $v->id }}) ? 'bg-zinc-50/90 dark:bg-zinc-800/60' : 'hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30'"
                                class="transition-colors"
                            >
                                <!-- Checkbox -->
                                <td class="py-3 px-4 text-center">
                                    <input 
                                        type="checkbox" 
                                        value="{{ $v->id }}" 
                                        @change="toggleSelect({{ $v->id }})" 
                                        :checked="isSelected({{ $v->id }})"
                                        class="rounded border-zinc-300 dark:border-zinc-700 text-zinc-900 focus:ring-zinc-900 dark:bg-zinc-800"
                                    >
                                </td>

                                <!-- Username & Password -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('users.show', $v->username) }}" class="font-mono font-bold text-zinc-900 dark:text-zinc-100 hover:underline">
                                            {{ $v->username }}
                                        </a>
                                        <button 
                                            type="button" 
                                            @click="copyText('{{ $v->username }}', 'Kode Voucher')" 
                                            class="p-1 text-zinc-400 hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-200"
                                            title="Salin Kode"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                    @if($v->password && $v->password !== $v->username)
                                        <div class="text-[10px] text-zinc-500 dark:text-zinc-400 font-mono">Pass: {{ $v->password }}</div>
                                    @endif
                                </td>

                                <!-- Profile & Price -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $v->profile?->name ?? 'Default' }}</span>
                                        @php
                                            $cat = $v->profile?->category ?? 'voucher';
                                        @endphp
                                        @if($cat === 'bulanan')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                                Bulanan
                                            </span>
                                        @elseif($cat === 'pribadi')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-semibold bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-300 dark:border-purple-800">
                                                Pribadi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                Voucher
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                                        {{ \App\Support\FormatHelper::formatRupiah($v->profile?->selling_price ?? 3000) }}
                                    </div>
                                </td>

                                <!-- Time Limit & Remaining -->
                                <td class="py-3 px-4 font-mono text-zinc-600 dark:text-zinc-400">
                                    <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                        {{ $v->profile?->validity ?? ($v->uptime_limit ?: '-') }}
                                    </div>
                                    @if($isOnline && !empty($activeUserMap[$v->username]))
                                        @php
                                            $liveSes = $activeUserMap[$v->username];
                                            $liveProg = \App\Support\FormatHelper::getUptimeProgress(is_array($liveSes) ? ($liveSes['uptime'] ?? '0s') : '0s', $v->uptime_limit ?: ($v->profile?->validity ?: (is_array($liveSes) ? ($liveSes['limit-uptime'] ?? null) : null)), is_array($liveSes) ? ($liveSes['session-time-left'] ?? null) : null);
                                        @endphp
                                        @if($liveProg['has_limit'] && $liveProg['remaining_formatted'])
                                            <div class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 mt-0.5 whitespace-nowrap">
                                                ⏳ Sisa: {{ $liveProg['remaining_formatted'] }} ({{ $liveProg['remaining_percent'] }}%)
                                            </div>
                                            <div class="w-24 bg-zinc-200 dark:bg-zinc-700 rounded-full h-1 mt-1 overflow-hidden">
                                                <div class="bg-amber-500 h-1 rounded-full" style="width: {{ $liveProg['remaining_percent'] }}%"></div>
                                            </div>
                                        @elseif($isOnline)
                                            <div class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-0.5 whitespace-nowrap">
                                                Aktif: {{ $liveProg['used_formatted'] }}
                                            </div>
                                        @endif
                                    @endif
                                </td>

                                <!-- Batch Tag / Comment -->
                                <td class="py-3 px-4">
                                    <span class="text-zinc-500 dark:text-zinc-400 text-xs truncate max-w-[200px] block" title="{{ $v->comment }}">
                                        {{ $v->comment ?: '-' }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="py-3 px-4">
                                    @if($isOnline)
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                                            </span>
                                            <button 
                                                type="button" 
                                                @click="toggleStatus({{ $v->id }})" 
                                                class="text-[10px] text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 hover:underline cursor-pointer"
                                                title="Klik untuk non-aktifkan"
                                            >
                                                {{ $v->is_active ? 'Aktif' : 'In-Active' }}
                                            </button>
                                        </div>
                                    @else
                                        <button 
                                            type="button" 
                                            @click="toggleStatus({{ $v->id }})" 
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium transition-colors cursor-pointer {{ $v->is_active ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 border border-zinc-300 dark:border-zinc-600 hover:bg-zinc-300' }}"
                                            title="{{ $v->is_active ? 'Klik untuk non-aktifkan' : 'Klik untuk aktifkan' }}"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full {{ $v->is_active ? 'bg-emerald-500' : 'bg-zinc-400 dark:bg-zinc-500' }}"></span>
                                            <span>{{ $v->is_active ? 'Ready' : 'In-Active' }}</span>
                                        </button>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <button 
                                            type="button" 
                                            @click="openPrintPreview('58mm', [{{ $v->id }}])" 
                                            class="px-2.5 py-1 text-xs font-medium rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors border border-zinc-200 dark:border-zinc-700 flex items-center gap-1 cursor-pointer"
                                            title="Pratinjau & Cetak Voucher"
                                        >
                                            <svg class="w-3.5 h-3.5 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            <span>Cetak</span>
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="openWhatsAppModal({{ json_encode($v) }})" 
                                            class="px-2 py-1 text-xs font-medium rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-1 cursor-pointer"
                                            title="Kirim detail voucher ke WhatsApp"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.087-.177.181-.076.355.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.087.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                                            </svg>
                                            <span>WA</span>
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="editUser({{ json_encode($v) }})" 
                                            class="px-2 py-1 text-xs font-medium rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors border border-zinc-200 dark:border-zinc-700 cursor-pointer"
                                            title="Edit Akun User"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="deleteSingleUser({{ $v->id }}, '{{ $v->username }}')" 
                                            class="px-2 py-1 text-xs font-medium rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors border border-rose-200 dark:border-rose-900/50 cursor-pointer"
                                            title="Hapus User / Voucher"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-zinc-500 dark:text-zinc-400 text-xs">
                                    Tidak ada voucher yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($vouchers->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-800">
                    {{ $vouchers->links() }}
                </div>
            @endif
        </div>

        <!-- FLOATING MULTI-ACTION PRINT BAR (when 1 or more vouchers are selected) -->
        <div 
            x-show="selectedVouchers.length > 0" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-6"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-6"
            class="fixed bottom-20 lg:bottom-8 inset-x-4 sm:inset-x-auto sm:left-1/2 sm:-translate-x-1/2 z-40 bg-zinc-900/95 dark:bg-zinc-800/95 backdrop-blur-md text-white dark:text-zinc-100 px-4 py-3 rounded-2xl shadow-2xl border border-zinc-700 dark:border-zinc-600 flex flex-wrap items-center justify-between gap-3 text-xs max-w-xl w-full"
            style="display: none;"
        >
            <div class="flex items-center gap-2 font-bold text-zinc-100 dark:text-zinc-100">
                <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-md font-mono" x-text="formatNumber(selectedVouchers.length)"></span>
                <span>Voucher Terpilih</span>
            </div>

            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    @click="openPrintPreview('58mm')" 
                    class="px-3.5 py-1.5 font-semibold rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-100 dark:bg-zinc-700/80 dark:hover:bg-zinc-700 dark:text-zinc-100 border border-zinc-700 dark:border-zinc-600 transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5 text-zinc-400 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Voucher</span>
                </button>
                <button 
                    type="button" 
                    @click="openWhatsAppModal()" 
                    class="px-3 py-1.5 font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer"
                    title="Bagikan voucher terpilih ke WhatsApp"
                >
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.087-.177.181-.076.355.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.087.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                    </svg>
                    <span>WA</span>
                </button>
                <button 
                    type="button" 
                    @click="deleteSelected()" 
                    :disabled="isBatchDeleting"
                    class="px-3 py-1.5 font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white transition-colors flex items-center gap-1.5 shadow-xs"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span x-text="isBatchDeleting ? 'Menghapus...' : 'Hapus'"></span>
                </button>
                <button 
                    type="button" 
                    @click="selectedVouchers = []" 
                    class="p-1.5 hover:bg-zinc-800 dark:hover:bg-zinc-700 rounded-lg text-zinc-400 hover:text-zinc-100 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors"
                    title="Batal Seleksi"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
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
                    <input type="checkbox" id="userIsActive" x-model="userForm.is_active" class="rounded border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 text-zinc-900 focus:ring-zinc-500">
                    <label for="userIsActive" class="font-medium text-zinc-700 dark:text-zinc-300 cursor-pointer">User Aktif (Bisa Login Hotspot)</label>
                </div>
                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="$dispatch('close-modal', 'hotspot-user-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 cursor-pointer">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-semibold cursor-pointer" x-text="isEditing ? 'Perbarui User' : 'Simpan User'"></button>
                </div>
            </form>
        </x-modal>

        <!-- Print Preview Pop-up Modal -->
        <x-modal name="print-preview-modal" title="Pratinjau Cetak Voucher" maxWidth="4xl">
            <div class="space-y-4">
                <!-- Action & Format Switcher Bar -->
                <div class="flex flex-wrap items-center justify-between gap-3 p-2.5 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200/80 dark:border-zinc-700/80 text-xs">
                    <!-- Format Tabs -->
                    <div class="flex items-center gap-1.5 p-1 bg-zinc-200/70 dark:bg-zinc-900/70 rounded-lg">
                        <button 
                            type="button" 
                            @click="switchPreviewFormat('58mm')" 
                            :class="previewFormat === '58mm' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200'"
                            class="px-3 py-1.5 rounded-md transition-colors cursor-pointer"
                        >
                            Thermal 58mm
                        </button>
                        <button 
                            type="button" 
                            @click="switchPreviewFormat('80mm')" 
                            :class="previewFormat === '80mm' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200'"
                            class="px-3 py-1.5 rounded-md transition-colors cursor-pointer"
                        >
                            Thermal 80mm
                        </button>
                        <button 
                            type="button" 
                            @click="switchPreviewFormat('grid')" 
                            :class="previewFormat === 'grid' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200'"
                            class="px-3 py-1.5 rounded-md transition-colors cursor-pointer flex items-center gap-1.5"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Grid A4</span>
                        </button>
                    </div>

                    <!-- Direct Actions -->
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            @click="openWhatsAppModalFromPreview()" 
                            class="px-3 py-1.5 font-medium rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-1.5 shadow-xs cursor-pointer"
                            title="Bagikan voucher ke WhatsApp"
                        >
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.087-.177.181-.076.355.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.087.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                            </svg>
                            <span>Share WA</span>
                        </button>

                        <button 
                            type="button" 
                            @click="printIframe()" 
                            class="px-3.5 py-1.5 font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Cetak Sekarang</span>
                        </button>

                        <button 
                            type="button" 
                            @click="openInNewTab()" 
                            class="p-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Buka di tab browser baru"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Iframe Preview Viewport -->
                <div class="relative w-full h-[62vh] rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-800 bg-zinc-100 dark:bg-zinc-950">
                    <div x-show="iframeLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xs z-10">
                        <div class="w-7 h-7 border-2 border-zinc-300 dark:border-zinc-700 border-t-zinc-900 dark:border-t-zinc-100 rounded-full animate-spin"></div>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 font-medium">Memuat pratinjau cetak...</span>
                    </div>

                    <iframe 
                        id="printPreviewIframe" 
                        :src="previewUrl" 
                        @load="iframeLoading = false" 
                        class="w-full h-full bg-white text-zinc-900"
                        title="Voucher Print Preview"
                    ></iframe>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <span>Tips: Anda bisa mencetak langsung dari pop-up ini tanpa perlu membuka tab baru.</span>
                    <button 
                        type="button" 
                        @click="$dispatch('close-modal', 'print-preview-modal')" 
                        class="px-3.5 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </x-modal>

        <!-- WhatsApp Share Pop-up Modal -->
        <x-modal name="whatsapp-share-modal" title="Kirim Voucher via WhatsApp" maxWidth="lg">
            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Nomor WhatsApp Pelanggan (Opsional)
                    </label>
                    <input 
                        type="tel" 
                        x-model="whatsappPhone" 
                        placeholder="Contoh: 08123456789 atau 628123456789" 
                        class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono focus:ring-2 focus:ring-emerald-500"
                    >
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">Kosongkan jika ingin memilih kontak langsung di aplikasi WhatsApp.</p>
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Pratinjau Pesan WhatsApp
                    </label>
                    <textarea 
                        x-model="whatsappMessage" 
                        rows="9" 
                        class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono text-[11px] leading-relaxed focus:ring-2 focus:ring-emerald-500"
                    ></textarea>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-zinc-100 dark:border-zinc-800">
                    <button 
                        type="button" 
                        @click="copyWhatsAppText()" 
                        class="px-3 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-medium transition-colors flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span>Salin Pesan</span>
                    </button>

                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            @click="$dispatch('close-modal', 'whatsapp-share-modal')" 
                            class="px-3 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="button" 
                            @click="sendWhatsApp()" 
                            class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.299.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.087-.177.181-.076.355.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.087.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/>
                            </svg>
                            <span>Buka WhatsApp</span>
                        </button>
                    </div>
                </div>
            </div>
        </x-modal>
    </div>

    @push('scripts')
    <script>
        function vouchersManager() {
            return {
                selectedVouchers: [],
                isBatchDeleting: false,
                isEditing: false,
                profiles: @json($profiles ?? []),
                routerSetting: @json($routerSetting ?? null),
                previewFormat: '58mm',
                previewIds: [],
                previewUrl: '',
                iframeLoading: true,
                whatsappPhone: '',
                whatsappMessage: '',
                activeVoucherForShare: null,
                userForm: { id: null, username: '', password: '', profile_id: '', uptime_limit: '3h', comment: '', is_active: true },
                formatNumber(val) {
                    if (window.formatNumber) return window.formatNumber(val);
                    return val;
                },
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
                            if (window.showToast) {
                                window.showToast(`User ${this.userForm.username} berhasil disimpan!`, 'success');
                            } else {
                                alert(`User ${this.userForm.username} berhasil disimpan!`);
                            }
                            this.$dispatch('close-modal', 'hotspot-user-modal');
                            setTimeout(() => window.location.reload(), 400);
                        } else {
                            if (window.showToast) {
                                window.showToast(data.message || 'Gagal menyimpan user', 'error');
                            } else {
                                alert(data.message || 'Gagal menyimpan user');
                            }
                        }
                    })
                    .catch(() => {
                        if (window.showToast) {
                            window.showToast('Terjadi kesalahan koneksi', 'error');
                        } else {
                            alert('Terjadi kesalahan koneksi');
                        }
                    });
                },
                deleteSingleUser(id, username) {
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
                                if (window.showToast) {
                                    window.showToast(`User ${username} berhasil dihapus`, 'info');
                                } else {
                                    alert(`User ${username} berhasil dihapus`);
                                }
                                setTimeout(() => window.location.reload(), 400);
                            } else {
                                if (window.showToast) {
                                    window.showToast(data.message || 'Gagal menghapus user', 'error');
                                } else {
                                    alert(data.message || 'Gagal menghapus user');
                                }
                            }
                        })
                        .catch(() => {
                            if (window.showToast) {
                                window.showToast('Gagal menghapus user', 'error');
                            } else {
                                alert('Gagal menghapus user');
                            }
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
                            if (window.showToast) {
                                window.showToast(data.message, 'success');
                            } else {
                                alert(data.message);
                            }
                            setTimeout(() => window.location.reload(), 400);
                        } else {
                            if (window.showToast) {
                                window.showToast(data.message || 'Gagal mengubah status', 'error');
                            } else {
                                alert(data.message || 'Gagal mengubah status');
                            }
                        }
                    })
                    .catch(() => {
                        if (window.showToast) {
                            window.showToast('Gagal terhubung ke server', 'error');
                        } else {
                            alert('Gagal terhubung ke server');
                        }
                    });
                },
                toggleSelect(id) {
                    const idx = this.selectedVouchers.indexOf(id);
                    if (idx > -1) {
                        this.selectedVouchers.splice(idx, 1);
                    } else {
                        this.selectedVouchers.push(id);
                    }
                },
                isSelected(id) {
                    return this.selectedVouchers.includes(id);
                },
                isAllSelected(allIds) {
                    if (!allIds || allIds.length === 0) return false;
                    return allIds.every(id => this.selectedVouchers.includes(id));
                },
                toggleSelectAll(allIds) {
                    if (this.isAllSelected(allIds)) {
                        this.selectedVouchers = this.selectedVouchers.filter(id => !allIds.includes(id));
                    } else {
                        const toAdd = allIds.filter(id => !this.selectedVouchers.includes(id));
                        this.selectedVouchers = [...this.selectedVouchers, ...toAdd];
                    }
                },
                openPrintPreview(format = '58mm', ids = null) {
                    this.previewFormat = format || '58mm';
                    if (ids && Array.isArray(ids)) {
                        this.previewIds = ids;
                    } else if (this.selectedVouchers.length > 0) {
                        this.previewIds = [...this.selectedVouchers];
                    } else {
                        this.previewIds = [];
                    }
                    this.updatePreviewUrl();
                    this.iframeLoading = true;
                    this.$dispatch('open-modal', 'print-preview-modal');
                },
                switchPreviewFormat(format) {
                    this.previewFormat = format;
                    this.updatePreviewUrl();
                    this.iframeLoading = true;
                },
                updatePreviewUrl() {
                    let baseUrl = '';
                    if (this.previewFormat === '58mm') {
                        baseUrl = '{{ route("vouchers.print.58mm") }}';
                    } else if (this.previewFormat === '80mm') {
                        baseUrl = '{{ route("vouchers.print.80mm") }}';
                    } else {
                        baseUrl = '{{ route("vouchers.print.grid") }}';
                    }

                    const params = new URLSearchParams();
                    if (this.previewIds && this.previewIds.length > 0) {
                        params.set('ids', this.previewIds.join(','));
                    } else {
                        const currentParams = new URLSearchParams(window.location.search);
                        for (const [key, val] of currentParams.entries()) {
                            params.set(key, val);
                        }
                    }
                    params.set('embedded', '1');
                    this.previewUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');
                },
                printIframe() {
                    const iframe = document.getElementById('printPreviewIframe');
                    if (!iframe) return;
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch (e) {
                        window.open(this.previewUrl, '_blank');
                    }
                },
                openInNewTab() {
                    if (this.previewUrl) {
                        try {
                            const url = new URL(this.previewUrl, window.location.origin);
                            url.searchParams.delete('embedded');
                            window.open(url.toString(), '_blank');
                        } catch (e) {
                            window.open(this.previewUrl, '_blank');
                        }
                    }
                },
                generateWhatsAppText(voucher) {
                    const brand = (this.routerSetting?.hotspot_name || this.routerSetting?.app_name || 'Hotspot WiFi').toUpperCase();
                    const loginUrl = this.routerSetting?.login_url || 'http://hotspot.net';
                    const profileName = voucher?.profile?.name || 'Standard';
                    const price = voucher?.profile?.selling_price ? 'Rp ' + (window.formatNumber ? window.formatNumber(voucher.profile.selling_price) : voucher.profile.selling_price) : 'Gratis';
                    const validity = voucher?.uptime_limit || voucher?.profile?.validity || 'Sesuai Kuota';
                    const username = voucher?.username || '-';
                    const password = voucher?.password || voucher?.username || '-';
                    const kontak = this.routerSetting?.contact_phone || '';
                    const footer = this.routerSetting?.footer_text || 'Terima kasih telah menggunakan layanan kami!';

                    const isSame = (username === password || !voucher?.password);
                    const credentialLine = isSame 
                        ? `• *Kode Login:* \`${username}\`` 
                        : `• *Username:* \`${username}\`\n• *Password:* \`${password}\``;

                    const defaultTpl = `🌐 *VOUCHER INTERNET - {brand}*\n` +
                                       `━━━━━━━━━━━━━━━━━━━━\n` +
                                       `🏷️ *Paket:* {paket}\n` +
                                       `⏱️ *Masa Aktif:* {durasi}\n` +
                                       `💰 *Harga:* {harga}\n\n` +
                                       `🔑 *KREDENSIAL LOGIN*\n` +
                                       `{kredensial}\n\n` +
                                       `📱 *CARA PENGGUNAAN*\n` +
                                       `1. Hubungkan WiFi ke *{brand}*\n` +
                                       `2. Buka browser: {login_url}\n` +
                                       `3. Masukkan ${isSame ? 'Kode Login' : 'Username & Password'} di atas\n` +
                                       `4. Klik tombol Login\n\n` +
                                       `_Terima kasih telah menggunakan layanan kami!_`;

                    let tpl = this.routerSetting?.wa_template || defaultTpl;
                    return tpl
                        .replace(/{brand}/g, brand)
                        .replace(/{paket}/g, profileName)
                        .replace(/{durasi}/g, validity)
                        .replace(/{harga}/g, price)
                        .replace(/{kredensial}/g, credentialLine)
                        .replace(/{username}/g, username)
                        .replace(/{password}/g, password)
                        .replace(/{login_url}/g, loginUrl)
                        .replace(/{kontak}/g, kontak)
                        .replace(/{footer}/g, footer);
                },
                generateBatchWhatsAppText(vouchers) {
                    const brand = (this.routerSetting?.hotspot_name || this.routerSetting?.app_name || 'Hotspot WiFi').toUpperCase();
                    const loginUrl = this.routerSetting?.login_url || 'http://hotspot.net';

                    let msg = `🌐 *DAFTAR VOUCHER - ${brand}*\n` +
                              `━━━━━━━━━━━━━━━━━━━━\n` +
                              `Total: ${vouchers.length} Voucher\n\n`;

                    vouchers.slice(0, 15).forEach((v, idx) => {
                        const prof = v.profile?.name || 'Paket';
                        const isSame = (v.username === v.password || !v.password);
                        if (isSame) {
                            msg += `${idx + 1}. *Kode Login:* \`${v.username}\` (${prof})\n`;
                        } else {
                            msg += `${idx + 1}. *User:* \`${v.username}\` | *Pass:* \`${v.password}\` (${prof})\n`;
                        }
                    });

                    if (vouchers.length > 15) {
                        msg += `...dan ${vouchers.length - 15} voucher lainnya.\n`;
                    }

                    msg += `\n📱 *Login:* ${loginUrl}\n` +
                           `━━━━━━━━━━━━━━━━━━━━`;
                    return msg;
                },
                openWhatsAppModal(voucher = null) {
                    this.activeVoucherForShare = voucher;
                    this.whatsappPhone = '';
                    if (voucher) {
                        this.whatsappMessage = this.generateWhatsAppText(voucher);
                    } else if (this.selectedVouchers.length > 0) {
                        const currentVouchers = @json($vouchers->items() ?? []);
                        const matched = currentVouchers.filter(v => this.selectedVouchers.includes(v.id));
                        this.whatsappMessage = this.generateBatchWhatsAppText(matched.length > 0 ? matched : this.selectedVouchers.map(id => ({ username: 'ID#' + id })));
                    } else {
                        this.whatsappMessage = this.generateWhatsAppText(null);
                    }
                    this.$dispatch('open-modal', 'whatsapp-share-modal');
                },
                openWhatsAppModalFromPreview() {
                    if (this.previewIds && this.previewIds.length === 1) {
                        const currentVouchers = @json($vouchers->items() ?? []);
                        const v = currentVouchers.find(item => item.id == this.previewIds[0]);
                        this.openWhatsAppModal(v || { id: this.previewIds[0], username: 'Voucher' });
                    } else {
                        this.openWhatsAppModal(null);
                    }
                },
                sendWhatsApp() {
                    let phone = (this.whatsappPhone || '').replace(/[^0-9]/g, '');
                    if (phone.startsWith('0')) {
                        phone = '62' + phone.substring(1);
                    }
                    const text = encodeURIComponent(this.whatsappMessage || '');
                    const url = phone ? `https://api.whatsapp.com/send?phone=${phone}&text=${text}` : `https://api.whatsapp.com/send?text=${text}`;
                    window.open(url, '_blank');
                },
                copyWhatsAppText() {
                    if (!this.whatsappMessage) return;
                    this.copyText(this.whatsappMessage, 'Pesan WhatsApp');
                },
                printSelectedGrid() {
                    this.openPrintPreview('grid');
                },
                printSelected58mm() {
                    this.openPrintPreview('58mm');
                },
                printSelected80mm() {
                    this.openPrintPreview('80mm');
                },
                deleteSelected() {
                    if (this.selectedVouchers.length === 0 || this.isBatchDeleting) return;
                    if (!confirm(`Apakah Anda yakin ingin menghapus ${window.formatNumber(this.selectedVouchers.length)} voucher terpilih dari database dan MikroTik?`)) return;

                    this.isBatchDeleting = true;
                    fetch('{{ route("vouchers.batch-delete") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: this.selectedVouchers })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isBatchDeleting = false;
                        if (data.success) {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert(data.message || 'Gagal menghapus voucher.');
                        }
                    })
                    .catch(() => {
                        this.isBatchDeleting = false;
                        alert('Terjadi kesalahan koneksi saat menghapus voucher.');
                    });
                },
                copyText(text, label) {
                    if (!text) return;
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text).then(() => {
                            if (window.dispatchEvent) {
                                window.dispatchEvent(new CustomEvent('toast', { detail: { message: `${label} disalin!` } }));
                            }
                        }).catch(() => {
                            alert(`${label} disalin: ${text}`);
                        });
                    } else {
                        const input = document.createElement('input');
                        input.value = text;
                        document.body.appendChild(input);
                        input.select();
                        document.execCommand('copy');
                        document.body.removeChild(input);
                        alert(`${label} disalin: ${text}`);
                    }
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
