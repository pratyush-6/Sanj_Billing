<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Financial Years" description="Manage accounting periods for your company." />
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        @if (! $company)
            <x-ui.card>
                <p class="text-sm text-ink-500">Please set up the <a href="{{ route('company.edit') }}" class="text-brand-600 hover:underline font-medium">company profile</a> first.</p>
            </x-ui.card>
        @else
            <div class="flex justify-end">
                <a href="{{ route('financial-years.create') }}">
                    <x-primary-button>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        New Financial Year
                    </x-primary-button>
                </a>
            </div>

            <x-ui.card :padded="false">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ink-100">
                        <thead class="bg-ink-50/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Period</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100">
                            @forelse ($financialYears as $fy)
                                <tr class="hover:bg-ink-50/60">
                                    <td class="px-4 py-3.5 text-sm font-medium text-ink-900">{{ $fy->name }}</td>
                                    <td class="px-4 py-3.5 text-sm text-ink-600">{{ $fy->start_date->format('d-M-Y') }} &ndash; {{ $fy->end_date->format('d-M-Y') }}</td>
                                    <td class="px-4 py-3.5 text-sm space-x-1">
                                        @if ($fy->is_closed)
                                            <x-ui.badge variant="neutral">Closed</x-ui.badge>
                                        @elseif ($fy->is_active)
                                            <x-ui.badge variant="success">Active</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="warning">Inactive</x-ui.badge>
                                        @endif
                                        @if ($fy->is_locked)
                                            <x-ui.badge variant="danger">Locked</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right text-sm space-x-3 whitespace-nowrap">
                                        @unless ($fy->is_closed)
                                            @unless ($fy->is_active)
                                                <form method="POST" action="{{ route('financial-years.activate', $fy) }}" class="inline">
                                                    @csrf
                                                    <button class="text-brand-600 hover:text-brand-800 font-medium">Activate</button>
                                                </form>
                                            @endunless
                                            @if ($fy->is_locked)
                                                <form method="POST" action="{{ route('financial-years.unlock', $fy) }}" class="inline">
                                                    @csrf
                                                    <button class="text-brand-600 hover:text-brand-800 font-medium">Unlock</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('financial-years.lock', $fy) }}" class="inline">
                                                    @csrf
                                                    <button class="text-brand-600 hover:text-brand-800 font-medium">Lock</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('financial-years.close', $fy) }}" class="inline" onsubmit="return confirm('Close this financial year? This cannot be undone.');">
                                                @csrf
                                                <button class="text-rose-600 hover:text-rose-800 font-medium">Close</button>
                                            </form>
                                        @endunless
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <x-ui.empty-state title="No financial years yet" description="Create one to start recording transactions." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-app-layout>
