@props(['title' => null, 'subtitle' => null, 'actions' => null, 'padding' => 'p-5 sm:p-6', 'class' => ''])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs transition-all ' . $class]) }}>
    @if ($title || $actions)
        <div class="px-5 py-4 sm:px-6 border-b border-zinc-100 dark:border-zinc-800/80 flex items-center justify-between flex-wrap gap-2">
            <div>
                @if ($title)
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if ($actions)
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>
