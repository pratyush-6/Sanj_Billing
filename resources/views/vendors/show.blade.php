<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $vendor->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><p class="text-gray-500">Contact</p><p class="font-medium">{{ $vendor->contact_person ?? '—' }}</p></div>
                <div><p class="text-gray-500">Mobile</p><p class="font-medium">{{ $vendor->mobile ?? '—' }}</p></div>
                <div><p class="text-gray-500">GSTIN</p><p class="font-medium">{{ $vendor->gstin ?? '—' }}</p></div>
                <div><p class="text-gray-500">PAN</p><p class="font-medium">{{ $vendor->pan ?? '—' }}</p></div>
                <div class="col-span-2 sm:col-span-4">
                    <a href="{{ route('vendors.edit', $vendor) }}" class="text-indigo-600 hover:underline">Edit Vendor</a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-3 border-b bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-600">Expense History</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expense #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($expenses as $expense)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $expense->expense_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $expense->expense_date->format('d-M-Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $expense->category->name }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($expense->total_amount, 2) }}</td>
                                <td class="px-6 py-4 text-sm">{{ $expense->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No expenses recorded for this vendor yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $expenses->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
