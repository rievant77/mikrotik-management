<x-layouts.app>
    <x-slot:header>POS Kasir Overview</x-slot:header>

    <div class="space-y-6" x-data="posOverview()">
        <!-- Header & Quick Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Kasir & Rekonsiliasi Pendapatan</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Pencatatan pendapatan berbasis voucher terpakai & tagihan bulanan manual</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pos.monthly') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Input Bayar Bulanan</span>
                </a>
                <a href="{{ route('pos.shifts') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Kelola Shift Kasir</span>
                </a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Omzet Voucher Terpakai -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Omzet Voucher (Terpakai)</span>
                <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">Rp {{ number_format($overview['voucher_revenue'] ?? 0, 0, ',', '.') }}</div>
                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400 flex justify-between">
                    <span>{{ $overview['vouchers_used_count'] ?? 0 }} Voucher Aktif</span>
                    <a href="{{ route('pos.vouchers') }}" class="text-zinc-700 dark:text-zinc-300 hover:underline">Rincian &rarr;</a>
                </div>
            </div>

            <!-- 2. Tagihan Bulanan Terbayar -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Bulanan Masuk</span>
                <div class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($overview['monthly_paid'] ?? 0, 0, ',', '.') }}</div>
                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400 flex justify-between">
                    <span>Target: Rp {{ number_format($overview['monthly_expected'] ?? 0, 0, ',', '.') }}</span>
                    <a href="{{ route('pos.monthly') }}" class="text-zinc-700 dark:text-zinc-300 hover:underline">Detail &rarr;</a>
                </div>
            </div>

            <!-- 3. Tunggakan / UNPAID Bulanan -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Tunggakan Belum Bayar</span>
                <div class="mt-2 text-2xl font-bold text-rose-600 dark:text-rose-400 font-mono">Rp {{ number_format($overview['monthly_unpaid'] ?? 0, 0, ',', '.') }}</div>
                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400 flex justify-between">
                    <span>Belum Lunas</span>
                    <a href="{{ route('pos.monthly') }}" class="text-rose-600 hover:underline">Tagih &rarr;</a>
                </div>
            </div>

            <!-- 4. Kas Fisik Laci Shift Aktif -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Kas Laci Shift {{ $overview['active_shift'] ? ('#' . $overview['active_shift']->id) : 'Tutup' }}</span>
                <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">Rp {{ number_format($overview['active_shift']->expected_cash ?? 0, 0, ',', '.') }}</div>
                <div class="mt-2 text-xs text-zinc-500 dark:text-zinc-400 flex justify-between">
                    <span>Modal: Rp {{ number_format($overview['active_shift']->opening_cash ?? 0, 0, ',', '.') }}</span>
                    <a href="{{ route('pos.shifts') }}" class="text-zinc-700 dark:text-zinc-300 hover:underline">Shift &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Split Grid: Voucher Usage Today & Recent Monthly Payments -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left: Voucher Terpakai Hari Ini -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Aktivasi Voucher Terbaru</h3>
                        <p class="text-[11px] text-zinc-400">Omzet dihitung saat voucher pertama kali login</p>
                    </div>
                    <a href="{{ route('pos.vouchers') }}" class="text-xs text-zinc-600 dark:text-zinc-300 hover:underline">Semua &rarr;</a>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/60 mt-2">
                    @forelse($recentVouchers as $v)
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100">{{ $v->username }}</div>
                                <div class="text-[10px] text-zinc-400">{{ $v->profile_name }} • Diaktifkan {{ $v->activated_at }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($v->selling_price, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-emerald-600">Profit: Rp {{ number_format($v->profit, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-zinc-400">
                            Belum ada voucher yang diaktivasi.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right: Pembayaran Bulanan Terbaru -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Pembayaran Bulanan Masuk</h3>
                        <p class="text-[11px] text-zinc-400">Transaksi kasir & transfer bank</p>
                    </div>
                    <a href="{{ route('pos.monthly') }}" class="text-xs text-zinc-600 dark:text-zinc-300 hover:underline">Semua &rarr;</a>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/60 mt-2">
                    @forelse($recentPayments as $p)
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $p->customer->name ?? 'Pelanggan' }}</div>
                                <div class="text-[10px] text-zinc-400">Periode: {{ $p->billing_month }} • {{ ucfirst($p->payment_method) }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($p->amount_paid, 0, ',', '.') }}</div>
                                <x-badge type="paid">Lunas</x-badge>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-zinc-400">
                            Belum ada pembayaran bulanan yang dicatat.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function posOverview() {
            return {};
        }
    </script>
    @endpush
</x-layouts.app>
