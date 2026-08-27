<x-layouts.app>
    <x-slot:header>Audit Log</x-slot:header>

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Audit Trail & Log Aktivitas</h1>
            <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Rekam jejak seluruh perubahan konfigurasi, transaksi, dan aktivitas sistem</p>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3.5 px-4">Waktu</th>
                            <th class="py-3.5 px-4">User / Operator</th>
                            <th class="py-3.5 px-4">Aktivitas (Action)</th>
                            <th class="py-3.5 px-4">Entitas Target</th>
                            <th class="py-3.5 px-4">IP Address</th>
                            <th class="py-3.5 px-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="py-3.5 px-4 font-mono text-zinc-500">25 Ags 2026, 09:12</td>
                            <td class="py-3.5 px-4 font-semibold text-zinc-900 dark:text-zinc-100">System (Collector)</td>
                            <td class="py-3.5 px-4 font-mono">voucher_activated</td>
                            <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-300">Voucher: VC-89241</td>
                            <td class="py-3.5 px-4 font-mono text-zinc-400">127.0.0.1</td>
                            <td class="py-3.5 px-4 text-right"><x-badge type="success">Success</x-badge></td>
                        </tr>
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="py-3.5 px-4 font-mono text-zinc-500">25 Ags 2026, 08:45</td>
                            <td class="py-3.5 px-4 font-semibold text-zinc-900 dark:text-zinc-100">Admin (Kaligata)</td>
                            <td class="py-3.5 px-4 font-mono">monthly_payment_created</td>
                            <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-300">Customer: Budi Santoso (Rp150.000)</td>
                            <td class="py-3.5 px-4 font-mono text-zinc-400">192.168.88.10</td>
                            <td class="py-3.5 px-4 text-right"><x-badge type="success">Success</x-badge></td>
                        </tr>
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="py-3.5 px-4 font-mono text-zinc-500">25 Ags 2026, 08:00</td>
                            <td class="py-3.5 px-4 font-semibold text-zinc-900 dark:text-zinc-100">Admin (Kaligata)</td>
                            <td class="py-3.5 px-4 font-mono">cashier_shift_opened</td>
                            <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-300">Shift #104 (Modal: Rp200.000)</td>
                            <td class="py-3.5 px-4 font-mono text-zinc-400">192.168.88.10</td>
                            <td class="py-3.5 px-4 text-right"><x-badge type="success">Success</x-badge></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
