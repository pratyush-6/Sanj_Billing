@php($depth = $depth ?? 0)

<div class="flex items-center justify-between px-4 py-2.5 text-sm" style="padding-left: {{ 1 + $depth }}rem">
    <span @class(['text-ink-900 font-semibold' => $depth === 0, 'text-ink-700' => $depth > 0])>{{ $node->account->name }}</span>
    <span @class(['font-medium', 'text-ink-900' => $depth > 0, 'text-ink-500' => $depth === 0])>
        {{ $node->children->isEmpty() ? number_format($node->balance, 2) : '' }}
    </span>
</div>

@foreach ($node->children as $child)
    @include('accounting.chart-of-accounts._node', ['node' => $child, 'depth' => $depth + 1])
@endforeach
