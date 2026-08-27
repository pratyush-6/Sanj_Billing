<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Expenses" :description="$expenses->total().' expense(s)'" />
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        <div class="flex justify-end">
            @can('expenses.manage')
                <a href="{{ route('expenses.create') }}">
                    <x-primary-button>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        New Expense
                    </x-primary-button>
                </a>
            @endcan
        </div>

        <x-ui.card>
            <form method="GET" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 items-end text-sm">
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Category</label>
                    <select name="expense_category_id" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('expense_category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Vendor</label>
                    <select name="vendor_id" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected(request('vendor_id') == $vendor->id)>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Payment Mode</label>
                    <select name="payment_method_id" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All</option>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->id }}" @selected(request('payment_method_id') == $method->id)>{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Account</label>
                    <select name="bank_account_id" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All</option>
                        @foreach ($bankAccounts as $account)
                            <option value="{{ $account->id }}" @selected(request('bank_account_id') == $account->id)>{{ $account->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Status</label>
                    <select name="status" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All</option>
                        @foreach (config('expense.statuses') as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <x-secondary-button type="submit" class="w-full justify-center">Filter</x-secondary-button>
                    <a href="{{ route('expenses.index') }}" class="inline-flex items-center px-3 py-2 text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200 text-sm">Reset</a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Expense #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Vendor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Payment</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Amount</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Bill</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($expenses as $expense)
                            <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50 whitespace-nowrap">{{ $expense->expense_number }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300 whitespace-nowrap">{{ $expense->expense_date->format('d-M-Y') }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $expense->category->name }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $expense->vendor?->name ?? '—' }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $expense->paymentMethod?->name }} / {{ $expense->bankAccount?->account_name }}</td>
                                <td class="px-4 py-3.5 text-sm text-right font-medium text-ink-900 dark:text-ink-50 whitespace-nowrap">{{ number_format($expense->total_amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-center">
                                    @if ($expense->documents->isNotEmpty())
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline text-brand-500 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" /></svg>
                                    @else
                                        <span class="text-ink-300 dark:text-ink-600">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="match($expense->status) { 'Approved' => 'success', 'Draft' => 'warning', 'Cancelled' => 'danger', default => 'neutral' }">{{ $expense->status }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3.5 text-right text-sm whitespace-nowrap">
                                    @can('expenses.manage')
                                        @if ($expense->status !== 'Cancelled')
                                            <a href="{{ route('expenses.edit', $expense) }}" class="text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300 font-medium">Edit</a>
                                            <form method="POST" action="{{ route('expenses.cancel', $expense) }}" class="inline" onsubmit="return confirm('Cancel this expense?');">
                                                @csrf
                                                <button class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-300 font-medium ml-2">Cancel</button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <x-ui.empty-state title="No expenses found" description="Try adjusting your filters, or record your first expense." />
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
