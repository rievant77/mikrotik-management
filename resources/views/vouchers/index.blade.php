<x-layouts.app>
    <x-slot:header>Daftar & Cetak Voucher</x-slot:header>

    <div class="space-y-6" x-data="vouchersManager()">
        <!-- Header Page -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight flex items-center gap-2.5">
                    <span>🎟️</span>
                    <span>Daftar & Cetak Voucher</span>
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Kelola inventory voucher hasil generate, filter batch, dan cetak 1 atau banyak voucher sekaligus.
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a 
                    href="{{ route('vouchers.print.grid', request()->query()) }}" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs"
                >
                    <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Grid Terfilter</span>
                </a>
                <a 
                    href="{{ route('hotspot.generate') }}" 
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-xl bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 shadow-xs transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Generate Voucher Baru</span>
                </a>
            </div>
        </div>

        <!-- 4 KPI Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Total Voucher -->
            <div class="p-4 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total Voucher</span>
                    <span class="text-lg">🎟️</span>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-extrabold text-zinc-900 dark:text-zinc-100 font-mono">
                    {{ number_format($stats['total']) }}
                </div>
                <span class="text-[11px] text-zinc-400 mt-1 block">Tersimpan di sistem</span>
            </div>

            <!-- Sedang Online -->
            <div class="p-4 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Sedang Online</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono">
                    {{ number_format($stats['online']) }}
                </div>
                <span class="text-[11px] text-zinc-400 mt-1 block">Sesi aktif di router</span>
            </div>

            <!-- Total Batch -->
            <div class="p-4 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Batch Generate</span>
                    <span class="text-lg">📦</span>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-extrabold text-zinc-900 dark:text-zinc-100 font-mono">
                    {{ number_format($stats['batches']) }}
                </div>
                <span class="text-[11px] text-zinc-400 mt-1 block">Kelompok batch generate</span>
            </div>

            <!-- Estimasi Inventory -->
            <div class="p-4 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Nilai Inventory</span>
                    <span class="text-lg">💰</span>
                </div>
                <div class="mt-2 text-xl sm:text-2xl font-extrabold text-sky-600 dark:text-sky-400 font-mono">
                    {{ \App\Support\FormatHelper::formatRupiah($stats['inventory_value']) }}
                </div>
                <span class="text-[11px] text-zinc-400 mt-1 block">Potensi nominal rupiah</span>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-4 shadow-xs">
            <form method="GET" action="{{ route('vouchers.index') }}" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Search Input -->
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 mb-1">Cari Voucher</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}" 
                                placeholder="Kode username, pass, batch..." 
                                class="w-full pl-8 pr-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-400"
                            >
                            <svg class="w-4 h-4 text-zinc-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
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
                                <option value="{{ $p->id }}" {{ request('profile_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} ({{ \App\Support\FormatHelper::formatRupiah($p->selling_price) }})
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
                            <option value="online" {{ request('status') === 'online' ? 'selected' : '' }}>🟢 Sedang Online</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>⚪ In-Active / Non-Aktif</option>
                        </select>
                    </div>
                </div>

                @if(request()->hasAny(['search', 'profile_id', 'batch']) || (request('status') && request('status') !== 'active'))
                    <div class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800 text-xs">
                        <span class="text-zinc-500 dark:text-zinc-400">Filter aktif diterapkan.</span>
                        <a href="{{ route('vouchers.index') }}" class="text-xs font-semibold text-red-600 dark:text-red-400 hover:underline">
                            Reset Filter ✕
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
                <span class="font-bold text-zinc-800 dark:text-zinc-200">Pilih Semua di Halaman Ini ({{ $vouchers->count() }} voucher)</span>
            </label>

            <div class="flex items-center gap-2">
                <span class="text-zinc-500 dark:text-zinc-400" x-text="selectedVouchers.length + ' terpilih'"></span>
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
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-extrabold text-base text-zinc-900 dark:text-zinc-100 tracking-wider">
                                        {{ $v->username }}
                                    </span>
                                    <button 
                                        type="button" 
                                        @click="copyText('{{ $v->username }}', 'Kode Voucher')" 
                                        class="p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200"
                                        title="Salin Kode"
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
                                    ⚪ In-Active
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Meta Details -->
                    <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-[10px] text-zinc-400 block">Paket & Harga:</span>
                            <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $v->profile?->name ?? 'Default' }}</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-mono font-semibold ml-1">
                                ({{ \App\Support\FormatHelper::formatRupiah($v->profile?->selling_price ?? 3000) }})
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-zinc-400 block">Masa Aktif / Limit:</span>
                            <span class="font-mono text-zinc-700 dark:text-zinc-300">{{ $v->profile?->validity ?? ($v->uptime_limit ?: '-') }}</span>
                            @if($isOnline && !empty($activeUserMap[$v->username]))
                                @php
                                    $liveSes = $activeUserMap[$v->username];
                                    $liveProg = \App\Support\FormatHelper::getUptimeProgress(is_array($liveSes) ? ($liveSes['uptime'] ?? '0s') : '0s', $v->uptime_limit ?: ($v->profile?->validity ?: (is_array($liveSes) ? ($liveSes['limit-uptime'] ?? null) : null)), is_array($liveSes) ? ($liveSes['session-time-left'] ?? null) : null);
                                @endphp
                                @if($liveProg['has_limit'] && $liveProg['remaining_formatted'])
                                    <div class="text-[10px] font-mono text-amber-600 dark:text-amber-400 font-semibold mt-0.5">
                                        ⏳ Sisa: {{ $liveProg['remaining_formatted'] }} ({{ $liveProg['remaining_percent'] }}%)
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
                        <div class="mt-2 text-[10px] text-zinc-400 bg-zinc-100 dark:bg-zinc-800/50 px-2 py-1 rounded truncate">
                            🏷️ {{ $v->comment }}
                        </div>
                    @endif

                    <!-- Action Buttons per Voucher -->
                    <div class="mt-3 pt-2.5 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <a 
                                href="{{ route('vouchers.print.58mm', ['ids' => $v->id]) }}" 
                                class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors"
                            >
                                🧾 58mm
                            </a>
                            <a 
                                href="{{ route('vouchers.print.80mm', ['ids' => $v->id]) }}" 
                                class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors"
                            >
                                🧾 80mm
                            </a>
                        </div>
                        <a 
                            href="{{ route('users.show', $v->username) }}" 
                            class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 transition-colors"
                        >
                            Detail User →
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 text-zinc-400 text-xs">
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
                                            class="p-1 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200"
                                            title="Salin Kode"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                    @if($v->password && $v->password !== $v->username)
                                        <div class="text-[10px] text-zinc-400 font-mono">Pass: {{ $v->password }}</div>
                                    @endif
                                </td>

                                <!-- Profile & Price -->
                                <td class="py-3 px-4">
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $v->profile?->name ?? 'Default' }}</span>
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
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Online
                                        </span>
                                    @elseif($v->is_active)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                            Ready
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-zinc-200 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 border border-zinc-300 dark:border-zinc-600">
                                            ⚪ In-Active
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a 
                                            href="{{ route('vouchers.print.58mm', ['ids' => $v->id]) }}" 
                                            class="px-2.5 py-1 text-xs font-medium rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors border border-zinc-200 dark:border-zinc-700"
                                            title="Cetak Struk 58mm"
                                        >
                                            58mm
                                        </a>
                                        <a 
                                            href="{{ route('vouchers.print.80mm', ['ids' => $v->id]) }}" 
                                            class="px-2.5 py-1 text-xs font-medium rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors border border-zinc-200 dark:border-zinc-700"
                                            title="Cetak Struk 80mm"
                                        >
                                            80mm
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-zinc-400 text-xs">
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
                <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-md font-mono" x-text="selectedVouchers.length"></span>
                <span>Voucher Terpilih</span>
            </div>

            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    @click="printSelectedGrid()" 
                    class="px-3 py-1.5 font-semibold rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-100 dark:bg-zinc-700/80 dark:hover:bg-zinc-700 dark:text-zinc-100 border border-zinc-700 dark:border-zinc-600 transition-colors flex items-center gap-1.5 shadow-xs"
                >
                    <span>🖨️</span> Grid A4
                </button>
                <button 
                    type="button" 
                    @click="printSelected58mm()" 
                    class="px-3 py-1.5 font-semibold rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-100 dark:bg-zinc-700/80 dark:hover:bg-zinc-700 dark:text-zinc-100 border border-zinc-700 dark:border-zinc-600 transition-colors flex items-center gap-1.5 shadow-xs"
                >
                    <span>🧾</span> 58mm
                </button>
                <button 
                    type="button" 
                    @click="printSelected80mm()" 
                    class="px-3 py-1.5 font-semibold rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-100 dark:bg-zinc-700/80 dark:hover:bg-zinc-700 dark:text-zinc-100 border border-zinc-700 dark:border-zinc-600 transition-colors flex items-center gap-1.5 shadow-xs"
                >
                    <span>🧾</span> 80mm
                </button>
                <button 
                    type="button" 
                    @click="deleteSelected()" 
                    :disabled="isBatchDeleting"
                    class="px-3 py-1.5 font-semibold rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors flex items-center gap-1.5 shadow-xs"
                >
                    <span>🗑️</span>
                    <span x-text="isBatchDeleting ? 'Menghapus...' : 'Hapus'"></span>
                </button>
                <button 
                    type="button" 
                    @click="selectedVouchers = []" 
                    class="p-1.5 hover:bg-zinc-800 dark:hover:bg-zinc-700 rounded-lg text-zinc-400 hover:text-zinc-100 dark:hover:text-zinc-100 transition-colors"
                    title="Batal Seleksi"
                >
                    ✕
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function vouchersManager() {
            return {
                selectedVouchers: [],
                isBatchDeleting: false,
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
                printSelectedGrid() {
                    if (this.selectedVouchers.length === 0) return;
                    window.open('{{ route("vouchers.print.grid") }}?ids=' + this.selectedVouchers.join(','), '_blank');
                },
                printSelected58mm() {
                    if (this.selectedVouchers.length === 0) return;
                    window.open('{{ route("vouchers.print.58mm") }}?ids=' + this.selectedVouchers.join(','), '_blank');
                },
                printSelected80mm() {
                    if (this.selectedVouchers.length === 0) return;
                    window.open('{{ route("vouchers.print.80mm") }}?ids=' + this.selectedVouchers.join(','), '_blank');
                },
                deleteSelected() {
                    if (this.selectedVouchers.length === 0 || this.isBatchDeleting) return;
                    if (!confirm(`Apakah Anda yakin ingin menghapus ${this.selectedVouchers.length} voucher terpilih dari database dan MikroTik?`)) return;

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
