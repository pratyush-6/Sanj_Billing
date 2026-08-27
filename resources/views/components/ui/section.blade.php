@props(['title', 'description' => null])

<div {{ $attributes }}>
    <div class="mb-4">
        <h3 class="text-sm font-semibold text-ink-800 dark:text-ink-100">{{ $title }}</h3>
        @if ($description)
            <p class="text-xs text-ink-400 dark:text-ink-500 mt-0.5">{{ $description }}</p>
        @endif
    </div>
    {{ $slot }}
</div>
