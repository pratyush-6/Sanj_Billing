<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Stock Adjustments" :description="$adjustments->total().' adjustment(s)'" />
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        @can('stock-adjustments.approve')
            @if ($branch)
                <x-ui.card>
                    <p class="text-sm font-semibold text-ink-900 dark:text-ink-50">Move unassigned stock into {{ $branch->name }}</p>
                    <p class="text-xs text-ink-500 dark:text-ink-400 mt-1">Stock recorded before branches existed belongs to no branch. Moving it here posts immediately and keeps its value.</p>
                    <form method="POST" action="{{ route('stock-adjustments.assign-unassigned') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end mt-4 text-sm">
                        @csrf
                        <div class="sm:col-span-2">
                            <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Product</label>
                            <select name="product_id" required class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Select</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->displayLabel() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Quantity</label>
                            <input type="number" name="quantity" step="0.01" min="0.01" required class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Date</label>
                            <input type="date" name="adjustment_date" value="{{ date('Y-m-d') }}" required class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Reason</label>
                            <select name="reason" required class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Select</option>
                                @foreach ($reasons as $reason)
                                    <option value="{{ $reason }}">{{ $reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-5">
                            <x-primary-button>Move into {{ $branch->name }}</x-primary-button>
                        </div>
                    </form>
                </x-ui.card>
            @endif
        @endcan

        <div class="flex justify-end">
            @can('stock-adjustments.manage')
                <a href="{{ route('stock-adjustments.create') }}">
                    <x-primary-button>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        New Stock Adjustment
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
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Adjustment #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Type</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Reason</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($adjustments as $adjustment)
                            <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50 whitespace-nowrap">
                                    <a href="{{ route('stock-adjustments.show', $adjustment) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $adjustment->adjustment_number }}</a>
                                </td>
                                <td class="px-4 py-3.5 text-sm text-ink-700 dark:text-ink-200">{{ $adjustment->product->name }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="$adjustment->type === 'Increase' ? 'success' : 'warning'">{{ $adjustment->type }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($adjustment->quantity, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $adjustment->reason }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="match($adjustment->status) { 'Approved' => 'success', 'Rejected' => 'danger', default => 'neutral' }">{{ $adjustment->status }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3.5 text-right text-sm">
                                    <a href="{{ route('stock-adjustments.show', $adjustment) }}" class="text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300 font-medium">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-ui.empty-state title="No stock adjustments yet" description="Record a correction for damaged, lost, or opening stock." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-4">{{ $adjustments->links() }}</div>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
