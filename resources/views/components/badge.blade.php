@props(['type' => 'default', 'dot' => false])

@php
$classes = match($type) {
    'online', 'active', 'paid', 'success' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/20',
    'degraded', 'partial', 'warning' => 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/20',
    'unreachable', 'unpaid', 'void', 'danger', 'expired' => 'bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-500/20',
    'disabled', 'neutral', 'gray' => 'bg-zinc-500/10 text-zinc-700 dark:text-zinc-300 border-zinc-500/20',
    'blue', 'info', 'primary' => 'bg-sky-500/10 text-sky-700 dark:text-sky-400 border-sky-500/20',
    default => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border-zinc-200 dark:border-zinc-700'
};

$dotColor = match($type) {
    'online', 'active', 'paid', 'success' => 'bg-emerald-500 animate-pulse',
    'degraded', 'partial', 'warning' => 'bg-amber-500',
    'unreachable', 'unpaid', 'void', 'danger', 'expired' => 'bg-rose-500',
    default => 'bg-zinc-400'
};
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium border ' . $classes]) }}>
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
    @endif
    {{ $slot }}
</span>
