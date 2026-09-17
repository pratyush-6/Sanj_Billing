<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="'Delivery Challan '.$challan->challan_number">
            <x-slot name="actions">
                <a href="{{ route('delivery-challans.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Delivery Challans</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="max-w-4xl space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        @if ($challan->saleOrder)
            <x-ui.alert variant="info">
                Converted from sale order
                <a href="{{ route('sale-orders.show', $challan->saleOrder) }}" class="underline font-medium">{{ $challan->saleOrder->order_number }}</a>.
            </x-ui.alert>
        @endif

        <x-ui.card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 flex-1">
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Customer</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $challan->party->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Challan Date</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $challan->challan_date->format('d-M-Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Created By</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $challan->creator?->name ?? '—' }}</div>
                    </div>
                </div>
                <x-ui.badge :variant="match($challan->status) {
                    'Completed' => 'success',
                    'Partially Invoiced' => 'warning',
                    'Invoiced' => 'brand',
                    'Cancelled' => 'danger',
                    default => 'neutral',
                }" class="text-sm">{{ $challan->status }}</x-ui.badge>
            </div>

            @if ($challan->notes)
                <p class="text-sm text-ink-600 dark:text-ink-300 mt-4 border-t border-ink-100 dark:border-ink-800 pt-4">{{ $challan->notes }}</p>
            @endif

            <div class="flex flex-wrap items-center gap-3 mt-6 border-t border-ink-100 dark:border-ink-800 pt-4">
                <a href="{{ route('delivery-challans.print', $challan) }}" target="_blank">
                    <x-secondary-button>Print</x-secondary-button>
                </a>
                @can('delivery-challans.manage')
                    @if ($challan->status === 'Draft')
                        <a href="{{ route('delivery-challans.edit', $challan) }}">
                            <x-secondary-button>Edit</x-secondary-button>
                        </a>
                        <form method="POST" action="{{ route('delivery-challans.complete', $challan) }}" onsubmit="return confirm('Complete this delivery challan? Stock will be reduced and it can no longer be edited.');">
                            @csrf
                            <x-primary-button type="submit">Complete</x-primary-button>
                        </form>
                        <form method="POST" action="{{ route('delivery-challans.cancel', $challan) }}" onsubmit="return confirm('Cancel this delivery challan?');">
                            @csrf
                            <x-danger-button type="submit">Cancel</x-danger-button>
                        </form>
                    @endif
                @endcan
                @can('sale-invoices.manage')
                    @if (in_array($challan->status, ['Completed', 'Partially Invoiced']) && Route::has('sale-invoices.create'))
                        <a href="{{ route('sale-invoices.create', ['from_challan' => $challan->id]) }}">
                            <x-secondary-button>Create Invoice</x-secondary-button>
                        </a>
                    @endif
                @endcan
            </div>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/80 dark:bg-ink-800/80">
                <h3 class="text-sm font-semibold text-ink-800 dark:text-ink-100">Line Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($challan->items as $item)
                            <tr>
                                <td class="px-4 py-3.5 text-sm text-ink-900 dark:text-ink-50">{{ $item->product->name }} ({{ $item->product->sku }})</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->quantity, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $item->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
