@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-ink-300 focus:border-brand-500 focus:ring-brand-500 rounded-lg shadow-sm text-sm text-ink-900 disabled:bg-ink-50 disabled:text-ink-500']) }}>
