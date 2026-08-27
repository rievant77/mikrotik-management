<x-layouts.app>
    <x-slot:header>Role & Hak Akses (RBAC)</x-slot:header>

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">Matrix Hak Akses (RBAC)</h1>
            <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Struktur perizinan hak akses pengguna berdasarkan PRD Section 13</p>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-50/80 dark:bg-zinc-800/50 text-[11px] font-semibold text-zinc-400 uppercase border-b border-zinc-200 dark:border-zinc-800">
                        <tr>
                            <th class="py-3.5 px-4">Fitur / Modul</th>
                            <th class="py-3.5 px-4 text-center">Admin (Owner)</th>
                            <th class="py-3.5 px-4 text-center">Kasir (Operator)</th>
                            <th class="py-3.5 px-4 text-center">Teknisi (NOC)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">Live Dashboard & Charts</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                        </tr>
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">Router Settings & Diagnostics</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                            <td class="py-3 px-4 text-center text-rose-500">❌</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                        </tr>
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">Generate Voucher Hotspot</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                            <td class="py-3 px-4 text-center text-rose-500">❌</td>
                        </tr>
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">Kelola Hotspot Profile</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                            <td class="py-3 px-4 text-center text-rose-500">❌</td>
                            <td class="py-3 px-4 text-center text-rose-500">❌</td>
                        </tr>
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">Input Pembayaran Bulanan</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                            <td class="py-3 px-4 text-center text-rose-500">❌</td>
                        </tr>
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">Void / Batalkan Pembayaran</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                            <td class="py-3 px-4 text-center text-rose-500">❌</td>
                            <td class="py-3 px-4 text-center text-rose-500">❌</td>
                        </tr>
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">Cashier Shift (Buka / Tutup)</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                            <td class="py-3 px-4 text-center text-rose-500">❌</td>
                        </tr>
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                            <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">Audit Trail Logs</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                            <td class="py-3 px-4 text-center text-rose-500">❌</td>
                            <td class="py-3 px-4 text-center text-emerald-600">✅</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
