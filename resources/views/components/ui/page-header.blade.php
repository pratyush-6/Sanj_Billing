@props(['title', 'description' => null])

<div {{ $attributes->class(['flex flex-wrap items-center justify-between gap-4']) }}>
    <div>
        <h1 class="text-xl font-bold text-ink-900 dark:text-ink-50 tracking-tight">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
