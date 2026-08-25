<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Bank & Cash Accounts" description="Where money moves in and out of." />
    </x-slot>

    <div class="max-w-4xl space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        <div class="flex justify-end">
            <a href="{{ route('bank-accounts.create') }}">
                <x-primary-button>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    New Account
                </x-primary-button>
            </a>
        </div>

        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100">
                    <thead class="bg-ink-50/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Account</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Type</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 uppercase tracking-wide">Opening Balance</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @forelse ($bankAccounts as $account)
                            <tr class="hover:bg-ink-50/60">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900">
                                    {{ $account->account_name }}
                                    @if ($account->bank_name)
                                        <span class="text-ink-400 font-normal">({{ $account->bank_name }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-sm text-ink-600">{{ ucfirst(str_replace('_', ' ', $account->account_type)) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-900">{{ number_format($account->opening_balance, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="$account->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($account->status) }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3.5 text-right text-sm">
                                    <a href="{{ route('bank-accounts.edit', $account) }}" class="text-brand-600 hover:text-brand-800 font-medium">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-ui.empty-state title="No accounts yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
