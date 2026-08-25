@props(['variant' => 'neutral'])

@php
$variants = [
    'success' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20',
    'warning' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20',
    'danger' => 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20',
    'info' => 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-600/20',
    'brand' => 'bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-600/20',
    'neutral' => 'bg-ink-100 text-ink-600 ring-1 ring-inset ring-ink-500/10',
];
@endphp

<span {{ $attributes->class(['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium', $variants[$variant] ?? $variants['neutral']]) }}>
    {{ $slot }}
</span>
