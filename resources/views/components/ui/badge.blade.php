@props(['variant' => 'neutral'])

@php
$variants = [
    'success' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-400/30',
    'warning' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-400/30',
    'danger' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-400/30',
    'info' => 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-400/30',
    'brand' => 'bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-600/20 dark:bg-brand-500/10 dark:text-brand-400 dark:ring-brand-400/30',
    'neutral' => 'bg-ink-100 text-ink-600 ring-1 ring-inset ring-ink-500/10 dark:bg-ink-800 dark:text-ink-300 dark:ring-ink-400/10',
];
@endphp

<span {{ $attributes->class(['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium', $variants[$variant] ?? $variants['neutral']]) }}>
    {{ $slot }}
</span>
