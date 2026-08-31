<x-layouts.app>
    <x-slot:header>Rekap Penjualan & Omzet Voucher</x-slot:header>

    <div class="space-y-6" x-data="voucherRevenueLedger()">
        <!-- Header & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Rekap Penjualan & Omzet Voucher</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Pendapatan riil voucher hotspot yang telah diaktivasi dan digunakan oleh pelanggan</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vouchers.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    <span>Stok & Cetak Voucher</span>
                </a>
            </div>
        </div>

        <!-- Metric Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Voucher Terpakai ({{ $metrics['period'] ?? 'Bulan Ini' }})</span>
                <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">{{ number_format($metrics['total_count'] ?? 0, 0, ',', '.') }}</div>
                <div class="mt-2 text-xs text-zinc-500">Total voucher aktif tercatat</div>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Total Omzet Penjualan</span>
                <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono">Rp {{ number_format($metrics['total_revenue'] ?? 0, 0, ',', '.') }}</div>
                <div class="mt-2 text-xs text-zinc-500">Gross revenue dari voucher</div>
            </div>
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                <span class="text-xs font-medium text-zinc-400 uppercase">Total Laba Bersih (Profit)</span>
                <div class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($metrics['total_profit'] ?? 0, 0, ',', '.') }}</div>
                <div class="mt-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium">Laba bersih masuk ke kas</div>
            </div>
        </div>

        <!-- Empty State -->
        <template x-if="records.length === 0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Belum Ada Voucher Terpakai</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Pendapatan omzet voucher akan otomatis tercatat di sini saat voucher pertama kali diaktivasi dan digunakan oleh pelanggan.</p>
            </div>
        </template>

        <!-- Table Card -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs" x-show="records.length > 0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3.5 px-4">Kode Voucher</th>
                            <th class="py-3.5 px-4">Paket / Profile</th>
                            <th class="py-3.5 px-4">Waktu Pertama Dipakai</th>
                            <th class="py-3.5 px-4">Harga Pokok</th>
                            <th class="py-3.5 px-4">Harga Jual</th>
                            <th class="py-3.5 px-4">Laba Bersih</th>
                            <th class="py-3.5 px-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <template x-for="r in records" :key="r.id">
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="py-3.5 px-4 font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="r.username || r.code"></td>
                                <td class="py-3.5 px-4 font-medium" x-text="r.profile_name || r.profile"></td>
                                <td class="py-3.5 px-4 text-zinc-500 font-mono text-[11px]" x-text="r.activated_at"></td>
                                <td class="py-3.5 px-4 font-mono text-zinc-400" x-text="formatRupiah(r.cost_price || r.cost || 0)"></td>
                                <td class="py-3.5 px-4 font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="formatRupiah(r.selling_price || r.price || 0)"></td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(r.profit || 0)"></td>
                                <td class="py-3.5 px-4 text-right">
                                    <x-badge type="paid">Completed</x-badge>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            @if($sales instanceof \Illuminate\Pagination\LengthAwarePaginator && $sales->hasPages())
                <div class="p-4 border-t border-zinc-100 dark:border-zinc-800">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function voucherRevenueLedger() {
            return {
                records: @json($sales?->items() ?? $sales ?? []),
                formatRupiah(num) {
                    return window.formatRupiah(num || 0);
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
