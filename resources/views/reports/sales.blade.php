<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="ui_label('Sales Report')" :description="ui_label('Invoiced sales across every branch of the company, plus orders not yet invoiced.')" />
    </x-slot>

    <div class="space-y-4">
        <x-ui.card>
            <form method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end text-sm">
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">{{ ui_label('From') }}</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">{{ ui_label('To') }}</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">{{ ui_label('Branch') }}</label>
                    <select name="branch_id" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">{{ ui_label('All branches') }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($branchId == $branch->id)>{{ $branch->name }}{{ $branch->is_primary ? ' ('.ui_label('Head').')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <x-secondary-button type="submit" class="w-full justify-center">{{ ui_label('Filter') }}</x-secondary-button>
                    <a href="{{ route('reports.sales') }}" class="inline-flex items-center px-3 py-2 text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200 text-sm">{{ ui_label('Reset') }}</a>
                </div>
            </form>
        </x-ui.card>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-ui.stat :label="ui_label('Invoiced Sales')" accent="emerald" :value="'₹'.number_format($summary['total'], 2)" :hint="$summary['count'].' '.ui_label('posted invoices')" />
            <x-ui.stat :label="ui_label('Order Pipeline')" accent="amber" :value="'₹'.number_format($pipeline['total'], 2)" :hint="ui_label('Confirmed orders — not yet invoiced')" />
            <x-ui.stat :label="ui_label('Average Invoice')" accent="sky" :value="'₹'.number_format($summary['count'] > 0 ? $summary['total'] / $summary['count'] : 0, 2)" />
        </div>

        <x-ui.card>
            <p class="text-sm font-semibold text-ink-900 dark:text-ink-50 mb-3">{{ ui_label('Monthly Trend') }}</p>
            <div class="h-64">
                <x-ui.chart type="line" :data="[
                    'labels' => $trend->pluck('label'),
                    'datasets' => [['label' => ui_label('Invoiced Sales'), 'data' => $trend->pluck('total'), 'borderColor' => '#0d9488', 'backgroundColor' => 'rgba(13,148,136,0.1)', 'fill' => true, 'tension' => 0.3]],
                ]" :options="['plugins' => ['legend' => ['display' => false]]]" />
            </div>
        </x-ui.card>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <x-ui.card :padded="false">
                <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/60 dark:bg-ink-800/60">
                    <p class="text-sm font-semibold text-ink-900 dark:text-ink-50">{{ ui_label('By Branch') }}</p>
                </div>
                <div class="divide-y divide-ink-100 dark:divide-ink-800">
                    @forelse ($byBranch as $row)
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="text-ink-700 dark:text-ink-200">{{ $row->branch_name }}</span>
                            <span class="font-medium text-ink-900 dark:text-ink-50">₹{{ number_format($row->total, 2) }}</span>
                        </div>
                    @empty
                        <div class="px-4 py-2.5 text-sm text-ink-400 dark:text-ink-500">{{ ui_label('No invoices found') }}</div>
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card :padded="false">
                <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/60 dark:bg-ink-800/60">
                    <p class="text-sm font-semibold text-ink-900 dark:text-ink-50">{{ ui_label('Top Customers') }}</p>
                </div>
                <div class="divide-y divide-ink-100 dark:divide-ink-800">
                    @forelse ($byParty as $row)
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <a href="{{ route('parties.show', $row->party_id) }}" class="text-ink-700 dark:text-ink-200 hover:text-brand-600 dark:hover:text-brand-400">{{ $row->party_name }}</a>
                            <span class="font-medium text-ink-900 dark:text-ink-50">₹{{ number_format($row->total, 2) }}</span>
                        </div>
                    @empty
                        <div class="px-4 py-2.5 text-sm text-ink-400 dark:text-ink-500">{{ ui_label('No invoices found') }}</div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <x-ui.card :padded="false">
            <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/60 dark:bg-ink-800/60">
                <p class="text-sm font-semibold text-ink-900 dark:text-ink-50">{{ ui_label('Sale Details') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">{{ __('common.date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">{{ ui_label('Invoice #') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">{{ ui_label('Branch') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">{{ ui_label('Customer') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">{{ __('common.product') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">{{ __('common.qty') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">{{ ui_label('Rate') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">{{ __('common.amount') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">{{ ui_label('Sold By') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($lines as $line)
                            <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300 whitespace-nowrap">{{ $line->saleInvoice->invoice_date->format('d-M-Y') }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-900 dark:text-ink-50">
                                    <a href="{{ route('sale-invoices.show', $line->saleInvoice) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $line->saleInvoice->invoice_number }}</a>
                                </td>
                                <td class="px-4 py-3.5 text-sm text-ink-700 dark:text-ink-200">{{ $line->saleInvoice->branch?->name ?? ui_label('Unassigned') }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-700 dark:text-ink-200">{{ $line->saleInvoice->party?->name }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-700 dark:text-ink-200">{{ $line->product?->displayLabel() }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($line->quantity, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">₹{{ number_format($line->unit_price, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right font-medium text-ink-900 dark:text-ink-50">₹{{ number_format($line->amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-700 dark:text-ink-200">{{ $line->saleInvoice->creator?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9"><x-ui.empty-state :title="ui_label('No sale lines found')" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-4">{{ $lines->links() }}</div>
        </x-ui.card>
    </div>
</x-app-layout>
