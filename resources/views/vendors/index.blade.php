<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Vendors" description="Suppliers you record expenses against." />
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        <div class="flex justify-between items-center gap-4">
            <form method="GET" class="flex-1 max-w-sm">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-ink-400 dark:text-ink-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search vendors..." class="w-full pl-9 bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
            </form>
            <a href="{{ route('vendors.create') }}">
                <x-primary-button>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    New Vendor
                </x-primary-button>
            </a>
        </div>

        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Contact</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">GSTIN</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($vendors as $vendor)
                            <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50">
                                    <a href="{{ route('vendors.show', $vendor) }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ $vendor->name }}</a>
                                </td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $vendor->mobile ?? $vendor->email ?? '—' }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $vendor->gstin ?? '—' }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="$vendor->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($vendor->status) }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3.5 text-right text-sm">
                                    <a href="{{ route('vendors.edit', $vendor) }}" class="text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300 font-medium">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-ui.empty-state title="No vendors yet" description="Add your first supplier to start recording expenses against them." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-4">{{ $vendors->links() }}</div>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
