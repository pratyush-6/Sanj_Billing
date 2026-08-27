@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-white dark:bg-ink-800 border-ink-300 dark:border-ink-600 focus:border-brand-500 focus:ring-brand-500 rounded-lg shadow-sm text-sm text-ink-900 dark:text-ink-100 placeholder:text-ink-400 dark:placeholder:text-ink-500 disabled:bg-ink-50 dark:disabled:bg-ink-900 disabled:text-ink-500']) }}>
