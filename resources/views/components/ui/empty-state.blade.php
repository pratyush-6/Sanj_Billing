@props(['title' => 'Nothing here yet', 'description' => null])

<div {{ $attributes->class(['flex flex-col items-center justify-center text-center py-14 px-6']) }}>
    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-ink-100 dark:bg-ink-800 text-ink-400 dark:text-ink-500 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 13.5h6m-6-3h6m-7.5 9h9a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0016.5 4.5h-9A2.25 2.25 0 005.25 6.75v10.5A2.25 2.25 0 007.5 19.5z" />
        </svg>
    </div>
    <p class="text-sm font-medium text-ink-700 dark:text-ink-200">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 text-sm text-ink-400 dark:text-ink-500 max-w-sm">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-4">{{ $action }}</div>
    @endisset
</div>
