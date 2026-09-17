<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Sale Orders" :description="$orders->total().' order(s)'" />
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        <div class="flex justify-end">
            @can('sale-orders.manage')
                <a href="{{ route('sale-orders.create') }}">
                    <x-primary-button>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        New Sale Order
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
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Customer</label>
                    <select name="party_id" onchange="this.form.submit()" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All</option>
                        @foreach ($parties as $party)
                            <option value="{{ $party->id }}" @selected(request('party_id') == $party->id)>{{ $party->name }}</option>
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
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Order #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50 whitespace-nowrap">
                                    <a href="{{ route('sale-orders.show', $order) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $order->order_number }}</a>
                                </td>
                                <td class="px-4 py-3.5 text-sm text-ink-700 dark:text-ink-200">{{ $order->party->name }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $order->order_date->format('d-M-Y') }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($order->items->sum('amount'), 2) }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="match($order->status) {
                                        'Confirmed' => 'info',
                                        'Converted' => 'brand',
                                        'Cancelled' => 'danger',
                                        default => 'neutral',
                                    }">{{ $order->status }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3.5 text-right text-sm">
                                    <a href="{{ route('sale-orders.show', $order) }}" class="text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300 font-medium">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-ui.empty-state title="No sale orders yet" description="Create an order to start selling to a customer." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-4">{{ $orders->links() }}</div>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
