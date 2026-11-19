<x-layouts.app>
    <x-slot:header>Tagihan & Pelanggan Bulanan</x-slot:header>

    <div class="space-y-6" x-data="monthlyBillingManager()">
        <!-- Header & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Tagihan & Pelanggan Bulanan</h1>
                <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-300 mt-1">Penerbitan tagihan bulanan per periode, pembayaran angsuran (cicilan), dan data pelanggan</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Ringkasan POS</span>
                </a>

                <a href="{{ route('pos.vouchers') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    <span>Rekap Voucher</span>
                </a>

                <a href="{{ route('pos.expenses') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Pengeluaran</span>
                </a>

                <button @click="openGenerateModal()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Generate Tagihan</span>
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-4 border-b border-zinc-200 dark:border-zinc-800 text-sm">
            <button 
                @click="activeTab = 'invoices'" 
                :class="activeTab === 'invoices' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 font-bold' : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200 font-medium'"
                class="flex items-center gap-2 px-3 py-3 border-b-2 -mb-px transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Daftar Tagihan</span>
                <span class="px-2 py-0.5 rounded-full text-xs border font-mono font-semibold" :class="activeTab === 'invoices' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700'" x-text="formatNumber(invoices.length)"></span>
            </button>
            <button 
                @click="activeTab = 'customers'" 
                :class="activeTab === 'customers' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 font-bold' : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200 font-medium'"
                class="flex items-center gap-2 px-3 py-3 border-b-2 -mb-px transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Data Master Pelanggan</span>
                <span class="px-2 py-0.5 rounded-full text-xs border font-mono font-semibold" :class="activeTab === 'customers' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700'" x-text="formatNumber(customers.length)"></span>
            </button>
        </div>

        <!-- TAB 1: INVOICES (TAGIHAN BULANAN) -->
        <div x-show="activeTab === 'invoices'" class="space-y-6">
            <!-- Period Selector & Status Filters -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-xl p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <label class="text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Periode Tagihan:</label>
                    <div class="flex items-center gap-2">
                        <input 
                            type="month" 
                            x-model="selectedMonth" 
                            @change="changeMonth()" 
                            class="px-3 py-1.5 text-xs font-bold font-mono rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                        <button type="button" @click="setMonthOffset(0)" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 transition-colors shadow-2xs">
                            Bulan Ini
                        </button>
                        <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-1 rounded-md border border-emerald-300 dark:border-emerald-800" x-text="metrics.period || 'Periode'"></span>
                    </div>
                </div>

                <!-- Status Filter Pills -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <button 
                        type="button" 
                        @click="statusFilter = 'all'" 
                        :class="statusFilter === 'all' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold border-zinc-900 dark:border-zinc-100 shadow-xs' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 font-semibold'" 
                        class="px-3 py-1.5 rounded-lg border transition-all shadow-2xs"
                    >
                        Semua (<span x-text="formatNumber(invoices.length)"></span>)
                    </button>
                    <button 
                        type="button" 
                        @click="statusFilter = 'unpaid'" 
                        :class="statusFilter === 'unpaid' ? 'bg-rose-600 text-white font-bold border-rose-600 shadow-xs' : 'bg-white dark:bg-zinc-900 text-rose-700 dark:text-rose-400 border-zinc-300 dark:border-zinc-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 font-semibold'" 
                        class="px-3 py-1.5 rounded-lg border transition-all shadow-2xs"
                    >
                        Belum Bayar (<span x-text="formatNumber(invoices.filter(x => x.status === 'unpaid').length)"></span>)
                    </button>
                    <button 
                        type="button" 
                        @click="statusFilter = 'partial'" 
                        :class="statusFilter === 'partial' ? 'bg-amber-600 text-white font-bold border-amber-600 shadow-xs' : 'bg-white dark:bg-zinc-900 text-amber-700 dark:text-amber-400 border-zinc-300 dark:border-zinc-700 hover:bg-amber-50 dark:hover:bg-amber-950/40 font-semibold'" 
                        class="px-3 py-1.5 rounded-lg border transition-all shadow-2xs"
                    >
                        Angsuran (<span x-text="formatNumber(invoices.filter(x => x.status === 'partial').length)"></span>)
                    </button>
                    <button 
                        type="button" 
                        @click="statusFilter = 'paid'" 
                        :class="statusFilter === 'paid' ? 'bg-emerald-600 text-white font-bold border-emerald-600 shadow-xs' : 'bg-white dark:bg-zinc-900 text-emerald-700 dark:text-emerald-400 border-zinc-300 dark:border-zinc-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 font-semibold'" 
                        class="px-3 py-1.5 rounded-lg border transition-all shadow-2xs"
                    >
                        Lunas (<span x-text="formatNumber(invoices.filter(x => x.status === 'paid').length)"></span>)
                    </button>
                </div>
            </div>

            <!-- Summary Metric Cards (2 cols mobile, 4 cols desktop) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-5 shadow-xs">
                    <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider block">Total Tagihan (Target)</span>
                    <div class="mt-2 text-base sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono tracking-tight" x-text="formatRupiah(metrics.total_amount)"></div>
                    <div class="mt-1.5 text-[11px] sm:text-xs text-zinc-600 dark:text-zinc-400 font-medium" x-text="metrics.total_count + ' Tagihan Diterbitkan'"></div>
                </div>
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-5 shadow-xs">
                    <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider block">Uang Masuk (Terbayar)</span>
                    <div class="mt-2 text-base sm:text-2xl font-bold text-emerald-700 dark:text-emerald-400 font-mono tracking-tight" x-text="formatRupiah(metrics.total_paid)"></div>
                    <div class="mt-1.5 text-[11px] sm:text-xs text-zinc-600 dark:text-zinc-400 font-medium" x-text="metrics.paid_count + ' Lunas • ' + metrics.partial_count + ' Angsuran'"></div>
                </div>
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-5 shadow-xs">
                    <span class="text-xs font-bold text-rose-800 dark:text-rose-300 uppercase tracking-wider block">Sisa Tagihan (Piutang)</span>
                    <div class="mt-2 text-base sm:text-2xl font-bold text-rose-700 dark:text-rose-400 font-mono tracking-tight" x-text="formatRupiah(metrics.total_unpaid)"></div>
                    <div class="mt-1.5 text-[11px] sm:text-xs text-rose-700 dark:text-rose-400 font-semibold" x-text="metrics.unpaid_count + ' Belum Bayar Sama Sekali'"></div>
                </div>
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 sm:p-5 shadow-xs">
                    <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider block">Persentase Pelunasan</span>
                    <div class="mt-2 text-base sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono tracking-tight" x-text="collectionPercent + '%'"></div>
                    <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2 mt-3 overflow-hidden border border-zinc-200 dark:border-zinc-700">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-300" :style="'width: ' + collectionPercent + '%'"></div>
                    </div>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
                <!-- Search & Filters in Table Header -->
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="relative w-full sm:w-80">
                        <input type="text" x-model="invoiceSearch" placeholder="Cari nama, invoice, username hotspot..." class="px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 w-full focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                        Menampilkan <strong class="text-zinc-900 dark:text-zinc-100 font-bold" x-text="formatNumber(filteredInvoices.length)"></strong> dari <span class="font-semibold text-zinc-800 dark:text-zinc-200" x-text="formatNumber(invoices.length)"></span> tagihan
                    </div>
                </div>

                <!-- Batch Action Bar -->
                <div x-show="selectedInvoiceIds.length > 0" x-cloak class="px-4 py-2.5 bg-rose-50 dark:bg-rose-950/40 border-b border-rose-200 dark:border-rose-900/60 flex items-center justify-between gap-3 transition-all">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-rose-600 text-white text-[11px] font-bold font-mono" x-text="selectedInvoiceIds.length"></span>
                        <span class="text-xs font-semibold text-rose-900 dark:text-rose-200">Tagihan dipilih</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="selectedInvoiceIds = []" class="px-2.5 py-1 text-xs font-semibold rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-rose-100/60 dark:hover:bg-rose-900/40 transition-colors">
                            Batal Pilih
                        </button>
                        <button type="button" @click="confirmBatchDeleteInvoices()" :disabled="isBatchDeleting" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white shadow-xs transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span x-show="!isBatchDeleting">Hapus <span x-text="selectedInvoiceIds.length"></span> Tagihan Terpilih</span>
                            <span x-show="isBatchDeleting">Menghapus...</span>
                        </button>
                    </div>
                </div>

                <!-- Empty Invoices State -->
                <template x-if="filteredInvoices.length === 0">
                    <div class="p-12 text-center">
                        <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400 dark:text-zinc-500">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Belum Ada Tagihan untuk Periode Ini</h3>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Klik tombol di bawah untuk menerbitkan tagihan secara otomatis kepada semua pelanggan aktif.</p>
                        <button @click="openGenerateModal()" class="mt-4 inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Generate Tagihan Periode Ini</span>
                        </button>
                    </div>
                </template>

                <div class="overflow-x-auto" x-show="filteredInvoices.length > 0">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-100/90 dark:bg-zinc-800/80 text-[11px] font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-700">
                            <tr>
                                <th class="py-3.5 px-4 w-10 text-center">
                                    <input type="checkbox" @change="toggleSelectAllInvoices($event)" :checked="isAllInvoicesSelected()" class="rounded border-zinc-300 dark:border-zinc-700 text-rose-600 focus:ring-rose-500">
                                </th>
                                <th class="py-3.5 px-4">No. Invoice</th>
                                <th class="py-3.5 px-4">Nama Pelanggan</th>
                                <th class="py-3.5 px-4">Jatuh Tempo</th>
                                <th class="py-3.5 px-4">Total Tagihan</th>
                                <th class="py-3.5 px-4">Sudah Dibayar</th>
                                <th class="py-3.5 px-4">Sisa Tagihan</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            <template x-for="inv in filteredInvoices" :key="inv.id">
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors" :class="selectedInvoiceIds.includes(Number(inv.id)) ? 'bg-rose-50/50 dark:bg-rose-950/20' : ''">
                                    <td class="py-3.5 px-4 text-center">
                                        <input type="checkbox" :value="inv.id" x-model.number="selectedInvoiceIds" class="rounded border-zinc-300 dark:border-zinc-700 text-rose-600 focus:ring-rose-500">
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="inv.invoice_number"></td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-zinc-900 dark:text-zinc-100" x-text="inv.customer ? inv.customer.name : '-'"></div>
                                        <template x-if="inv.customer && inv.customer.hotspot_user">
                                            <a :href="'/users/' + inv.customer.hotspot_user.username" class="inline-flex items-center gap-1.5 mt-0.5 px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-[11px] font-mono font-semibold text-zinc-800 dark:text-zinc-200 hover:text-emerald-700 dark:hover:text-emerald-400 border border-zinc-200 dark:border-zinc-700 transition-colors" title="Lihat Detail User Hotspot">
                                                <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                                </svg>
                                                <span x-text="inv.customer.hotspot_user.username"></span>
                                            </a>
                                        </template>
                                    </td>
                                    <td class="py-3.5 px-4 text-zinc-700 dark:text-zinc-300 font-mono text-[11px] font-medium" x-text="inv.due_date ? formatDateIndo(inv.due_date) : '-'"></td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="formatRupiah(inv.amount)"></td>
                                    <td class="py-3.5 px-4 font-mono text-emerald-700 dark:text-emerald-400 font-bold" x-text="formatRupiah(inv.amount_paid)"></td>
                                    <td class="py-3.5 px-4 font-mono font-bold">
                                        <span x-show="inv.balance_due > 0" class="text-rose-700 dark:text-rose-400" x-text="formatRupiah(inv.balance_due)"></span>
                                        <span x-show="inv.balance_due <= 0" class="text-zinc-500 dark:text-zinc-400 font-medium">Rp 0 (Lunas)</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span 
                                            :class="inv.status === 'paid' ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700' : (inv.status === 'partial' ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border-amber-300 dark:border-amber-700' : 'bg-rose-50 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300 border-rose-300 dark:border-rose-700')" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border shadow-xs"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full" :class="inv.status === 'paid' ? 'bg-emerald-500' : (inv.status === 'partial' ? 'bg-amber-500' : 'bg-rose-500')"></span>
                                            <span x-text="inv.status === 'paid' ? 'LUNAS' : (inv.status === 'partial' ? 'ANGSURAN' : 'BELUM BAYAR')"></span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <template x-if="inv.status !== 'paid'">
                                                <button @click="openPayInvoiceModal(inv)" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    <span>Bayar</span>
                                                </button>
                                            </template>
                                            <button @click="viewInstallments(inv)" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg text-zinc-800 dark:text-zinc-200 bg-white dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 border border-zinc-300 dark:border-zinc-700 transition-colors shadow-2xs" title="Riwayat Cicilan">
                                                <svg class="w-3.5 h-3.5 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>Riwayat</span>
                                            </button>
                                            <button @click="confirmDeleteInvoice(inv)" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/40 border border-rose-200 dark:border-rose-800 transition-colors shadow-2xs" title="Hapus Tagihan">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span>Hapus</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: DATA MASTER PELANGGAN -->
        <div x-show="activeTab === 'customers'" class="space-y-6">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <input type="text" x-model="customerSearch" placeholder="Cari nama pelanggan, kontak, username..." class="px-3 py-2 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 w-full sm:w-80 focus:ring-emerald-500 focus:border-emerald-500">
                    <button @click="openCustomerModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <span>Tambah Pelanggan</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-100/90 dark:bg-zinc-800/80 text-[11px] font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-700">
                            <tr>
                                <th class="py-3.5 px-4">Nama Pelanggan</th>
                                <th class="py-3.5 px-4">Akun Hotspot</th>
                                <th class="py-3.5 px-4">Kontak / Alamat</th>
                                <th class="py-3.5 px-4">Tarif Bulanan Default</th>
                                <th class="py-3.5 px-4">Jatuh Tempo (Tgl)</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            <template x-for="c in filteredMasterCustomers" :key="c.id">
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span x-text="c.name"></span>
                                            <template x-if="c.profile">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-950/50 text-blue-800 dark:text-blue-300 border border-blue-300 dark:border-blue-700" x-text="c.profile.name"></span>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono">
                                        <template x-if="c.hotspot_user">
                                            <a :href="'/users/' + c.hotspot_user.username" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-zinc-100 dark:bg-zinc-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 hover:text-emerald-700 dark:hover:text-emerald-400 font-mono text-xs transition-colors font-bold">
                                                <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                                </svg>
                                                <span x-text="c.hotspot_user.username"></span>
                                            </a>
                                        </template>
                                        <template x-if="!c.hotspot_user">
                                            <span class="text-zinc-500 dark:text-zinc-400 text-xs italic">Tanpa Akun (Manual)</span>
                                        </template>
                                    </td>
                                    <td class="py-3.5 px-4 text-zinc-700 dark:text-zinc-300">
                                        <div class="font-medium" x-text="c.contact || '-'"></div>
                                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400" x-text="c.address || ''"></div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="formatRupiah(c.monthly_price)"></td>
                                    <td class="py-3.5 px-4 font-mono text-zinc-700 dark:text-zinc-300 font-semibold" x-text="'Tgl ' + (c.billing_day || 1)"></td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button @click="editCustomer(c)" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-zinc-800 dark:text-zinc-200 bg-white dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 border border-zinc-300 dark:border-zinc-700 transition-colors shadow-2xs">
                                                <svg class="w-3.5 h-3.5 text-zinc-500 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span>Edit</span>
                                            </button>
                                            <button @click="deleteCustomer(c)" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-700 dark:text-rose-400 bg-white dark:bg-zinc-800 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-rose-300 dark:border-rose-800 transition-colors shadow-2xs">
                                                <svg class="w-3.5 h-3.5 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span>Hapus</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL 1: GENERATE INVOICES -->
        <x-modal name="generate-invoices-modal" title="Generate Tagihan Bulanan">
            <form @submit.prevent="submitGenerateInvoices()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-zinc-800 dark:text-zinc-200 mb-1">Pilih Periode Bulan & Tahun *</label>
                    <input type="month" x-model="genForm.month" required class="w-full px-3 py-2 text-sm font-bold font-mono rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:ring-emerald-500 focus:border-emerald-500">
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1.5">Sistem akan membuat tagihan baru untuk pelanggan yang dipilih dan belum memiliki tagihan pada bulan tersebut.</p>
                </div>

                <!-- Customer Selection Section -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <label class="font-bold text-zinc-800 dark:text-zinc-200">Pilih Pelanggan Yang Diberi Tagihan</label>
                        <span class="text-[11px] font-mono text-zinc-600 dark:text-zinc-400">
                            <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="selectedCustomerIds.length"></span> / <span x-text="customers.length"></span> dipilih
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input 
                                type="text" 
                                x-model="customerSelectSearch" 
                                placeholder="Cari nama, username, atau paket..." 
                                class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 focus:ring-emerald-500 focus:border-emerald-500"
                            >
                            <svg class="w-3.5 h-3.5 text-zinc-400 absolute left-2.5 top-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/80 cursor-pointer select-none hover:bg-zinc-100 dark:hover:bg-zinc-700/50 transition-colors">
                            <input 
                                type="checkbox" 
                                @change="toggleSelectAllCustomers($event)" 
                                :checked="isAllCustomersSelected()" 
                                class="rounded border-zinc-300 dark:border-zinc-700 text-emerald-600 focus:ring-emerald-500"
                            >
                            <span class="text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">Pilih Semua</span>
                        </label>
                    </div>

                    <div class="max-h-52 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700/80 divide-y divide-zinc-200 dark:divide-zinc-800 bg-white dark:bg-zinc-900/60 p-1">
                        <template x-for="cust in selectableCustomers" :key="cust.id">
                            <label class="flex items-center justify-between p-2 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 rounded-md cursor-pointer transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <input 
                                        type="checkbox" 
                                        :value="cust.id" 
                                        x-model.number="selectedCustomerIds" 
                                        class="rounded border-zinc-300 dark:border-zinc-700 text-emerald-600 focus:ring-emerald-500"
                                    >
                                    <div class="truncate">
                                        <div class="font-bold text-zinc-900 dark:text-zinc-100 truncate" x-text="cust.name"></div>
                                        <div class="flex items-center gap-1.5 text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                            <span x-show="cust.hotspot_user" class="font-mono bg-zinc-100 dark:bg-zinc-800 px-1 py-0.5 rounded" x-text="cust.hotspot_user ? cust.hotspot_user.username : ''"></span>
                                            <span x-show="cust.profile" class="text-emerald-600 dark:text-emerald-400 font-medium" x-text="cust.profile ? cust.profile.name : ''"></span>
                                            <span x-show="cust.billing_day" class="text-zinc-400" x-text="'Tgl ' + cust.billing_day"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-xs shrink-0 pl-2" x-text="formatRupiah(cust.monthly_price)"></div>
                            </label>
                        </template>
                        <div x-show="selectableCustomers.length === 0" class="py-6 text-center text-xs text-zinc-500 dark:text-zinc-400">
                            Tidak ada pelanggan yang cocok dengan pencarian
                        </div>
                    </div>
                </div>

                <div class="p-3.5 bg-zinc-50 dark:bg-zinc-800/80 rounded-xl border border-zinc-300 dark:border-zinc-700 space-y-1.5">
                    <div class="flex justify-between items-center">
                        <span class="text-zinc-700 dark:text-zinc-300 font-medium">Pelanggan Dipilih:</span>
                        <strong class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-sm">
                            <span x-text="formatNumber(selectedCustomerIds.length)"></span> / <span x-text="formatNumber(customers.length)"></span>
                        </strong>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-zinc-700 dark:text-zinc-300 font-medium">Estimasi Total Tagihan:</span>
                        <strong class="font-mono font-bold text-emerald-700 dark:text-emerald-400 text-sm" x-text="formatRupiah(selectedCustomersTotal())"></strong>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" @click="$dispatch('close-modal', 'generate-invoices-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 font-semibold hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">Batal</button>
                    <button type="submit" :disabled="isGenerating || selectedCustomerIds.length === 0" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold flex items-center gap-2 shadow-xs transition-colors">
                        <span x-show="isGenerating">Memproses...</span>
                        <span x-show="!isGenerating">Mulai Generate Tagihan</span>
                    </button>
                </div>
            </form>
        </x-modal>

        <!-- MODAL 2: BAYAR ANGSURAN / PELUNASAN -->
        <x-modal name="pay-invoice-modal" title="Input Pembayaran Tagihan">
            <form @submit.prevent="submitInvoicePayment()" class="space-y-4 text-xs">
                <template x-if="activeInvoice">
                    <div class="space-y-4">
                        <div class="p-3.5 bg-zinc-50 dark:bg-zinc-800/80 rounded-xl border border-zinc-300 dark:border-zinc-700 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-sm" x-text="activeInvoice.invoice_number"></span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100" x-text="activeInvoice.customer ? activeInvoice.customer.name : ''"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-700 text-center">
                                <div>
                                    <div class="text-[11px] font-semibold text-zinc-600 dark:text-zinc-400">Total Tagihan</div>
                                    <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-sm mt-0.5" x-text="formatRupiah(activeInvoice.amount)"></div>
                                </div>
                                <div>
                                    <div class="text-[11px] font-semibold text-emerald-800 dark:text-emerald-400">Sudah Masuk</div>
                                    <div class="font-mono font-bold text-emerald-700 dark:text-emerald-400 text-sm mt-0.5" x-text="formatRupiah(activeInvoice.amount_paid)"></div>
                                </div>
                                <div>
                                    <div class="text-[11px] font-semibold text-rose-800 dark:text-rose-400">Sisa Tagihan</div>
                                    <div class="font-mono font-bold text-rose-700 dark:text-rose-400 text-sm mt-0.5" x-text="formatRupiah(activeInvoice.balance_due)"></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-bold text-zinc-800 dark:text-zinc-200">Nomor / Jumlah Angsuran Dibayar *</label>
                                <button type="button" @click="payForm.amount_paid = activeInvoice.balance_due" class="text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 px-2.5 py-1 rounded-md border border-emerald-300 dark:border-emerald-700 transition-colors">
                                    Bayar Lunas Penuh (Rp <span x-text="formatNumber(activeInvoice.balance_due)"></span>)
                                </button>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-zinc-500 dark:text-zinc-400 select-none">Rp</span>
                                <input 
                                    type="text" 
                                    inputmode="numeric"
                                    :value="formatNumber(payForm.amount_paid)" 
                                    @input="payForm.amount_paid = parseNumber($event.target.value); $event.target.value = formatNumber(payForm.amount_paid)" 
                                    required 
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono font-bold text-sm focus:ring-emerald-500 focus:border-emerald-500" 
                                    placeholder="50.000"
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-zinc-800 dark:text-zinc-200 mb-1">Tanggal Bayar *</label>
                                <input type="date" x-model="payForm.paid_at" required class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-medium focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block font-bold text-zinc-800 dark:text-zinc-200 mb-1">Metode Pembayaran</label>
                                <select x-model="payForm.payment_method" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-medium focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="cash">Tunai / Cash</option>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-800 dark:text-zinc-200 mb-1">Catatan Pembayaran (Opsional)</label>
                            <input type="text" x-model="payForm.notes" placeholder="e.g. Angsuran ke-1 / DP" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-medium focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div class="flex justify-end gap-2 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                            <button type="button" @click="$dispatch('close-modal', 'pay-invoice-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 font-semibold hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">Batal</button>
                            <button type="submit" :disabled="isPaying" class="px-4 py-2 rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-bold hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors shadow-xs">
                                <span x-show="isPaying">Menyimpan...</span>
                                <span x-show="!isPaying">Simpan Pembayaran</span>
                            </button>
                        </div>
                    </div>
                </template>
            </form>
        </x-modal>

        <!-- MODAL 3: RIWAYAT CICILAN & VOID -->
        <x-modal name="installments-history-modal" title="Riwayat Cicilan & Pembayaran">
            <div class="space-y-4 text-xs" x-data="{ historyPayments: [] }">
                <template x-if="activeInvoice">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between pb-3 border-b border-zinc-200 dark:border-zinc-700">
                            <div>
                                <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-sm" x-text="activeInvoice.invoice_number"></span>
                                <span class="text-zinc-400 dark:text-zinc-500 mx-1">•</span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100" x-text="activeInvoice.customer ? activeInvoice.customer.name : ''"></span>
                            </div>
                            <div class="text-right font-mono">
                                <span class="text-zinc-600 dark:text-zinc-400 text-xs">Sisa Tagihan: </span>
                                <strong class="text-rose-700 dark:text-rose-400 text-sm font-bold" x-text="formatRupiah(activeInvoice.balance_due)"></strong>
                            </div>
                        </div>

                        <template x-if="!activeInvoice.payments || activeInvoice.payments.length === 0">
                            <div class="py-8 text-center text-zinc-500 dark:text-zinc-400 font-medium">
                                Belum ada riwayat pembayaran untuk tagihan ini.
                            </div>
                        </template>

                        <div class="divide-y divide-zinc-200 dark:divide-zinc-800 max-h-64 overflow-y-auto" x-show="activeInvoice.payments && activeInvoice.payments.length > 0">
                            <template x-for="p in activeInvoice.payments" :key="p.id">
                                <div class="py-3 flex items-center justify-between">
                                    <div>
                                        <div class="font-mono font-bold text-emerald-700 dark:text-emerald-400 text-sm" x-text="formatRupiah(p.amount_paid)"></div>
                                        <div class="text-xs text-zinc-600 dark:text-zinc-400 font-medium mt-0.5" x-text="formatDateTimeIndo(p.paid_at) + ' • ' + (p.payment_method || 'cash').toUpperCase() + (p.notes ? ' (' + p.notes + ')' : '')"></div>
                                    </div>
                                    <div>
                                        <button @click="voidSinglePayment(p)" class="px-2.5 py-1 text-xs font-bold rounded-lg text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/50 border border-rose-300 dark:border-rose-700 transition-colors">
                                            VOID (Batalkan)
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-zinc-200 dark:border-zinc-800">
                            <button type="button" @click="$dispatch('close-modal', 'installments-history-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 font-semibold hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">Tutup</button>
                        </div>
                    </div>
                </template>
            </div>
        </x-modal>

        <!-- MODAL 4: FORM MASTER PELANGGAN -->
        <x-modal name="customer-modal" title="Form Master Pelanggan">
            <form @submit.prevent="saveCustomer()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-zinc-800 dark:text-zinc-200 mb-1">Paket Profil Bulanan (Opsional)</label>
                    <select x-model="custForm.profile_id" @change="onProfileSelected($event.target.value)" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-medium focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">-- Tanpa Paket Khusus / Tarif Manual --</option>
                        <template x-for="p in monthlyProfiles" :key="p.id">
                            <option :value="p.id" x-text="p.name + ' (' + formatRupiah(p.selling_price) + ' / bln - HPP ' + formatRupiah(p.cost_price) + ')'"></option>
                        </template>
                    </select>
                    <span class="text-xs text-zinc-600 dark:text-zinc-400 mt-1 block font-medium">Memilih paket profil otomatis mengisi tarif bulanan default dan HPP.</span>
                </div>
                <div>
                    <label class="block font-bold text-zinc-800 dark:text-zinc-200 mb-1">Tautkan Akun Hotspot (Opsional)</label>
                    <select x-model="custForm.hotspot_user_id" @change="onHotspotUserSelected($event.target.value)" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono font-medium focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">-- Tanpa Akun Hotspot (Pelanggan Manual / Offline) --</option>
                        <template x-for="u in hotspotUsers" :key="u.id">
                            <option :value="u.id" x-text="u.username + (u.profile ? ' (' + u.profile.name + ' - ' + formatRupiah(u.profile.selling_price) + ')' : '')"></option>
                        </template>
                    </select>
                    <span class="text-xs text-zinc-600 dark:text-zinc-400 mt-1 block font-medium">Pilih akun hotspot untuk menghubungkan user & otomatis mengisi tarif profil.</span>
                </div>
                <div>
                    <label class="block font-bold text-zinc-800 dark:text-zinc-200 mb-1">Nama Pelanggan *</label>
                    <input type="text" x-model="custForm.name" required placeholder="e.g. Budi Santoso" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-medium focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-zinc-800 dark:text-zinc-200 mb-1">Tarif Bulanan Default *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-zinc-500 dark:text-zinc-400 select-none">Rp</span>
                            <input 
                                type="text" 
                                inputmode="numeric"
                                :value="formatNumber(custForm.monthly_price)" 
                                @input="custForm.monthly_price = parseNumber($event.target.value); $event.target.value = formatNumber(custForm.monthly_price)" 
                                required 
                                class="w-full pl-9 pr-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono font-bold focus:ring-emerald-500 focus:border-emerald-500" 
                                placeholder="150.000"
                            >
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-zinc-800 dark:text-zinc-200 mb-1">Modal / Upstream Bulanan (Rp)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-zinc-500 dark:text-zinc-400 select-none">Rp</span>
                            <input 
                                type="text" 
                                inputmode="numeric"
                                :value="formatNumber(custForm.cost_price)" 
                                @input="custForm.cost_price = parseNumber($event.target.value); $event.target.value = formatNumber(custForm.cost_price)" 
                                class="w-full pl-9 pr-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono font-medium focus:ring-emerald-500 focus:border-emerald-500" 
                                placeholder="50.000"
                            >
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-zinc-800 dark:text-zinc-200 mb-1">Jatuh Tempo (Tgl)</label>
                        <input type="number" min="1" max="31" x-model="custForm.billing_day" placeholder="1" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-medium focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-bold text-zinc-800 dark:text-zinc-200 mb-1">WhatsApp / Kontak</label>
                        <input type="text" x-model="custForm.contact" placeholder="0812..." class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-medium focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block font-bold text-zinc-800 dark:text-zinc-200 mb-1">Alamat / Lokasi</label>
                        <input type="text" x-model="custForm.address" placeholder="Blok / No" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-medium focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" @click="$dispatch('close-modal', 'customer-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 font-semibold hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-bold hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors shadow-xs" x-text="isEditingCust ? 'Perbarui Pelanggan' : 'Simpan Pelanggan'"></button>
                </div>
            </form>
        </x-modal>
    </div>

    @push('scripts')
    <script>
        function monthlyBillingManager() {
            return {
                activeTab: 'invoices',
                selectedMonth: '{{ $selectedMonth ?? now()->format("Y-m") }}',
                statusFilter: 'all',
                invoiceSearch: '',
                customerSearch: '',
                invoices: @json($invoices ?? []),
                metrics: @json($metrics ?? []),
                customers: @json($customers ?? []),
                hotspotUsers: @json($hotspotUsers ?? []),
                monthlyProfiles: @json($monthlyProfiles ?? []),
                isEditingCust: false,
                isGenerating: false,
                isPaying: false,
                activeInvoice: null,
                selectedCustomerIds: [],
                customerSelectSearch: '',
                selectedInvoiceIds: [],
                isBatchDeleting: false,
                genForm: { month: '{{ $selectedMonth ?? now()->format("Y-m") }}' },
                custForm: { id: null, name: '', hotspot_user_id: '', profile_id: '', monthly_price: 150000, cost_price: 0, billing_day: 1, contact: '', address: '' },
                payForm: { amount_paid: 0, paid_at: '{{ now()->toDateString() }}', payment_method: 'cash', notes: '' },

                init() {
                    this.$watch('statusFilter', () => { this.selectedInvoiceIds = []; });
                    this.$watch('invoiceSearch', () => { this.selectedInvoiceIds = []; });
                },

                get filteredInvoices() {
                    return this.invoices.filter(inv => {
                        if (this.statusFilter !== 'all' && inv.status !== this.statusFilter) return false;
                        if (!this.invoiceSearch) return true;
                        const q = this.invoiceSearch.toLowerCase();
                        const matchNum = inv.invoice_number && inv.invoice_number.toLowerCase().includes(q);
                        const matchCust = inv.customer && inv.customer.name && inv.customer.name.toLowerCase().includes(q);
                        const matchHotspot = inv.customer && inv.customer.hotspot_user && inv.customer.hotspot_user.username && inv.customer.hotspot_user.username.toLowerCase().includes(q);
                        return matchNum || matchCust || matchHotspot;
                    });
                },

                get filteredMasterCustomers() {
                    return this.customers.filter(c => {
                        if (!this.customerSearch) return true;
                        const q = this.customerSearch.toLowerCase();
                        const matchName = c.name && c.name.toLowerCase().includes(q);
                        const matchHotspot = c.hotspot_user && c.hotspot_user.username && c.hotspot_user.username.toLowerCase().includes(q);
                        const matchContact = c.contact && c.contact.toLowerCase().includes(q);
                        return matchName || matchHotspot || matchContact;
                    });
                },

                get collectionPercent() {
                    if (!this.metrics.total_amount || this.metrics.total_amount <= 0) return 0;
                    return Math.min(100, Math.round((this.metrics.total_paid / this.metrics.total_amount) * 100));
                },

                changeMonth() {
                    window.location.href = `{{ route('pos.monthly') }}?month=${this.selectedMonth}`;
                },

                setMonthOffset(offset) {
                    if (offset === 0) {
                        const now = new Date();
                        const m = (now.getMonth() + 1).toString().padStart(2, '0');
                        this.selectedMonth = `${now.getFullYear()}-${m}`;
                        this.changeMonth();
                    }
                },

                get selectableCustomers() {
                    return this.customers.filter(c => {
                        if (!this.customerSelectSearch) return true;
                        const q = this.customerSelectSearch.toLowerCase();
                        const matchName = c.name && c.name.toLowerCase().includes(q);
                        const matchHotspot = c.hotspot_user && c.hotspot_user.username && c.hotspot_user.username.toLowerCase().includes(q);
                        const matchContact = c.contact && c.contact.toLowerCase().includes(q);
                        const matchProfile = c.profile && c.profile.name && c.profile.name.toLowerCase().includes(q);
                        return matchName || matchHotspot || matchContact || matchProfile;
                    });
                },

                toggleSelectAllCustomers(e) {
                    if (e.target.checked) {
                        const selectableIds = this.selectableCustomers.map(c => Number(c.id));
                        this.selectedCustomerIds = Array.from(new Set([...this.selectedCustomerIds.map(Number), ...selectableIds]));
                    } else {
                        const selectableSet = new Set(this.selectableCustomers.map(c => Number(c.id)));
                        this.selectedCustomerIds = this.selectedCustomerIds.map(Number).filter(id => !selectableSet.has(id));
                    }
                },

                isAllCustomersSelected() {
                    if (this.selectableCustomers.length === 0) return false;
                    const selectedSet = new Set(this.selectedCustomerIds.map(Number));
                    return this.selectableCustomers.every(c => selectedSet.has(Number(c.id)));
                },

                selectedCustomersTotal() {
                    const selectedSet = new Set(this.selectedCustomerIds.map(Number));
                    return this.customers
                        .filter(c => selectedSet.has(Number(c.id)))
                        .reduce((acc, c) => acc + (parseFloat(c.monthly_price) || 0), 0);
                },

                openGenerateModal() {
                    this.genForm.month = this.selectedMonth;
                    this.customerSelectSearch = '';
                    this.selectedCustomerIds = this.customers.map(c => Number(c.id));
                    this.$dispatch('open-modal', 'generate-invoices-modal');
                },

                submitGenerateInvoices() {
                    if (this.selectedCustomerIds.length === 0) {
                        window.showToast('Pilih minimal satu pelanggan untuk generate tagihan!', 'error');
                        return;
                    }
                    this.isGenerating = true;
                    fetch('{{ route("pos.monthly.invoices.generate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            billing_month: this.genForm.month,
                            customer_ids: this.selectedCustomerIds.map(Number)
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isGenerating = false;
                        if (data.success) {
                            window.showToast(`Berhasil menerbitkan ${data.generated} tagihan baru!`, 'success');
                            this.$dispatch('close-modal', 'generate-invoices-modal');
                            setTimeout(() => {
                                window.location.href = `{{ route('pos.monthly') }}?month=${this.genForm.month}`;
                            }, 500);
                        } else {
                            window.showToast(data.message || 'Gagal menerbitkan tagihan', 'error');
                        }
                    })
                    .catch(() => {
                        this.isGenerating = false;
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },

                toggleSelectAllInvoices(e) {
                    if (e.target.checked) {
                        const allFilteredIds = this.filteredInvoices.map(i => Number(i.id));
                        this.selectedInvoiceIds = Array.from(new Set([...this.selectedInvoiceIds.map(Number), ...allFilteredIds]));
                    } else {
                        const filteredSet = new Set(this.filteredInvoices.map(i => Number(i.id)));
                        this.selectedInvoiceIds = this.selectedInvoiceIds.map(Number).filter(id => !filteredSet.has(id));
                    }
                },

                isAllInvoicesSelected() {
                    if (this.filteredInvoices.length === 0) return false;
                    const selectedSet = new Set(this.selectedInvoiceIds.map(Number));
                    return this.filteredInvoices.every(i => selectedSet.has(Number(i.id)));
                },

                confirmDeleteInvoice(inv) {
                    const custName = inv.customer ? inv.customer.name : 'Pelanggan';
                    const hasPayments = (inv.amount_paid > 0);
                    let msg = `Hapus tagihan ${inv.invoice_number} (${custName})?`;
                    if (hasPayments) {
                        msg += `\nPERINGATAN: Seluruh riwayat pembayaran (${this.formatRupiah(inv.amount_paid)}) yang terkait dengan tagihan ini akan ikut terhapus!`;
                    }
                    if (!confirm(msg)) return;

                    fetch(`/pos/monthly/invoices/${inv.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.showToast(data.message || 'Tagihan berhasil dihapus', 'success');
                            this.invoices = this.invoices.filter(i => Number(i.id) !== Number(inv.id));
                            this.selectedInvoiceIds = this.selectedInvoiceIds.map(Number).filter(id => id !== Number(inv.id));
                            this.recalcLocalMetrics();
                        } else {
                            window.showToast(data.message || 'Gagal menghapus tagihan', 'error');
                        }
                    })
                    .catch(() => {
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },

                confirmBatchDeleteInvoices() {
                    if (this.selectedInvoiceIds.length === 0) return;
                    const count = this.selectedInvoiceIds.length;
                    const msg = `Apakah Anda yakin ingin menghapus ${count} tagihan terpilih?\nPERINGATAN: Semua catatan pembayaran yang terhubung dengan tagihan tersebut akan ikut terhapus permanen!`;
                    if (!confirm(msg)) return;

                    this.isBatchDeleting = true;
                    fetch('{{ route("pos.monthly.invoices.batch-delete") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            invoice_ids: this.selectedInvoiceIds.map(Number)
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isBatchDeleting = false;
                        if (data.success) {
                            window.showToast(data.message || `${count} tagihan berhasil dihapus`, 'success');
                            const deletedSet = new Set(this.selectedInvoiceIds.map(Number));
                            this.invoices = this.invoices.filter(i => !deletedSet.has(Number(i.id)));
                            this.selectedInvoiceIds = [];
                            this.recalcLocalMetrics();
                        } else {
                            window.showToast(data.message || 'Gagal menghapus tagihan secara massal', 'error');
                        }
                    })
                    .catch(() => {
                        this.isBatchDeleting = false;
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },

                openPayInvoiceModal(inv) {
                    this.activeInvoice = inv;
                    this.payForm = {
                        amount_paid: inv.balance_due,
                        paid_at: new Date().toISOString().split('T')[0],
                        payment_method: 'cash',
                        notes: ''
                    };
                    this.$dispatch('open-modal', 'pay-invoice-modal');
                },

                submitInvoicePayment() {
                    if (!this.activeInvoice) return;
                    this.isPaying = true;
                    fetch(`/pos/monthly/invoices/${this.activeInvoice.id}/pay`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.payForm)
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isPaying = false;
                        if (data.success) {
                            const idx = this.invoices.findIndex(x => x.id === this.activeInvoice.id);
                            if (idx !== -1) {
                                this.invoices[idx] = data.invoice;
                            }
                            this.recalcLocalMetrics();
                            this.$dispatch('close-modal', 'pay-invoice-modal');
                            window.showToast(`Pembayaran tagihan ${this.activeInvoice.invoice_number} berhasil dicatat!`, 'success');
                        } else {
                            window.showToast(data.message || 'Gagal mencatat pembayaran', 'error');
                        }
                    })
                    .catch(() => {
                        this.isPaying = false;
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },

                viewInstallments(inv) {
                    this.activeInvoice = inv;
                    this.$dispatch('open-modal', 'installments-history-modal');
                },

                voidSinglePayment(payment) {
                    if (!confirm(`Apakah Anda yakin ingin membatalkan (VOID) pembayaran sebesar ${this.formatRupiah(payment.amount_paid)}?`)) return;

                    fetch(`/pos/monthly/payments/${payment.id}/void`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ reason: 'Pembatalan transaksi cicilan' })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.showToast('Pembayaran berhasil di-VOID', 'info');
                            window.location.reload();
                        } else {
                            window.showToast(data.message || 'Gagal VOID', 'error');
                        }
                    })
                    .catch(() => {
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },

                onProfileSelected(pid) {
                    if (!pid) return;
                    const p = this.monthlyProfiles.find(x => String(x.id) === String(pid));
                    if (p) {
                        if (p.selling_price && p.selling_price > 0) {
                            this.custForm.monthly_price = p.selling_price;
                        }
                        if (p.cost_price !== undefined && p.cost_price !== null) {
                            this.custForm.cost_price = p.cost_price;
                        }
                    }
                },

                onHotspotUserSelected(userId) {
                    if (!userId) return;
                    const u = this.hotspotUsers.find(x => String(x.id) === String(userId));
                    if (u) {
                        if (!this.custForm.name || this.custForm.name.trim() === '') {
                            this.custForm.name = u.username;
                        }
                        if (u.profile && u.profile.selling_price > 0) {
                            this.custForm.monthly_price = u.profile.selling_price;
                        }
                        if (u.profile && u.profile.cost_price > 0) {
                            this.custForm.cost_price = u.profile.cost_price;
                        }
                    }
                },

                openCustomerModal() {
                    this.isEditingCust = false;
                    this.custForm = { id: null, name: '', hotspot_user_id: '', profile_id: '', monthly_price: 150000, cost_price: 0, billing_day: 1, contact: '', address: '' };
                    this.$dispatch('open-modal', 'customer-modal');
                },

                editCustomer(c) {
                    this.isEditingCust = true;
                    this.custForm = {
                        id: c.id,
                        name: c.name,
                        hotspot_user_id: c.hotspot_user_id || (c.hotspot_user ? c.hotspot_user.id : ''),
                        profile_id: c.profile_id || (c.profile ? c.profile.id : ''),
                        monthly_price: c.monthly_price || 150000,
                        cost_price: c.cost_price || 0,
                        billing_day: c.billing_day || 1,
                        contact: c.contact || '',
                        address: c.address || ''
                    };
                    this.$dispatch('open-modal', 'customer-modal');
                },

                saveCustomer() {
                    const url = this.isEditingCust ? `/pos/monthly/customers/${this.custForm.id}` : '/pos/monthly/customers';
                    const method = this.isEditingCust ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.custForm)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (this.isEditingCust) {
                                const idx = this.customers.findIndex(x => x.id === this.custForm.id);
                                if (idx !== -1) this.customers[idx] = { ...this.customers[idx], ...data.customer };
                                window.showToast(`Pelanggan ${this.custForm.name} berhasil diperbarui!`, 'success');
                            } else {
                                this.customers.unshift(data.customer);
                                window.showToast(`Pelanggan ${data.customer.name} berhasil ditambahkan!`, 'success');
                            }
                            this.$dispatch('close-modal', 'customer-modal');
                        } else {
                            window.showToast(data.message || 'Gagal menyimpan customer', 'error');
                        }
                    })
                    .catch(() => {
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },

                deleteCustomer(c) {
                    if (confirm(`Apakah Anda yakin ingin menghapus data pelanggan "${c.name}"?`)) {
                        fetch(`/pos/monthly/customers/${c.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.customers = this.customers.filter(x => x.id !== c.id);
                                window.showToast(`Pelanggan ${c.name} telah dihapus`, 'info');
                            } else {
                                window.showToast(data.message || 'Gagal menghapus pelanggan', 'error');
                            }
                        })
                        .catch(() => {
                            window.showToast('Gagal menghapus customer', 'error');
                        });
                    }
                },

                recalcLocalMetrics() {
                    let totalAmount = 0;
                    let totalPaid = 0;
                    let totalUnpaid = 0;
                    let paidCount = 0;
                    let partialCount = 0;
                    let unpaidCount = 0;

                    this.invoices.forEach(inv => {
                        totalAmount += (parseFloat(inv.amount) || 0);
                        totalPaid += (parseFloat(inv.amount_paid) || 0);
                        totalUnpaid += (parseFloat(inv.balance_due) || 0);
                        if (inv.status === 'paid') paidCount++;
                        else if (inv.status === 'partial') partialCount++;
                        else unpaidCount++;
                    });

                    this.metrics.total_count = this.invoices.length;
                    this.metrics.total_amount = totalAmount;
                    this.metrics.total_paid = totalPaid;
                    this.metrics.total_unpaid = totalUnpaid;
                    this.metrics.paid_count = paidCount;
                    this.metrics.partial_count = partialCount;
                    this.metrics.unpaid_count = unpaidCount;
                },

                formatRupiah(num) {
                    return window.formatRupiah(num || 0);
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
