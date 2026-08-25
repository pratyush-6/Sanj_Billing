<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Expenses') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-3 bg-green-50 text-green-800 rounded-md text-sm">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="p-3 bg-red-50 text-red-800 rounded-md text-sm">{{ session('error') }}</div>
            @endif

            <div class="flex justify-between items-center">
                <h3 class="text-sm font-medium text-gray-500">{{ $expenses->total() }} expense(s)</h3>
                @can('expenses.manage')
                    <a href="{{ route('expenses.create') }}" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                        + New Expense
                    </a>
                @endcan
            </div>

            <form method="GET" class="bg-white shadow-sm sm:rounded-lg p-4 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 items-end text-sm">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Category</label>
                    <select name="expense_category_id" class="w-full border-gray-300 rounded-md text-sm">
                        <option value="">All</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('expense_category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Vendor</label>
                    <select name="vendor_id" class="w-full border-gray-300 rounded-md text-sm">
                        <option value="">All</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected(request('vendor_id') == $vendor->id)>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Payment Mode</label>
                    <select name="payment_method_id" class="w-full border-gray-300 rounded-md text-sm">
                        <option value="">All</option>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->id }}" @selected(request('payment_method_id') == $method->id)>{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Account</label>
                    <select name="bank_account_id" class="w-full border-gray-300 rounded-md text-sm">
                        <option value="">All</option>
                        @foreach ($bankAccounts as $account)
                            <option value="{{ $account->id }}" @selected(request('bank_account_id') == $account->id)>{{ $account->account_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-md text-sm">
                        <option value="">All</option>
                        @foreach (config('expense.statuses') as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="px-3 py-2 bg-gray-800 text-white rounded-md text-sm">Filter</button>
                    <a href="{{ route('expenses.index') }}" class="px-3 py-2 border rounded-md text-sm">Reset</a>
                </div>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expense #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bill</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($expenses as $expense)
                            <tr>
                                <td class="px-4 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $expense->expense_number }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $expense->expense_date->format('d-M-Y') }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600">{{ $expense->category->name }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600">{{ $expense->vendor?->name ?? '—' }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600">{{ $expense->paymentMethod?->name }} / {{ $expense->bankAccount?->account_name }}</td>
                                <td class="px-4 py-4 text-sm text-right text-gray-900 whitespace-nowrap">{{ number_format($expense->total_amount, 2) }}</td>
                                <td class="px-4 py-4 text-sm">{{ $expense->documents->isNotEmpty() ? '📎' : '—' }}</td>
                                <td class="px-4 py-4 text-sm">
                                    <span @class([
                                        'px-2 py-1 rounded-full text-xs',
                                        'bg-green-100 text-green-700' => $expense->status === 'Approved',
                                        'bg-yellow-100 text-yellow-700' => $expense->status === 'Draft',
                                        'bg-red-100 text-red-700' => $expense->status === 'Cancelled',
                                    ])>{{ $expense->status }}</span>
                                </td>
                                <td class="px-4 py-4 text-right text-sm whitespace-nowrap">
                                    @can('expenses.manage')
                                        @if ($expense->status !== 'Cancelled')
                                            <a href="{{ route('expenses.edit', $expense) }}" class="text-indigo-600 hover:underline">Edit</a>
                                            <form method="POST" action="{{ route('expenses.cancel', $expense) }}" class="inline" onsubmit="return confirm('Cancel this expense?');">
                                                @csrf
                                                <button class="text-red-600 hover:underline ml-2">Cancel</button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">No expenses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-4">{{ $expenses->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
