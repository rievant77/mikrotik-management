@props(['variant' => 'compact'])

@php
    $classes = match ($variant) {
        'full' => 'hidden w-full items-center justify-center gap-2 px-3.5 py-2.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 aria-disabled:opacity-60 aria-disabled:cursor-wait',
        default => 'hidden items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 aria-disabled:opacity-60 aria-disabled:cursor-wait',
    };
@endphp

<button
    type="button"
    data-pwa-install
    hidden
    {{ $attributes->merge(['class' => $classes]) }}
    title="Pasang Aplikasi ke Perangkat"
>
    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
    </svg>
    <span class="{{ $variant === 'compact' ? 'hidden sm:inline' : '' }}">Install App</span>
</button>
