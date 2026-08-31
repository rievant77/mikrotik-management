<x-layouts.app>
    <x-slot:header>User Bulanan & Tagihan</x-slot:header>

    <div class="space-y-6" x-data="monthlyManager()">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Customer & Tagihan Bulanan</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Kelola data pelanggan bulanan, tarif kustom, dan input transaksi pembayaran manual</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openCustomerModal()" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white dark:bg-zinc-100 dark:hover:bg-zinc-200 dark:text-zinc-900 transition-colors shadow-xs">
                    + Tambah Customer
                </button>
                <button @click="openPaymentModal()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 transition-colors shadow-xs">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Input Bayar Manual</span>
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <template x-if="filteredCustomers.length === 0">
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="mt-4 font-bold text-zinc-900 dark:text-zinc-100 text-sm">Belum Ada Pelanggan Bulanan</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Daftarkan pelanggan bulanan untuk mengatur tarif kustom dan mencatat riwayat pembayaran tagihan bulanan.</p>
                <button @click="openCustomerModal()" class="mt-4 inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900">
                    + Tambah Pelanggan Pertama
                </button>
            </div>
        </template>

        <!-- Customers Table Card -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs" x-show="filteredCustomers.length > 0">
            <div class="p-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                <input type="text" x-model="search" placeholder="Cari nama customer..." class="px-3 py-1.5 text-xs rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 w-64">
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3.5 px-4">Nama Customer</th>
                            <th class="py-3.5 px-4">Username Hotspot</th>
                            <th class="py-3.5 px-4">Kontak / Alamat</th>
                            <th class="py-3.5 px-4">Tarif Bulanan</th>
                            <th class="py-3.5 px-4">Status Periode Ini</th>
                            <th class="py-3.5 px-4">Tanggal Bayar</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <template x-for="c in filteredCustomers" :key="c.id">
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100" x-text="c.name"></td>
                                <td class="py-3.5 px-4 font-mono text-zinc-600 dark:text-zinc-400" x-text="c.username || c.hotspot_user?.username || '-'"></td>
                                <td class="py-3.5 px-4 text-zinc-500">
                                    <div x-text="c.contact || c.phone || '-'"></div>
                                    <div class="text-[10px] text-zinc-400" x-text="c.address || ''"></div>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="formatRupiah(c.monthly_price || c.price || 0)"></td>
                                <td class="py-3.5 px-4">
                                    <span 
                                        :class="c.status === 'PAID' ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/20' : (c.status === 'PARTIAL' ? 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/20' : (c.status === 'VOID' ? 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20' : 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-500/20'))" 
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="c.status === 'PAID' ? 'bg-emerald-500' : (c.status === 'PARTIAL' ? 'bg-amber-500' : (c.status === 'VOID' ? 'bg-zinc-400' : 'bg-rose-500'))"></span>
                                        <span x-text="c.status || 'UNPAID'"></span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-500" x-text="c.paid_at || 'Belum dibayar'"></td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <template x-if="c.status !== 'PAID'">
                                            <button @click="quickPay(c)" class="px-2.5 py-1 text-xs font-semibold rounded bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900">
                                                Bayar
                                            </button>
                                        </template>
                                        <template x-if="c.status === 'PAID'">
                                            <button @click="voidPayment(c)" class="px-2 py-1 text-xs font-medium rounded text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100">
                                                Void
                                            </button>
                                        </template>
                                        <button @click="editCustomer(c)" class="px-2 py-1 rounded text-xs font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700">
                                            Edit
                                        </button>
                                        <button @click="deleteCustomer(c)" class="px-2 py-1 rounded text-xs font-medium text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100">
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

        <!-- Add/Edit Customer Modal -->
        <x-modal name="customer-modal" title="Form Pelanggan Bulanan">
            <form @submit.prevent="saveCustomer()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nama Customer *</label>
                    <input type="text" x-model="custForm.name" required class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Username Hotspot (Bila Ada)</label>
                        <input type="text" x-model="custForm.username" placeholder="e.g. budi-santoso" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tarif Bulanan Kustom *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-semibold text-zinc-400 select-none">Rp</span>
                            <input 
                                type="text" 
                                inputmode="numeric"
                                :value="formatNumber(custForm.monthly_price || custForm.price)" 
                                @input="custForm.monthly_price = parseNumber($event.target.value); custForm.price = parseNumber($event.target.value); $event.target.value = formatNumber(custForm.monthly_price)" 
                                required 
                                class="w-full pl-9 pr-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono font-bold" 
                                placeholder="100.000"
                            >
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nomor Kontak / WhatsApp</label>
                        <input type="text" x-model="custForm.phone" placeholder="0812..." class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Alamat / Lokasi</label>
                        <input type="text" x-model="custForm.address" placeholder="Blok / No Rumah" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="$dispatch('close-modal', 'customer-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-semibold" x-text="isEditingCust ? 'Perbarui Customer' : 'Simpan Customer'"></button>
                </div>
            </form>
        </x-modal>

        <!-- Input Pembayaran Manual Modal -->
        <x-modal name="payment-input-modal" title="Input Pembayaran Tagihan Bulanan">
            <form @submit.prevent="submitPayment()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Pilih Customer *</label>
                    <select x-model="payForm.customerId" @change="onCustomerSelect()" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                        <template x-for="c in customers" :key="c.id">
                            <option :value="c.id" x-text="c.name + ' (' + formatRupiah(c.monthly_price || c.price || 0) + ')'"></option>
                        </template>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Periode Tagihan *</label>
                        <select x-model="payForm.billingMonth" class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                            <option value="2026-08">Agustus 2026</option>
                            <option value="2026-09">September 2026</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Bayar *</label>
                        <input type="date" x-model="payForm.paidAt" required class="w-full px-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tarif Standar</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-semibold text-zinc-400 select-none">Rp</span>
                            <input type="text" :value="formatNumber(payForm.monthlyPrice)" readonly class="w-full pl-9 pr-3 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-700/50 border border-zinc-200 dark:border-zinc-700 text-zinc-500 font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nominal Dibayar *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-semibold text-zinc-400 select-none">Rp</span>
                            <input 
                                type="text" 
                                inputmode="numeric"
                                :value="formatNumber(payForm.amountPaid)" 
                                @input="payForm.amountPaid = parseNumber($event.target.value); $event.target.value = formatNumber(payForm.amountPaid)" 
                                required 
                                class="w-full pl-9 pr-3 py-2 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 font-mono font-bold text-sm" 
                                placeholder="100.000"
                            >
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-zinc-700 dark:text-zinc-300 mb-1">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="payForm.method = 'cash'" :class="payForm.method === 'cash' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300'" class="py-2 rounded-lg text-center transition-colors">
                            Tunai (Cash)
                        </button>
                        <button type="button" @click="payForm.method = 'transfer'" :class="payForm.method === 'transfer' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300'" class="py-2 rounded-lg text-center transition-colors">
                            Transfer Bank
                        </button>
                        <button type="button" @click="payForm.method = 'qris'" :class="payForm.method === 'qris' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300'" class="py-2 rounded-lg text-center transition-colors">
                            QRIS
                        </button>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" @click="$dispatch('close-modal', 'payment-input-modal')" class="px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-semibold">Simpan Pembayaran</button>
                </div>
            </form>
        </x-modal>
    </div>

    @push('scripts')
    <script>
        function monthlyManager() {
            return {
                search: '',
                isEditingCust: false,
                customers: @json($customers ?? []),
                custForm: { id: null, name: '', username: '', price: 150000, phone: '', address: '' },
                payForm: { customerId: null, billingMonth: '2026-08', paidAt: '', monthlyPrice: 0, amountPaid: 0, method: 'cash' },
                get filteredCustomers() {
                    return this.customers.filter(c => !this.search || c.name.toLowerCase().includes(this.search.toLowerCase()));
                },
                openCustomerModal() {
                    this.isEditingCust = false;
                    this.custForm = { id: null, name: '', username: '', monthly_price: 150000, contact: '', address: '' };
                    this.$dispatch('open-modal', 'customer-modal');
                },
                editCustomer(c) {
                    this.isEditingCust = true;
                    this.custForm = {
                        id: c.id,
                        name: c.name,
                        username: c.username || (c.hotspot_user?.username || ''),
                        monthly_price: c.monthly_price || c.price || 150000,
                        contact: c.contact || c.phone || '',
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
                                window.showToast(`Data customer ${this.custForm.name} berhasil diperbarui!`, 'success');
                            } else {
                                this.customers.unshift(data.customer);
                                window.showToast(`Customer baru ${data.customer.name} berhasil ditambahkan!`, 'success');
                            }
                            this.$dispatch('close-modal', 'customer-modal');
                        } else {
                            window.showToast(data.message || 'Gagal menyimpan customer', 'error');
                        }
                    })
                    .catch(err => {
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },
                deleteCustomer(c) {
                    if (confirm(`Apakah Anda yakin ingin menghapus data customer "${c.name}"?`)) {
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
                                window.showToast(`Customer ${c.name} telah dihapus`, 'info');
                            } else {
                                window.showToast(data.message || 'Gagal menghapus customer', 'error');
                            }
                        })
                        .catch(err => {
                            window.showToast('Gagal menghapus customer', 'error');
                        });
                    }
                },
                voidPayment(c) {
                    const payment = c.payments && c.payments.length > 0 ? c.payments[0] : null;
                    if (!payment) {
                        window.showToast('Tidak ada data transaksi pembayaran yang bisa di-VOID', 'info');
                        return;
                    }

                    if (confirm(`Batalkan / VOID status pembayaran ${c.name} untuk periode ini?`)) {
                        fetch(`/pos/monthly/payments/${payment.id}/void`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ reason: 'Pembatalan kasir' })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                c.status = 'VOID';
                                c.paid_at = null;
                                window.showToast(`Pembayaran ${c.name} berhasil di-VOID`, 'info');
                            } else {
                                window.showToast(data.message || 'Gagal VOID pembayaran', 'error');
                            }
                        })
                        .catch(err => {
                            window.showToast('Gagal memproses VOID', 'error');
                        });
                    }
                },
                openPaymentModal() {
                    this.payForm.paidAt = new Date().toISOString().split('T')[0];
                    this.$dispatch('open-modal', 'payment-input-modal');
                },
                quickPay(c) {
                    this.payForm.customerId = c.id;
                    const price = c.monthly_price || c.price || 0;
                    this.payForm.monthlyPrice = price;
                    this.payForm.amountPaid = price;
                    this.openPaymentModal();
                },
                onCustomerSelect() {
                    const c = this.customers.find(item => item.id == this.payForm.customerId);
                    if (c) {
                        const price = c.monthly_price || c.price || 0;
                        this.payForm.monthlyPrice = price;
                        this.payForm.amountPaid = price;
                    }
                },
                submitPayment() {
                    fetch('/pos/monthly/payments', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            customer_id: this.payForm.customerId,
                            billing_month: this.payForm.billingMonth,
                            amount_paid: this.payForm.amountPaid,
                            paid_at: this.payForm.paidAt,
                            payment_method: this.payForm.method
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const c = this.customers.find(item => item.id == this.payForm.customerId);
                            if (c) {
                                c.status = 'PAID';
                                c.paid_at = this.payForm.paidAt;
                                c.payments = [data.payment];
                            }
                            this.$dispatch('close-modal', 'payment-input-modal');
                            window.showToast(`Pembayaran bulanan berhasil dicatat`, 'success');
                        } else {
                            window.showToast(data.message || 'Gagal mencatat pembayaran', 'error');
                        }
                    })
                    .catch(err => {
                        window.showToast('Terjadi kesalahan koneksi', 'error');
                    });
                },
                formatRupiah(num) {
                    return window.formatRupiah(num || 0);
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
