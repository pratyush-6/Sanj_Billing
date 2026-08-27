@props(['padded' => true])

<div {{ $attributes->class(['bg-white dark:bg-ink-900 rounded-xl border border-ink-200/70 dark:border-ink-700/70 shadow-sm overflow-hidden', 'p-6' => $padded]) }}>
    {{ $slot }}
</div>
