<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Vendors') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-3 bg-green-50 text-green-800 rounded-md text-sm">{{ session('status') }}</div>
            @endif

            <div class="flex justify-between items-center gap-4">
                <form method="GET" class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search vendors..." class="w-full max-w-sm border-gray-300 rounded-md shadow-sm text-sm">
                </form>
                <a href="{{ route('vendors.create') }}" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700 whitespace-nowrap">
                    + New Vendor
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">GSTIN</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($vendors as $vendor)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <a href="{{ route('vendors.show', $vendor) }}" class="hover:underline">{{ $vendor->name }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $vendor->mobile ?? $vendor->email ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $vendor->gstin ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs {{ $vendor->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($vendor->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('vendors.edit', $vendor) }}" class="text-indigo-600 hover:underline">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No vendors yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $vendors->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
