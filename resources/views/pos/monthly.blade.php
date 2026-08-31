<x-layouts.app>
    <x-slot:header>Tagihan & Pelanggan Bulanan</x-slot:header>

    <div class="space-y-6" x-data="monthlyBillingManager()">
        <!-- Header & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Tagihan & Pelanggan Bulanan</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Penerbitan tagihan bulanan per periode, pembayaran angsuran (cicilan), dan data pelanggan</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button @click="openGenerateModal()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>⚡ Generate Tagihan</span>
                </button>
                <button @click="openCustomerModal()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>+ Tambah Customer</span>
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-zinc-200 dark:border-zinc-800 text-sm">
            <button 
                @click="activeTab = 'invoices'" 
                :class="activeTab === 'invoices' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 font-bold' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 font-medium'"
                class="flex items-center gap-2 px-4 py-3 border-b-2 transition-colors"
            >
                <span>📋 Daftar Tagihan (Invoices)</span>
                <span class="px-2 py-0.5 rounded-full text-xs" :class="activeTab === 'invoices' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400'" x-text="invoices.length"></span>
            </button>
            <button 
                @click="activeTab = 'customers'" 
                :class="activeTab === 'customers' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400 font-bold' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 font-medium'"
                class="flex items-center gap-2 px-4 py-3 border-b-2 transition-colors"
            >
                <span>👥 Data Master Pelanggan</span>
                <span class="px-2 py-0.5 rounded-full text-xs" :class="activeTab === 'customers' ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400'" x-text="customers.length"></span>
            </button>
        </div>

        <!-- TAB 1: INVOICES (TAGIHAN BULANAN) -->
        <div x-show="activeTab === 'invoices'" class="space-y-6">
            <!-- Period Selector & Status Filters -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <label class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Periode Tagihan:</label>
                    <div class="flex items-center gap-2">
                        <input 
                            type="month" 
                            x-model="selectedMonth" 
                            @change="changeMonth()" 
                            class="px-3 py-1.5 text-xs font-bold font-mono rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100"
                        >
                        <button type="button" @click="setMonthOffset(0)" class="px-2.5 py-1.5 text-xs font-medium rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300">
                            Bulan Ini
                        </button>
                    </div>
                </div>

                <!-- Status Filter Pills -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <button 
                        type="button" 
                        @click="statusFilter = 'all'" 
                        :class="statusFilter === 'all' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200'" 
                        class="px-3 py-1.5 rounded-lg transition-colors"
                    >
                        Semua (<span x-text="invoices.length"></span>)
                    </button>
                    <button 
                        type="button" 
                        @click="statusFilter = 'unpaid'" 
                        :class="statusFilter === 'unpaid' ? 'bg-rose-600 text-white font-bold' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100'" 
                        class="px-3 py-1.5 rounded-lg transition-colors"
                    >
                        Belum Bayar (<span x-text="invoices.filter(x => x.status === 'unpaid').length"></span>)
                    </button>
                    <button 
                        type="button" 
                        @click="statusFilter = 'partial'" 
                        :class="statusFilter === 'partial' ? 'bg-amber-600 text-white font-bold' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 hover:bg-amber-100'" 
                        class="px-3 py-1.5 rounded-lg transition-colors"
                    >
                        Angsuran (<span x-text="invoices.filter(x => x.status === 'partial').length"></span>)
                    </button>
                    <button 
                        type="button" 
                        @click="statusFilter = 'paid'" 
                        :class="statusFilter === 'paid' ? 'bg-emerald-600 text-white font-bold' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100'" 
                        class="px-3 py-1.5 rounded-lg transition-colors"
                    >
                        Lunas (<span x-text="invoices.filter(x => x.status === 'paid').length"></span>)
                    </button>
                </div>
            </div>

            <!-- Summary Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                    <span class="text-xs font-medium text-zinc-400 uppercase">Total Tagihan (Target)</span>
                    <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono" x-text="formatRupiah(metrics.total_amount)"></div>
                    <div class="mt-2 text-xs text-zinc-500" x-text="metrics.total_count + ' Tagihan Diterbitkan'"></div>
                </div>
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                    <span class="text-xs font-medium text-zinc-400 uppercase">Uang Masuk (Terbayar)</span>
                    <div class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono" x-text="formatRupiah(metrics.total_paid)"></div>
                    <div class="mt-2 text-xs text-zinc-500" x-text="metrics.paid_count + ' Lunas • ' + metrics.partial_count + ' Angsuran'"></div>
                </div>
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                    <span class="text-xs font-medium text-zinc-400 uppercase">Sisa Tagihan (Piutang)</span>
                    <div class="mt-2 text-2xl font-bold text-rose-600 dark:text-rose-400 font-mono" x-text="formatRupiah(metrics.total_unpaid)"></div>
                    <div class="mt-2 text-xs text-rose-600 dark:text-rose-400" x-text="metrics.unpaid_count + ' Belum Bayar Sama Sekali'"></div>
                </div>
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs">
                    <span class="text-xs font-medium text-zinc-400 uppercase">Persentase Pelunasan</span>
                    <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono" x-text="collectionPercent + '%'"></div>
                    <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2 mt-3 overflow-hidden">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-300" :style="'width: ' + collectionPercent + '%'"></div>
                    </div>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
                <!-- Search & Filters in Table Header -->
                <div class="p-4 border-b border-zinc-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <input type="text" x-model="invoiceSearch" placeholder="Cari nama atau nomor invoice..." class="px-3 py-1.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 w-full sm:w-72">
                    <div class="text-xs text-zinc-400">
                        Menampilkan <strong class="text-zinc-900 dark:text-zinc-100" x-text="filteredInvoices.length"></strong> dari <span x-text="invoices.length"></span> tagihan
                    </div>
                </div>

                <!-- Empty Invoices State -->
                <template x-if="filteredInvoices.length === 0">
                    <div class="p-12 text-center">
                        <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Belum Ada Tagihan untuk Periode Ini</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Klik tombol di bawah untuk menerbitkan tagihan secara otomatis kepada semua pelanggan aktif.</p>
                        <button @click="openGenerateModal()" class="mt-4 inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white">
                            ⚡ Generate Tagihan Periode Ini
                        </button>
                    </div>
                </template>

                <div class="overflow-x-auto" x-show="filteredInvoices.length > 0">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
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
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            <template x-for="inv in filteredInvoices" :key="inv.id">
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="inv.invoice_number"></td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-zinc-900 dark:text-zinc-100" x-text="inv.customer ? inv.customer.name : '-'"></div>
                                        <template x-if="inv.customer && inv.customer.hotspot_user">
                                            <a :href="'/users/' + inv.customer.hotspot_user.username" class="inline-flex items-center gap-1 mt-0.5 px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-[11px] font-mono text-zinc-700 dark:text-zinc-300 hover:text-emerald-600" title="Lihat Detail User Hotspot">
                                                <span class="text-emerald-500">📶</span>
                                                <span x-text="inv.customer.hotspot_user.username"></span>
                                            </a>
                                        </template>
                                    </td>
                                    <td class="py-3.5 px-4 text-zinc-500 font-mono text-[11px]" x-text="inv.due_date || '-'"></td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="formatRupiah(inv.amount)"></td>
                                    <td class="py-3.5 px-4 font-mono text-emerald-600 dark:text-emerald-400 font-semibold" x-text="formatRupiah(inv.amount_paid)"></td>
                                    <td class="py-3.5 px-4 font-mono font-bold" :class="inv.balance_due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-400'" x-text="formatRupiah(inv.balance_due)"></td>
                                    <td class="py-3.5 px-4">
                                        <span 
                                            :class="inv.status === 'paid' ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/20' : (inv.status === 'partial' ? 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/20' : 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-500/20')" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                        >
                                            <span class="w-1.5 h-1.5 rounded-full" :class="inv.status === 'paid' ? 'bg-emerald-500' : (inv.status === 'partial' ? 'bg-amber-500' : 'bg-rose-500')"></span>
                                            <span x-text="inv.status === 'paid' ? 'LUNAS' : (inv.status === 'partial' ? 'ANGSURAN' : 'BELUM BAYAR')"></span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <template x-if="inv.status !== 'paid'">
                                                <button @click="openPayInvoiceModal(inv)" class="px-2.5 py-1 text-xs font-semibold rounded bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors">
                                                    Bayar
                                                </button>
                                            </template>
                                            <button @click="viewInstallments(inv)" class="px-2 py-1 text-xs font-medium rounded text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors" title="Riwayat Cicilan">
                                                Riwayat
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
                <div class="p-4 border-b border-zinc-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <input type="text" x-model="customerSearch" placeholder="Cari nama pelanggan..." class="px-3 py-1.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 w-full sm:w-64">
                    <button @click="openCustomerModal()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900">
                        + Tambah Pelanggan Baru
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="py-3.5 px-4">Nama Pelanggan</th>
                                <th class="py-3.5 px-4">Akun Hotspot</th>
                                <th class="py-3.5 px-4">Kontak / Alamat</th>
                                <th class="py-3.5 px-4">Tarif Bulanan Default</th>
                                <th class="py-3.5 px-4">Jatuh Tempo (Tgl)</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            <template x-for="c in filteredMasterCustomers" :key="c.id">
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                    <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100" x-text="c.name"></td>
                                    <td class="py-3.5 px-4 font-mono">
                                        <template x-if="c.hotspot_user">
                                            <a :href="'/users/' + c.hotspot_user.username" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-zinc-100 dark:bg-zinc-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-zinc-200 hover:text-emerald-600 font-mono text-xs transition-colors">
                                                <span class="text-emerald-500">📶</span>
                                                <span class="font-bold" x-text="c.hotspot_user.username"></span>
                                            </a>
                                        </template>
                                        <template x-if="!c.hotspot_user">
                                            <span class="text-zinc-400 text-xs italic">Tanpa Akun (Manual)</span>
                                        </template>
                                    </td>
                                    <td class="py-3.5 px-4 text-zinc-500">
                                        <div x-text="c.contact || '-'"></div>
                                        <div class="text-[10px] text-zinc-400" x-text="c.address || ''"></div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="formatRupiah(c.monthly_price)"></td>
                                    <td class="py-3.5 px-4 font-mono text-zinc-600 dark:text-zinc-400" x-text="'Tanggal ' + (c.billing_day || 1)"></td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button @click="editCustomer(c)" class="px-2.5 py-1 rounded text-xs font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700">
                                                Edit
                                            </button>
                                            <button @click="deleteCustomer(c)" class="px-2.5 py-1 rounded text-xs font-medium text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100">
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
        </div>

        <!-- MODAL 1: GENERATE INVOICES -->
        <x-modal name="generate-invoices-modal" title="⚡ Generate Tagihan Bulanan">
            <form @submit.prevent="submitGenerateInvoices()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Pilih Periode Bulan & Tahun *</label>
                    <input type="month" x-model="genForm.month" required class="w-full px-3 py-2 text-sm font-bold font-mono rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    <p class="text-[11px] text-zinc-400 mt-1">Sistem akan membuat tagihan baru untuk seluruh pelanggan aktif yang belum memiliki tagihan pada bulan tersebut.</p>
                </div>

                <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-lg border border-zinc-200 dark:border-zinc-700 space-y-1">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Total Pelanggan Aktif:</span>
                        <strong class="font-mono text-zinc-900 dark:text-zinc-100" x-text="customers.length"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Estimasi Total Tagihan:</span>
                        <strong class="font-mono text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(customers.reduce((acc, c) => acc + (parseFloat(c.monthly_price) || 0), 0))"></strong>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="$dispatch('close-modal', 'generate-invoices-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">Batal</button>
                    <button type="submit" :disabled="isGenerating" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold flex items-center gap-2">
                        <span x-show="isGenerating">Memproses...</span>
                        <span x-show="!isGenerating">⚡ Mulai Generate Tagihan</span>
                    </button>
                </div>
            </form>
        </x-modal>

        <!-- MODAL 2: BAYAR ANGSURAN / PELUNASAN -->
        <x-modal name="pay-invoice-modal" title="Input Pembayaran Tagihan">
            <form @submit.prevent="submitInvoicePayment()" class="space-y-4 text-xs">
                <template x-if="activeInvoice">
                    <div class="space-y-4">
                        <div class="p-3.5 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-sm" x-text="activeInvoice.invoice_number"></span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100" x-text="activeInvoice.customer ? activeInvoice.customer.name : ''"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-700 text-center">
                                <div>
                                    <div class="text-[10px] text-zinc-400">Total Tagihan</div>
                                    <div class="font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="formatRupiah(activeInvoice.amount)"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-zinc-400">Sudah Masuk</div>
                                    <div class="font-mono font-semibold text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(activeInvoice.amount_paid)"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-zinc-400">Sisa Tagihan</div>
                                    <div class="font-mono font-bold text-rose-600 dark:text-rose-400" x-text="formatRupiah(activeInvoice.balance_due)"></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-medium text-zinc-700 dark:text-zinc-300">Nomor / Jumlah Angsuran Dibayar *</label>
                                <button type="button" @click="payForm.amount_paid = activeInvoice.balance_due" class="text-[10px] text-emerald-600 dark:text-emerald-400 hover:underline">
                                    Bayar Lunas Penuh (Rp <span x-text="formatNumber(activeInvoice.balance_due)"></span>)
                                </button>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-semibold text-zinc-400 select-none">Rp</span>
                                <input 
                                    type="text" 
                                    inputmode="numeric"
                                    :value="formatNumber(payForm.amount_paid)" 
                                    @input="payForm.amount_paid = parseNumber($event.target.value); $event.target.value = formatNumber(payForm.amount_paid)" 
                                    required 
                                    class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono font-bold text-sm" 
                                    placeholder="50.000"
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Bayar *</label>
                                <input type="date" x-model="payForm.paid_at" required class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                            </div>
                            <div>
                                <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Metode Pembayaran</label>
                                <select x-model="payForm.payment_method" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                                    <option value="cash">Tunai / Cash</option>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Catatan Pembayaran (Opsional)</label>
                            <input type="text" x-model="payForm.notes" placeholder="e.g. Angsuran ke-1 / DP" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                        </div>

                        <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                            <button type="button" @click="$dispatch('close-modal', 'pay-invoice-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">Batal</button>
                            <button type="submit" :disabled="isPaying" class="px-4 py-2 rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-semibold">
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
                        <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                            <div>
                                <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="activeInvoice.invoice_number"></span>
                                <span class="text-zinc-400"> - </span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100" x-text="activeInvoice.customer ? activeInvoice.customer.name : ''"></span>
                            </div>
                            <div class="text-right font-mono">
                                <span class="text-zinc-400">Sisa: </span>
                                <strong class="text-rose-600 dark:text-rose-400" x-text="formatRupiah(activeInvoice.balance_due)"></strong>
                            </div>
                        </div>

                        <template x-if="!activeInvoice.payments || activeInvoice.payments.length === 0">
                            <div class="py-8 text-center text-zinc-400">
                                Belum ada riwayat pembayaran untuk tagihan ini.
                            </div>
                        </template>

                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800 max-h-64 overflow-y-auto" x-show="activeInvoice.payments && activeInvoice.payments.length > 0">
                            <template x-for="p in activeInvoice.payments" :key="p.id">
                                <div class="py-2.5 flex items-center justify-between">
                                    <div>
                                        <div class="font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(p.amount_paid)"></div>
                                        <div class="text-[10px] text-zinc-400" x-text="p.paid_at + ' • ' + (p.payment_method || 'cash').toUpperCase() + (p.notes ? ' (' + p.notes + ')' : '')"></div>
                                    </div>
                                    <div>
                                        <button @click="voidSinglePayment(p)" class="px-2 py-1 text-[10px] font-semibold rounded text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 transition-colors">
                                            VOID (Batalkan)
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-zinc-100 dark:border-zinc-800">
                            <button type="button" @click="$dispatch('close-modal', 'installments-history-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">Tutup</button>
                        </div>
                    </div>
                </template>
            </div>
        </x-modal>

        <!-- MODAL 4: FORM MASTER PELANGGAN -->
        <x-modal name="customer-modal" title="Form Master Pelanggan">
            <form @submit.prevent="saveCustomer()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tautkan Akun Hotspot (Opsional)</label>
                    <select x-model="custForm.hotspot_user_id" @change="onHotspotUserSelected($event.target.value)" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono">
                        <option value="">-- Tanpa Akun Hotspot (Pelanggan Manual / Offline) --</option>
                        <template x-for="u in hotspotUsers" :key="u.id">
                            <option :value="u.id" x-text="u.username + (u.profile ? ' (' + u.profile.name + ' - ' + formatRupiah(u.profile.selling_price) + ')' : '')"></option>
                        </template>
                    </select>
                    <span class="text-[10px] text-zinc-400 mt-1 block">Pilih akun hotspot untuk menghubungkan user & otomatis mengisi tarif profil.</span>
                </div>
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nama Pelanggan *</label>
                    <input type="text" x-model="custForm.name" required placeholder="e.g. Budi Santoso" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tarif Bulanan Default *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-semibold text-zinc-400 select-none">Rp</span>
                            <input 
                                type="text" 
                                inputmode="numeric"
                                :value="formatNumber(custForm.monthly_price)" 
                                @input="custForm.monthly_price = parseNumber($event.target.value); $event.target.value = formatNumber(custForm.monthly_price)" 
                                required 
                                class="w-full pl-9 pr-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono font-bold" 
                                placeholder="150.000"
                            >
                        </div>
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Modal / Upstream Bulanan (Rp)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-semibold text-zinc-400 select-none">Rp</span>
                            <input 
                                type="text" 
                                inputmode="numeric"
                                :value="formatNumber(custForm.cost_price)" 
                                @input="custForm.cost_price = parseNumber($event.target.value); $event.target.value = formatNumber(custForm.cost_price)" 
                                class="w-full pl-9 pr-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono" 
                                placeholder="50.000"
                            >
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Jatuh Tempo (Tgl)</label>
                        <input type="number" min="1" max="31" x-model="custForm.billing_day" placeholder="1" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">WhatsApp / Kontak</label>
                        <input type="text" x-model="custForm.contact" placeholder="0812..." class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Alamat / Lokasi</label>
                        <input type="text" x-model="custForm.address" placeholder="Blok / No" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="$dispatch('close-modal', 'customer-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-semibold" x-text="isEditingCust ? 'Perbarui Pelanggan' : 'Simpan Pelanggan'"></button>
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
                isEditingCust: false,
                isGenerating: false,
                isPaying: false,
                activeInvoice: null,
                genForm: { month: '{{ $selectedMonth ?? now()->format("Y-m") }}' },
                custForm: { id: null, name: '', hotspot_user_id: '', monthly_price: 150000, billing_day: 1, contact: '', address: '' },
                payForm: { amount_paid: 0, paid_at: '{{ now()->toDateString() }}', payment_method: 'cash', notes: '' },

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

                openGenerateModal() {
                    this.genForm.month = this.selectedMonth;
                    this.$dispatch('open-modal', 'generate-invoices-modal');
                },

                submitGenerateInvoices() {
                    this.isGenerating = true;
                    fetch('{{ route("pos.monthly.invoices.generate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ billing_month: this.genForm.month })
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
                    this.custForm = { id: null, name: '', hotspot_user_id: '', monthly_price: 150000, cost_price: 0, billing_day: 1, contact: '', address: '' };
                    this.$dispatch('open-modal', 'customer-modal');
                },

                editCustomer(c) {
                    this.isEditingCust = true;
                    this.custForm = {
                        id: c.id,
                        name: c.name,
                        hotspot_user_id: c.hotspot_user_id || (c.hotspot_user ? c.hotspot_user.id : ''),
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
                    let totalPaid = 0;
                    let totalUnpaid = 0;
                    let paidCount = 0;
                    let partialCount = 0;
                    let unpaidCount = 0;

                    this.invoices.forEach(inv => {
                        totalPaid += (parseFloat(inv.amount_paid) || 0);
                        totalUnpaid += (parseFloat(inv.balance_due) || 0);
                        if (inv.status === 'paid') paidCount++;
                        else if (inv.status === 'partial') partialCount++;
                        else unpaidCount++;
                    });

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
