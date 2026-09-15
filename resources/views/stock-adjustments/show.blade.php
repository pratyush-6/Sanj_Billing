<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="'Stock Adjustment '.$adjustment->adjustment_number">
            <x-slot name="actions">
                <a href="{{ route('stock-adjustments.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Stock Adjustments</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="max-w-3xl space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        <x-ui.card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 flex-1">
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Product</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $adjustment->product->name }} ({{ $adjustment->product->sku }})</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Type</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $adjustment->type }} by {{ number_format($adjustment->quantity, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Reason</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $adjustment->reason }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Adjustment Date</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $adjustment->adjustment_date->format('d-M-Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Created By</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $adjustment->creator?->name ?? '—' }}</div>
                    </div>
                </div>
                <x-ui.badge :variant="match($adjustment->status) { 'Approved' => 'success', 'Rejected' => 'danger', default => 'neutral' }" class="text-sm">{{ $adjustment->status }}</x-ui.badge>
            </div>

            @if ($adjustment->notes)
                <p class="text-sm text-ink-600 dark:text-ink-300 mt-4 border-t border-ink-100 dark:border-ink-800 pt-4">{{ $adjustment->notes }}</p>
            @endif

            @if ($adjustment->status !== 'Pending')
                <div class="text-sm text-ink-600 dark:text-ink-300 mt-4 border-t border-ink-100 dark:border-ink-800 pt-4">
                    {{ $adjustment->status }} by {{ $adjustment->approver?->name ?? 'Unknown' }} on {{ $adjustment->approved_at?->format('d-M-Y H:i') }}
                    @if ($adjustment->approval_comments)
                        <p class="mt-1">"{{ $adjustment->approval_comments }}"</p>
                    @endif
                </div>
            @endif

            @can('stock-adjustments.approve')
                @if ($adjustment->status === 'Pending' && $adjustment->created_by !== auth()->id())
                    <div class="flex flex-wrap items-center gap-3 mt-6 border-t border-ink-100 dark:border-ink-800 pt-4">
                        <form method="POST" action="{{ route('stock-adjustments.decide', $adjustment) }}">
                            @csrf
                            <input type="hidden" name="decision" value="Approved">
                            <x-primary-button type="submit">Approve</x-primary-button>
                        </form>
                        <form method="POST" action="{{ route('stock-adjustments.decide', $adjustment) }}" onsubmit="return confirm('Reject this stock adjustment?');">
                            @csrf
                            <input type="hidden" name="decision" value="Rejected">
                            <x-danger-button type="submit">Reject</x-danger-button>
                        </form>
                    </div>
                @elseif ($adjustment->status === 'Pending')
                    <p class="text-xs text-ink-400 dark:text-ink-500 mt-6 border-t border-ink-100 dark:border-ink-800 pt-4">You submitted this adjustment, so you cannot approve or reject it yourself.</p>
                @endif
            @endcan
        </x-ui.card>
    </div>
</x-app-layout>
