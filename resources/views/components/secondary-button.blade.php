<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-ink-800 border border-ink-300 dark:border-ink-600 rounded-lg font-semibold text-sm text-ink-700 dark:text-ink-200 shadow-sm hover:bg-ink-50 dark:hover:bg-ink-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-ink-900 disabled:opacity-40 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
