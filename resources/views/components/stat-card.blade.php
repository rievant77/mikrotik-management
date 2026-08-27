@props(['title', 'value', 'change' => null, 'icon' => null, 'color' => 'zinc'])

<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 shadow-xs flex flex-col justify-between hover:border-zinc-300 dark:hover:border-zinc-700 transition-colors">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ $title }}</p>
            <h4 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1.5 tracking-tight">{{ $value }}</h4>
        </div>
        @if ($icon)
            <div class="p-2.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-lg border border-zinc-200 dark:border-zinc-700">
                {{ $icon }}
            </div>
        @endif
    </div>
    @if ($change || isset($footer))
        <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between text-xs">
            @if ($change)
                <span class="text-zinc-500 dark:text-zinc-400">{{ $change }}</span>
            @endif
            @if (isset($footer))
                {{ $footer }}
            @endif
        </div>
    @endif
</div>
