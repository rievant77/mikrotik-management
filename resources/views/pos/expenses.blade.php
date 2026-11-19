<x-layouts.app>
    <x-slot:header>Pengeluaran Operasional POS</x-slot:header>

    <div class="space-y-6" x-data="expensesManager()">
        <!-- Header & Top Navigation Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Pengeluaran Operasional</h1>
                <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-300 mt-1">Pencatatan pengeluaran kas harian dan alokasi biaya bulanan tetap (ISP, listrik, operasional)</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Month Filter -->
                <div class="flex items-center gap-2 bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 rounded-lg p-1 shadow-xs">
                    <input 
                        type="month" 
                        x-model="selectedMonth" 
                        @change="changeMonth()" 
                        class="px-2.5 py-1 text-xs font-bold font-mono rounded bg-transparent dark:bg-transparent border-0 dark:border-0 text-zinc-900 dark:text-zinc-100 focus:ring-0"
                    >
                    <button type="button" @click="setThisMonth()" class="px-2.5 py-1 text-xs font-semibold rounded bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 transition-colors">
                        Bulan Ini
                    </button>
                </div>
            </div>
        </div>

        <!-- 3 Executive KPI Cards for Expenses -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <!-- 1. Total Pengeluaran Bulan Ini -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Total Beban Biaya</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-700">Periode Ini</span>
                    </div>
                    <div class="mt-2 text-xl sm:text-2xl font-bold text-rose-700 dark:text-rose-400 font-mono" x-text="formatRupiah(totalExpenses)"></div>
                </div>
                <div class="mt-3 pt-2 border-t border-zinc-200 dark:border-zinc-800/80 text-[11px] text-zinc-600 dark:text-zinc-400">
                    Total pengurang laba kotor di dashboard
                </div>
            </div>

            <!-- 2. Pengeluaran Harian -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Pengeluaran Harian</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700" x-text="dailyExpenses.length + ' Transaksi'"></span>
                    </div>
                    <div class="mt-2 text-xl sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono" x-text="formatRupiah(dailyTotal)"></div>
                </div>
                <div class="mt-3 pt-2 border-t border-zinc-200 dark:border-zinc-800/80 text-[11px] text-zinc-600 dark:text-zinc-400">
                    Biaya operasional ad-hoc & insidentil
                </div>
            </div>

            <!-- 3. Pengeluaran Bulanan Tetap -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 sm:p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Beban Tetap Bulanan</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-900 dark:text-blue-300 border border-blue-300 dark:border-blue-700" x-text="monthlyEntries.length + ' Pos Biaya'"></span>
                    </div>
                    <div class="mt-2 text-xl sm:text-2xl font-bold text-zinc-900 dark:text-zinc-100 font-mono" x-text="formatRupiah(monthlyTotal)"></div>
                </div>
                <div class="mt-3 pt-2 border-t border-zinc-200 dark:border-zinc-800/80 text-[11px] text-zinc-600 dark:text-zinc-400">
                    Biaya berulang (ISP, listrik, sewa tiang, dll)
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-4 border-b border-zinc-200 dark:border-zinc-800 text-sm">
            <button 
                @click="activeTab = 'daily'" 
                :class="activeTab === 'daily' ? 'border-rose-500 text-rose-600 dark:text-rose-400 font-bold' : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200 font-medium'"
                class="flex items-center gap-2 px-3 py-3 border-b-2 -mb-px transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Pengeluaran Harian</span>
                <span class="px-2 py-0.5 rounded-full text-xs border font-mono font-semibold" :class="activeTab === 'daily' ? 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border-rose-300 dark:border-rose-800' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700'" x-text="dailyExpenses.length"></span>
            </button>
            <button 
                @click="activeTab = 'monthly'" 
                :class="activeTab === 'monthly' ? 'border-rose-500 text-rose-600 dark:text-rose-400 font-bold' : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200 font-medium'"
                class="flex items-center gap-2 px-3 py-3 border-b-2 -mb-px transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Beban Tetap Bulanan</span>
                <span class="px-2 py-0.5 rounded-full text-xs border font-mono font-semibold" :class="activeTab === 'monthly' ? 'bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border-rose-300 dark:border-rose-800' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border-zinc-300 dark:border-zinc-700'" x-text="monthlyEntries.length"></span>
            </button>
        </div>

        <!-- TAB 1: PENGELUARAN HARIAN -->
        <div x-show="activeTab === 'daily'" class="space-y-4">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs overflow-hidden">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Buku Kas Pengeluaran Harian</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Catatan operasional dan belanja insidentil periode <span class="font-bold text-zinc-800 dark:text-zinc-200" x-text="periodLabel"></span></p>
                    </div>
                    <button @click="openDailyModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs shrink-0 self-start sm:self-auto">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ Catat Pengeluaran</span>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Deskripsi Pengeluaran</th>
                                <th class="py-3 px-4">Dicatat Oleh</th>
                                <th class="py-3 px-4 text-right">Nominal</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                            <template x-if="dailyExpenses.length === 0">
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                                        Belum ada catatan pengeluaran harian pada periode ini.
                                    </td>
                                </tr>
                            </template>
                            <template x-for="item in dailyExpenses" :key="item.id">
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="py-3 px-4 font-mono font-medium text-zinc-900 dark:text-zinc-100" x-text="formatDateIndo(item.expense_date)"></td>
                                    <td class="py-3 px-4 font-medium text-zinc-900 dark:text-zinc-100" x-text="item.description"></td>
                                    <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400" x-text="item.creator?.name || 'Kasir/Admin'"></td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-rose-700 dark:text-rose-400" x-text="formatRupiah(item.amount)"></td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button @click="editDailyExpense(item)" class="px-2.5 py-1 text-xs font-semibold rounded bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 transition-colors">
                                                Edit
                                            </button>
                                            <button @click="deleteDailyExpense(item)" class="px-2.5 py-1 text-xs font-semibold rounded bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-700 transition-colors">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-zinc-50 dark:bg-zinc-800/60 border-t border-zinc-200 dark:border-zinc-800 font-semibold">
                            <tr>
                                <td colspan="3" class="py-3 px-4 text-right text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">Subtotal Harian:</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-rose-700 dark:text-rose-400 text-sm" x-text="formatRupiah(dailyTotal)"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: BEBAN TETAP BULANAN -->
        <div x-show="activeTab === 'monthly'" class="space-y-6">
            <!-- Alert Information about Snapshot Isolation -->
            <div class="p-3.5 rounded-xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 text-xs text-blue-900 dark:text-blue-300 flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="leading-relaxed">
                    <strong>Prinsip Snapshot Independen:</strong> Setiap bulan memiliki salinan biaya tetapnya sendiri. Mengedit nominal pada tabel di bawah hanya akan mengubah bulan terpilih (<span class="font-bold underline" x-text="periodLabel"></span>) dan tidak mempengaruhi data bulan-bulan terdahulu. Master template digunakan otomatis saat membuka bulan baru.
                </div>
            </div>

            <!-- Table 1: Entries for Current Month -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs overflow-hidden">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Alokasi Biaya Bulan Ini: <span class="font-mono text-rose-600 dark:text-rose-400" x-text="periodLabel"></span></h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Daftar biaya tetap yang dibebankan pada pembukuan periode terpilih</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-3 px-4">Nama Pos Pengeluaran</th>
                                <th class="py-3 px-4">Catatan Khusus Bulan Ini</th>
                                <th class="py-3 px-4 text-right">Nominal Beban</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                            <template x-if="monthlyEntries.length === 0">
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                                        Belum ada master template biaya tetap yang aktif. Tambahkan master template di bawah.
                                    </td>
                                </tr>
                            </template>
                            <template x-for="entry in monthlyEntries" :key="entry.id">
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100" x-text="entry.name"></td>
                                    <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400" x-text="entry.notes || '-'"></td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="formatRupiah(entry.amount)"></td>
                                    <td class="py-3 px-4 text-center">
                                        <button @click="editMonthlyEntry(entry)" class="px-2.5 py-1 text-xs font-semibold rounded bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 transition-colors">
                                            Sesuaikan Nominal Bulan Ini
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-zinc-50 dark:bg-zinc-800/60 border-t border-zinc-200 dark:border-zinc-800 font-semibold">
                            <tr>
                                <td colspan="2" class="py-3 px-4 text-right text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[11px]">Subtotal Biaya Tetap:</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-rose-700 dark:text-rose-400 text-sm" x-text="formatRupiah(monthlyTotal)"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Table 2: Master Templates Manager -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs overflow-hidden">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Master Template Biaya Bulanan</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Template biaya default untuk bulan-bulan mendatang (ISP, listrik, dll)</p>
                    </div>
                    <button @click="openTemplateModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>+ Template Baru</span>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-3 px-4">Nama Template</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Nominal Default</th>
                                <th class="py-3 px-4 text-center">Aksi Master</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                            <template x-if="templates.length === 0">
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                                        Belum ada master template biaya. Klik "+ Template Baru" untuk mendaftarkan pos biaya ISP/listrik.
                                    </td>
                                </tr>
                            </template>
                            <template x-for="tmpl in templates" :key="tmpl.id">
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="py-3 px-4 font-bold text-zinc-900 dark:text-zinc-100" x-text="tmpl.name"></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="tmpl.is_active ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-300 dark:border-zinc-700'" x-text="tmpl.is_active ? 'Aktif' : 'Nonaktif'"></span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="formatRupiah(tmpl.amount)"></td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button @click="editTemplate(tmpl)" class="px-2.5 py-1 text-xs font-semibold rounded bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 transition-colors">
                                                Edit
                                            </button>
                                            <button @click="deleteTemplate(tmpl)" class="px-2.5 py-1 text-xs font-semibold rounded bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-700 transition-colors">
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

        <!-- MODAL 1: Tambah / Edit Pengeluaran Harian -->
        <x-modal name="daily-expense-modal" title="Form Pengeluaran Harian">
            <form @submit.prevent="saveDailyExpense()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Transaksi</label>
                    <input 
                        type="date" 
                        x-model="dailyForm.expense_date" 
                        required 
                        class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-rose-500 font-mono"
                    >
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Deskripsi / Keperluan</label>
                    <input 
                        type="text" 
                        x-model="dailyForm.description" 
                        required 
                        placeholder="Contoh: Beli kabel LAN 20m, Bensin teknisi, dll" 
                        class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-rose-500"
                    >
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nominal (Rp)</label>
                    <input 
                        type="number" 
                        x-model.number="dailyForm.amount" 
                        required 
                        min="1" 
                        step="1000" 
                        class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-rose-500 font-mono font-bold"
                    >
                </div>

                <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-2">
                    <button type="button" @click="$dispatch('close-modal', 'daily-expense-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 font-semibold hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 font-semibold transition-colors shadow-xs">
                        Simpan Pengeluaran
                    </button>
                </div>
            </form>
        </x-modal>

        <!-- MODAL 2: Edit Nominal Entri Bulan Ini (Snapshot) -->
        <x-modal name="monthly-entry-modal" title="Sesuaikan Biaya Tetap Bulan Ini">
            <form @submit.prevent="saveMonthlyEntry()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nama Pos Biaya</label>
                    <input 
                        type="text" 
                        :value="entryForm.name" 
                        disabled 
                        class="w-full px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-zinc-100 dark:bg-zinc-800/50 text-zinc-500 dark:text-zinc-400 font-bold"
                    >
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nominal Beban Bulan Ini (Rp)</label>
                    <input 
                        type="number" 
                        x-model.number="entryForm.amount" 
                        required 
                        min="0" 
                        step="1000" 
                        class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-rose-500 font-mono font-bold"
                    >
                    <p class="text-[11px] text-zinc-500 mt-1">Perubahan nominal ini hanya berlaku untuk bulan berjalan (<span x-text="periodLabel"></span>).</p>
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Catatan Khusus (Opsional)</label>
                    <textarea 
                        x-model="entryForm.notes" 
                        rows="2" 
                        placeholder="Contoh: Ada biaya tambahan bandwidth 10Mbps atau penyesuaian tarif listrik" 
                        class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-rose-500"
                    ></textarea>
                </div>

                <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-2">
                    <button type="button" @click="$dispatch('close-modal', 'monthly-entry-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 font-semibold hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 font-semibold transition-colors shadow-xs">
                        Simpan Penyesuaian
                    </button>
                </div>
            </form>
        </x-modal>

        <!-- MODAL 3: Tambah / Edit Master Template -->
        <x-modal name="template-modal" title="Form Master Template Biaya Bulanan">
            <form @submit.prevent="saveTemplate()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nama Pos Beban Tetap</label>
                    <input 
                        type="text" 
                        x-model="templateForm.name" 
                        required 
                        placeholder="Contoh: Dedicated ISP, Token Listrik Server, Sewa Tiang" 
                        class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-rose-500"
                    >
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nominal Default (Rp)</label>
                    <input 
                        type="number" 
                        x-model.number="templateForm.amount" 
                        required 
                        min="0" 
                        step="1000" 
                        class="w-full px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-rose-500 font-mono font-bold"
                    >
                    <p class="text-[11px] text-zinc-500 mt-1">Nominal ini otomatis dipakai saat sistem membuat snapshot untuk bulan baru.</p>
                </div>
                <div class="flex items-center gap-2">
                    <input 
                        type="checkbox" 
                        id="tmpl_active" 
                        x-model="templateForm.is_active" 
                        class="rounded border-zinc-300 dark:border-zinc-700 text-emerald-600 focus:ring-emerald-500"
                    >
                    <label for="tmpl_active" class="font-medium text-zinc-700 dark:text-zinc-300">Aktifkan template ini setiap bulan</label>
                </div>

                <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-2">
                    <button type="button" @click="$dispatch('close-modal', 'template-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700 font-semibold hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 font-semibold transition-colors shadow-xs">
                        Simpan Template
                    </button>
                </div>
            </form>
        </x-modal>
    </div>

    @push('scripts')
    <script>
        function expensesManager() {
            return {
                activeTab: 'daily',
                selectedMonth: '{{ $selectedMonth }}',
                periodLabel: '{{ $expenseData["period"] }}',
                dailyExpenses: @json($expenseData['daily_expenses']),
                monthlyEntries: @json($expenseData['monthly_entries']),
                templates: @json($templates),
                dailyTotal: {{ $expenseData['daily_total'] }},
                monthlyTotal: {{ $expenseData['monthly_total'] }},
                totalExpenses: {{ $expenseData['total_expenses'] }},

                // Forms
                isEditingDaily: false,
                dailyForm: { id: null, description: '', amount: 50000, expense_date: '{{ now()->format("Y-m-d") }}' },

                entryForm: { id: null, name: '', amount: 0, notes: '' },

                isEditingTemplate: false,
                templateForm: { id: null, name: '', amount: 500000, is_active: true },

                changeMonth() {
                    if (this.selectedMonth) {
                        window.location.href = `{{ route('pos.expenses') }}?month=${this.selectedMonth}`;
                    }
                },

                setThisMonth() {
                    const now = new Date();
                    const y = now.getFullYear();
                    const m = String(now.getMonth() + 1).padStart(2, '0');
                    this.selectedMonth = `${y}-${m}`;
                    this.changeMonth();
                },

                // Daily Handlers
                openDailyModal() {
                    this.isEditingDaily = false;
                    this.dailyForm = { 
                        id: null, 
                        description: '', 
                        amount: 50000, 
                        expense_date: '{{ now()->format("Y-m-d") }}' 
                    };
                    this.$dispatch('open-modal', 'daily-expense-modal');
                },

                editDailyExpense(item) {
                    this.isEditingDaily = true;
                    this.dailyForm = { 
                        id: item.id, 
                        description: item.description, 
                        amount: parseFloat(item.amount) || 0, 
                        expense_date: item.expense_date ? item.expense_date.split('T')[0] : '{{ now()->format("Y-m-d") }}' 
                    };
                    this.$dispatch('open-modal', 'daily-expense-modal');
                },

                saveDailyExpense() {
                    const url = this.isEditingDaily 
                        ? `/pos/expenses/daily/${this.dailyForm.id}` 
                        : '/pos/expenses/daily';
                    const method = this.isEditingDaily ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.dailyForm)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (this.isEditingDaily) {
                                const idx = this.dailyExpenses.findIndex(x => x.id === this.dailyForm.id);
                                if (idx !== -1) {
                                    this.dailyExpenses[idx] = { ...this.dailyExpenses[idx], ...data.expense };
                                }
                                window.showToast('Pengeluaran harian berhasil diperbarui', 'success');
                            } else {
                                this.dailyExpenses.unshift(data.expense);
                                window.showToast('Pengeluaran harian berhasil dicatat', 'success');
                            }
                            this.recalcTotals();
                            this.$dispatch('close-modal', 'daily-expense-modal');
                        } else {
                            window.showToast(data.message || 'Gagal menyimpan pengeluaran', 'error');
                        }
                    })
                    .catch(() => window.showToast('Terjadi kesalahan jaringan', 'error'));
                },

                deleteDailyExpense(item) {
                    if (!confirm(`Hapus pengeluaran "${item.description}" senilai ${this.formatRupiah(item.amount)}?`)) return;

                    fetch(`/pos/expenses/daily/${item.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.dailyExpenses = this.dailyExpenses.filter(x => x.id !== item.id);
                            this.recalcTotals();
                            window.showToast('Pengeluaran harian telah dihapus', 'info');
                        } else {
                            window.showToast(data.message || 'Gagal menghapus pengeluaran', 'error');
                        }
                    })
                    .catch(() => window.showToast('Terjadi kesalahan koneksi', 'error'));
                },

                // Monthly Entry Handlers
                editMonthlyEntry(entry) {
                    this.entryForm = {
                        id: entry.id,
                        name: entry.name,
                        amount: parseFloat(entry.amount) || 0,
                        notes: entry.notes || ''
                    };
                    this.$dispatch('open-modal', 'monthly-entry-modal');
                },

                saveMonthlyEntry() {
                    fetch(`/pos/expenses/entries/${this.entryForm.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            amount: this.entryForm.amount,
                            notes: this.entryForm.notes
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const idx = this.monthlyEntries.findIndex(x => x.id === this.entryForm.id);
                            if (idx !== -1) {
                                this.monthlyEntries[idx] = { ...this.monthlyEntries[idx], ...data.entry };
                            }
                            this.recalcTotals();
                            this.$dispatch('close-modal', 'monthly-entry-modal');
                            window.showToast('Penyesuaian biaya bulan ini berhasil disimpan', 'success');
                        } else {
                            window.showToast(data.message || 'Gagal menyimpan penyesuaian', 'error');
                        }
                    })
                    .catch(() => window.showToast('Terjadi kesalahan koneksi', 'error'));
                },

                // Template Handlers
                openTemplateModal() {
                    this.isEditingTemplate = false;
                    this.templateForm = { id: null, name: '', amount: 500000, is_active: true };
                    this.$dispatch('open-modal', 'template-modal');
                },

                editTemplate(tmpl) {
                    this.isEditingTemplate = true;
                    this.templateForm = {
                        id: tmpl.id,
                        name: tmpl.name,
                        amount: parseFloat(tmpl.amount) || 0,
                        is_active: Boolean(tmpl.is_active)
                    };
                    this.$dispatch('open-modal', 'template-modal');
                },

                saveTemplate() {
                    const url = this.isEditingTemplate 
                        ? `/pos/expenses/templates/${this.templateForm.id}` 
                        : '/pos/expenses/templates';
                    const method = this.isEditingTemplate ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.templateForm)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (this.isEditingTemplate) {
                                const idx = this.templates.findIndex(x => x.id === this.templateForm.id);
                                if (idx !== -1) {
                                    this.templates[idx] = { ...this.templates[idx], ...data.template };
                                }
                                window.showToast('Master template berhasil diperbarui', 'success');
                            } else {
                                this.templates.push(data.template);
                                window.showToast('Master template baru berhasil ditambahkan', 'success');
                                // Refresh to auto-populate entry if active
                                setTimeout(() => window.location.reload(), 1000);
                            }
                            this.$dispatch('close-modal', 'template-modal');
                        } else {
                            window.showToast(data.message || 'Gagal menyimpan template', 'error');
                        }
                    })
                    .catch(() => window.showToast('Terjadi kesalahan jaringan', 'error'));
                },

                deleteTemplate(tmpl) {
                    if (!confirm(`Hapus master template "${tmpl.name}"? Ini tidak akan menghapus entri yang sudah tercatat pada bulan lampau.`)) return;

                    fetch(`/pos/expenses/templates/${tmpl.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.templates = this.templates.filter(x => x.id !== tmpl.id);
                            window.showToast('Master template telah dihapus', 'info');
                        } else {
                            window.showToast(data.message || 'Gagal menghapus template', 'error');
                        }
                    })
                    .catch(() => window.showToast('Terjadi kesalahan koneksi', 'error'));
                },

                recalcTotals() {
                    let dTot = 0;
                    this.dailyExpenses.forEach(x => { dTot += (parseFloat(x.amount) || 0); });

                    let mTot = 0;
                    this.monthlyEntries.forEach(x => { mTot += (parseFloat(x.amount) || 0); });

                    this.dailyTotal = dTot;
                    this.monthlyTotal = mTot;
                    this.totalExpenses = dTot + mTot;
                },

                formatRupiah(num) {
                    return window.formatRupiah ? window.formatRupiah(num || 0) : 'Rp ' + Number(num || 0).toLocaleString('id-ID');
                },

                formatDateIndo(dateStr) {
                    if (!dateStr) return '-';
                    const parts = dateStr.split('T')[0].split('-');
                    if (parts.length === 3) {
                        return `${parts[2]}/${parts[1]}/${parts[0]}`;
                    }
                    return dateStr;
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
