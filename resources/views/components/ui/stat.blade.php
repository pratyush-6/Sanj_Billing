@props(['label', 'value', 'hint' => null, 'accent' => 'brand'])

@php
$accents = [
    'brand' => 'text-brand-600 bg-brand-50',
    'emerald' => 'text-emerald-600 bg-emerald-50',
    'amber' => 'text-amber-600 bg-amber-50',
    'rose' => 'text-rose-600 bg-rose-50',
    'sky' => 'text-sky-600 bg-sky-50',
];
@endphp

<div {{ $attributes->class(['rounded-xl border border-ink-200/70 bg-white p-5']) }}>
    <div class="flex items-center gap-3">
        @isset($icon)
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $accents[$accent] ?? $accents['brand'] }}">
                {{ $icon }}
            </div>
        @endisset
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wide text-ink-500 truncate">{{ $label }}</p>
            <p class="text-xl font-bold text-ink-900 truncate">{{ $value }}</p>
        </div>
    </div>
    @if ($hint)
        <p class="mt-2 text-xs text-ink-400">{{ $hint }}</p>
    @endif
</div>
