<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Financial Years') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="p-3 bg-green-50 text-green-800 rounded-md text-sm">{{ session('status') }}</div>
            @endif

            @if (! $company)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-600">
                    Please set up the <a href="{{ route('company.edit') }}" class="text-indigo-600 underline">company profile</a> first.
                </div>
            @else
                <div class="flex justify-end">
                    <a href="{{ route('financial-years.create') }}" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                        + New Financial Year
                    </a>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($financialYears as $fy)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $fy->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $fy->start_date->format('d-M-Y') }} &ndash; {{ $fy->end_date->format('d-M-Y') }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if ($fy->is_closed)
                                            <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-600">Closed</span>
                                        @elseif ($fy->is_active)
                                            <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Active</span>
                                        @else
                                            <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">Inactive</span>
                                        @endif
                                        @if ($fy->is_locked)
                                            <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700">Locked</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm space-x-2">
                                        @unless ($fy->is_closed)
                                            @unless ($fy->is_active)
                                                <form method="POST" action="{{ route('financial-years.activate', $fy) }}" class="inline">
                                                    @csrf
                                                    <button class="text-indigo-600 hover:underline">Activate</button>
                                                </form>
                                            @endunless
                                            @if ($fy->is_locked)
                                                <form method="POST" action="{{ route('financial-years.unlock', $fy) }}" class="inline">
                                                    @csrf
                                                    <button class="text-indigo-600 hover:underline">Unlock</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('financial-years.lock', $fy) }}" class="inline">
                                                    @csrf
                                                    <button class="text-indigo-600 hover:underline">Lock</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('financial-years.close', $fy) }}" class="inline" onsubmit="return confirm('Close this financial year? This cannot be undone.');">
                                                @csrf
                                                <button class="text-red-600 hover:underline">Close</button>
                                            </form>
                                        @endunless
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No financial years yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
