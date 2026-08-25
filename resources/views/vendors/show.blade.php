<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$vendor->name">
            <x-slot name="actions">
                <a href="{{ route('vendors.edit', $vendor) }}">
                    <x-secondary-button>Edit Vendor</x-secondary-button>
                </a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-4">
        <x-ui.card>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><p class="text-ink-400 text-xs uppercase tracking-wide">Contact</p><p class="font-medium text-ink-900 mt-0.5">{{ $vendor->contact_person ?? '—' }}</p></div>
                <div><p class="text-ink-400 text-xs uppercase tracking-wide">Mobile</p><p class="font-medium text-ink-900 mt-0.5">{{ $vendor->mobile ?? '—' }}</p></div>
                <div><p class="text-ink-400 text-xs uppercase tracking-wide">GSTIN</p><p class="font-medium text-ink-900 mt-0.5">{{ $vendor->gstin ?? '—' }}</p></div>
                <div><p class="text-ink-400 text-xs uppercase tracking-wide">PAN</p><p class="font-medium text-ink-900 mt-0.5">{{ $vendor->pan ?? '—' }}</p></div>
            </div>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="px-4 py-3 border-b border-ink-100 bg-ink-50/80">
                <h3 class="text-sm font-semibold text-ink-700">Expense History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100">
                    <thead class="bg-ink-50/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Expense #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 uppercase tracking-wide">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @forelse ($expenses as $expense)
                            <tr class="hover:bg-ink-50/60">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900">{{ $expense->expense_number }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600">{{ $expense->expense_date->format('d-M-Y') }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600">{{ $expense->category->name }}</td>
                                <td class="px-4 py-3.5 text-sm text-right font-medium text-ink-900">{{ number_format($expense->total_amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="match($expense->status) { 'Approved' => 'success', 'Draft' => 'warning', 'Cancelled' => 'danger', default => 'neutral' }">{{ $expense->status }}</x-ui.badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-ui.empty-state title="No expenses recorded for this vendor yet" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-4">{{ $expenses->links() }}</div>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
