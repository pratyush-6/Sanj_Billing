@props(['variant' => 'info', 'title' => null])

@php
$variants = [
    'success' => ['wrap' => 'bg-emerald-50 border-emerald-200 text-emerald-800', 'icon' => 'text-emerald-500'],
    'warning' => ['wrap' => 'bg-amber-50 border-amber-200 text-amber-800', 'icon' => 'text-amber-500'],
    'danger' => ['wrap' => 'bg-rose-50 border-rose-200 text-rose-800', 'icon' => 'text-rose-500'],
    'info' => ['wrap' => 'bg-sky-50 border-sky-200 text-sky-800', 'icon' => 'text-sky-500'],
][$variant] ?? ['wrap' => 'bg-ink-50 border-ink-200 text-ink-800', 'icon' => 'text-ink-500'];
@endphp

<div {{ $attributes->class(['flex gap-3 rounded-xl border p-4 text-sm', $variants['wrap']]) }}>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 {{ $variants['icon'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
    </svg>
    <div class="min-w-0">
        @if ($title)
            <p class="font-medium">{{ $title }}</p>
        @endif
        <div class="{{ $title ? 'mt-0.5' : '' }}">{{ $slot }}</div>
    </div>
</div>
