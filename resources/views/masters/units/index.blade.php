<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Units') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-3 bg-green-50 text-green-800 rounded-md text-sm">{{ session('status') }}</div>
            @endif

            <div class="flex justify-end">
                <a href="{{ route('units.create') }}" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">+ New Unit</a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($units as $unit)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $unit->name }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs {{ $unit->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($unit->status) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('units.edit', $unit) }}" class="text-indigo-600 hover:underline">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">No units yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
