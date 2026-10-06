<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Stock Transfers" description="Move stock from one branch to another. Shows transfers into or out of the selected branch.">
            <x-slot name="actions">
                @if (current_branch() && ! app(\App\Services\BranchContextService::class)->isAllBranches())
                    <a href="{{ route('stock-transfers.create') }}">
                        <x-primary-button type="button">New Transfer</x-primary-button>
                    </a>
                @endif
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Transfer #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">{{ __('common.date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">From</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">To</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">{{ __('common.status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($transfers as $transfer)
                            <tr class="hover:bg-ink-50/60 dark:hover:bg-ink-800/60">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50">{{ $transfer->transfer_number }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $transfer->transfer_date->format('d-M-Y') }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-700 dark:text-ink-200">{{ $transfer->fromBranch->name }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-700 dark:text-ink-200">{{ $transfer->toBranch->name }}</td>
                                <td class="px-4 py-3.5 text-sm"><x-ui.badge variant="success">{{ enum_label($transfer->status) }}</x-ui.badge></td>
                                <td class="px-4 py-3.5 text-right text-sm">
                                    <a href="{{ route('stock-transfers.show', $transfer) }}" class="text-brand-600 dark:text-brand-400 hover:text-brand-800 dark:hover:text-brand-300 font-medium">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-ui.empty-state title="No transfers yet" description="Transfers you make between branches will appear here." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-4">{{ $transfers->links() }}</div>
        </x-ui.card>
    </div>
</x-app-layout>
