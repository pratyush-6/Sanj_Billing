<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Expense Sub Categories') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-3 bg-green-50 text-green-800 rounded-md text-sm">{{ session('status') }}</div>
            @endif

            <div class="flex justify-between items-center">
                <a href="{{ route('expense-categories.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Categories</a>
                <a href="{{ route('expense-sub-categories.create') }}" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                    + New Sub Category
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($subCategories as $subCategory)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $subCategory->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $subCategory->category->name }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs {{ $subCategory->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($subCategory->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('expense-sub-categories.edit', $subCategory) }}" class="text-indigo-600 hover:underline">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No sub categories yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
