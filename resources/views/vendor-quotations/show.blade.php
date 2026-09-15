<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="'Quotation '.$quotation->quotation_number">
            <x-slot name="actions">
                <a href="{{ route('vendor-quotations.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Quotations</a>
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

        @if ($quotation->isExpired())
            <x-ui.alert variant="warning">This quotation's validity date has passed.</x-ui.alert>
        @endif

        <x-ui.card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 flex-1">
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Vendor</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $quotation->vendor->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Quotation Date</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $quotation->quotation_date->format('d-M-Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Valid Until</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $quotation->validity_date?->format('d-M-Y') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Created By</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $quotation->creator?->name ?? '—' }}</div>
                    </div>
                </div>
                <x-ui.badge :variant="match($quotation->status) {
                    'Approved' => 'success',
                    'Submitted' => 'info',
                    'Rejected' => 'danger',
                    'Converted' => 'brand',
                    default => 'neutral',
                }" class="text-sm">{{ $quotation->status }}</x-ui.badge>
            </div>

            @if ($quotation->notes)
                <p class="text-sm text-ink-600 dark:text-ink-300 mt-4 border-t border-ink-100 dark:border-ink-800 pt-4">{{ $quotation->notes }}</p>
            @endif

            <div class="flex flex-wrap items-center gap-3 mt-6 border-t border-ink-100 dark:border-ink-800 pt-4">
                @can('vendor-quotations.manage')
                    @if ($quotation->status === 'Draft')
                        <a href="{{ route('vendor-quotations.edit', $quotation) }}">
                            <x-secondary-button>Edit</x-secondary-button>
                        </a>
                        <form method="POST" action="{{ route('vendor-quotations.submit', $quotation) }}" onsubmit="return confirm('Submit this quotation for approval? It cannot be edited after submission.');">
                            @csrf
                            <x-primary-button type="submit">Submit for Approval</x-primary-button>
                        </form>
                    @endif
                @endcan

                @can('vendor-quotations.approve')
                    @if ($quotation->status === 'Submitted' && $quotation->created_by !== auth()->id())
                        <form method="POST" action="{{ route('vendor-quotations.approval.store', $quotation) }}" class="flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="decision" value="Approved">
                            <x-primary-button type="submit">Approve</x-primary-button>
                        </form>
                        <form method="POST" action="{{ route('vendor-quotations.approval.store', $quotation) }}" onsubmit="return confirm('Reject this quotation?');">
                            @csrf
                            <input type="hidden" name="decision" value="Rejected">
                            <x-danger-button type="submit">Reject</x-danger-button>
                        </form>
                    @elseif ($quotation->status === 'Submitted')
                        <p class="text-xs text-ink-400 dark:text-ink-500">You submitted this quotation, so you cannot approve or reject it yourself.</p>
                    @endif
                @endcan

                @can('purchase-orders.manage')
                    @if ($quotation->status === 'Approved')
                        <a href="{{ route('purchase-orders.create', ['from_quotation' => $quotation->id]) }}">
                            <x-primary-button>Convert to Purchase Order</x-primary-button>
                        </a>
                    @endif
                @endcan
            </div>

            @if ($quotation->purchaseOrders->isNotEmpty())
                <div class="border-t border-ink-100 dark:border-ink-800 mt-4 pt-4">
                    <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide mb-1">Purchase Orders</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($quotation->purchaseOrders as $po)
                            <a href="{{ route('purchase-orders.show', $po) }}" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">{{ $po->po_number }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
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
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Unit Price</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($quotation->items as $item)
                            <tr>
                                <td class="px-4 py-3.5 text-sm text-ink-900 dark:text-ink-50">{{ $item->product->name }} ({{ $item->product->sku }})</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->quantity, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-700 dark:text-ink-200 font-medium">{{ number_format($item->amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $item->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-ink-200 dark:border-ink-700">
                            <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold text-ink-800 dark:text-ink-100">Total</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-ink-900 dark:text-ink-50">{{ number_format($quotation->totalAmount(), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-ui.card>

        @if ($quotation->approvals->isNotEmpty())
            <x-ui.card :padded="false">
                <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/80 dark:bg-ink-800/80">
                    <h3 class="text-sm font-semibold text-ink-800 dark:text-ink-100">Approval History</h3>
                </div>
                <div class="divide-y divide-ink-100 dark:divide-ink-800">
                    @foreach ($quotation->approvals as $approval)
                        <div class="px-4 py-3 text-sm">
                            <span class="font-medium text-ink-900 dark:text-ink-50">{{ $approval->approver?->name ?? 'Unknown' }}</span>
                            <span class="text-ink-500 dark:text-ink-400">{{ strtolower($approval->status) }} this quotation on {{ $approval->acted_at?->format('d-M-Y H:i') }}</span>
                            @if ($approval->comments)
                                <p class="text-ink-600 dark:text-ink-300 mt-1">"{{ $approval->comments }}"</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>
