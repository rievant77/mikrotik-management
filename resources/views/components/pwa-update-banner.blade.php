<div
    x-data="{ updateReady: false }"
    @pwa-update-available.window="updateReady = true"
    x-show="updateReady"
    x-cloak
    class="fixed bottom-20 lg:bottom-6 left-4 right-4 sm:left-6 sm:right-auto z-50 max-w-sm no-print"
    role="status"
    aria-live="polite"
>
    <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl shadow-lg border bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 border-zinc-800 dark:border-zinc-200 text-xs font-medium">
        <span>Pembaruan aplikasi tersedia.</span>
        <div class="flex items-center gap-2 shrink-0">
            <button
                type="button"
                @click="window.applyPwaUpdate?.()"
                class="px-2.5 py-1 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white font-semibold transition-colors"
            >
                Muat Ulang
            </button>
            <button
                type="button"
                @click="updateReady = false"
                class="p-1 text-zinc-400 hover:text-white dark:text-zinc-500 dark:hover:text-zinc-900"
                aria-label="Tutup pemberitahuan pembaruan"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>
<p id="pwa-install-status" role="status" aria-live="polite" class="sr-only"></p>
