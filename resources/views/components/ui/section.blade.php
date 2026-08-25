@props(['title', 'description' => null])

<div {{ $attributes }}>
    <div class="mb-4">
        <h3 class="text-sm font-semibold text-ink-800">{{ $title }}</h3>
        @if ($description)
            <p class="text-xs text-ink-400 mt-0.5">{{ $description }}</p>
        @endif
    </div>
    {{ $slot }}
</div>
