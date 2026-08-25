@props(['compact' => false])

<div {{ $attributes->class(['flex items-center gap-2.5']) }}>
    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white font-bold text-base shadow-sm">S</span>
    @unless ($compact)
        <span class="text-lg font-bold tracking-tight text-ink-900">Sanjeevani</span>
    @endunless
</div>
