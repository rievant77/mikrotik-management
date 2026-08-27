<button 
    type="button"
    @click="window.themeManager.toggle()" 
    x-data="{ isDark: document.documentElement.classList.contains('dark') }"
    @theme-changed.window="isDark = $event.detail.isDark"
    class="p-2 text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 rounded-lg hover:bg-zinc-200/70 dark:hover:bg-zinc-800 transition-colors focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-600 border border-zinc-200 dark:border-zinc-700/80 bg-zinc-50 dark:bg-zinc-850"
    title="Ganti Mode Terang / Gelap"
    aria-label="Toggle Theme"
>
    <!-- Sun icon (shown when dark mode is active) -->
    <svg x-show="isDark" x-cloak class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
    <!-- Moon icon (shown when light mode is active) -->
    <svg x-show="!isDark" x-cloak class="w-4 h-4 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>
</button>
