<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Vendor Quotations" :description="$quotations->total().' quotation(s)'" />
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        <div class="flex justify-end">
            @can('vendor-quotations.manage')
                <a href="{{ route('vendor-quotations.create') }}">
                    <x-primary-button>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        New Quotation
                    </x-primary-button>
                </a>
            @endcan
        </div>

        <x-ui.card>
            <form method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end text-sm">
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Status</label>
                    <select name="status" onchange="this.form.submit()" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Vendor</label>
                    <select name="vendor_id" onchange="this.form.submit()" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected(request('vendor_id') == $vendor->id)>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </x-ui.card>

        <form method="GET" action="{{ route('vendor-quotations.compare') }}">
            <x-ui.card :padded="false">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                        <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                            <tr>
                                <th class="px-4 py-3 w-8"></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Quotation #</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Vendor</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Validity</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                            @forelse ($quotations as $quotation)
                                <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                    <td class="px-4 py-3.5">
                                        <input type="checkbox" name="ids[]" value="{{ $quotation->id }}" class="rounded border-ink-300 dark:border-ink-600 text-brand-600 focus:ring-brand-500">
                                    </td>
                                    <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50 whitespace-nowrap">
                                        <a href="{{ route('vendor-quotations.show', $quotation) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $quotation->quotation_number }}</a>
                                    </td>
                                    <td class="px-4 py-3.5 text-sm text-ink-700 dark:text-ink-200">{{ $quotation->vendor->name }}</td>
                                    <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $quotation->quotation_date->format('d-M-Y') }}</td>
                                    <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $quotation->validity_date?->format('d-M-Y') ?? '—' }}</td>
                                    <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($quotation->items->sum('amount'), 2) }}</td>
                                    <td class="px-4 py-3.5 text-sm">
                                        <x-ui.badge :variant="match($quotation->status) {
                                            'Approved' => 'success',
                                            'Submitted' => 'info',
                                            'Rejected' => 'danger',
                                            'Converted' => 'brand',
                                            default => 'neutral',
                                        }">{{ $quotation->status }}</x-ui.badge>
                                        @if ($quotation->isExpired())
                                            <x-ui.badge variant="warning">Expired</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right text-sm">
                                        <a href="{{ route('vendor-quotations.show', $quotation) }}" class="text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300 font-medium">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <x-ui.empty-state title="No vendor quotations yet" description="Create a quotation request to start comparing vendor pricing." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="flex items-center justify-between px-4 py-4">
                        <x-secondary-button type="submit">Compare Selected</x-secondary-button>
                        {{ $quotations->links() }}
                    </div>
                </div>
            </x-ui.card>
        </form>
    </div>
</x-app-layout>
