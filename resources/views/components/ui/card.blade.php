@props(['padded' => true])

<div {{ $attributes->class(['bg-white rounded-xl border border-ink-200/70 shadow-sm overflow-hidden', 'p-6' => $padded]) }}>
    {{ $slot }}
</div>
