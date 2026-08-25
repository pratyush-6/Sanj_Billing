<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Companies" description="Businesses set up in Sanjeevani." />
    </x-slot>

    <div class="max-w-4xl space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        <div class="flex justify-end">
            <a href="{{ route('companies.create') }}">
                <x-primary-button>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    New Company
                </x-primary-button>
            </a>
        </div>

        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100">
                    <thead class="bg-ink-50/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Business Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @forelse ($companies as $company)
                            <tr class="hover:bg-ink-50/60">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900">
                                    {{ $company->name }}
                                    @if ($currentCompany?->id === $company->id)
                                        <x-ui.badge variant="brand" class="ml-2">Current</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-sm text-ink-600">{{ $company->business_type ?? '—' }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="$company->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($company->status) }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3.5 text-right text-sm space-x-3">
                                    @if ($currentCompany?->id !== $company->id)
                                        <form method="POST" action="{{ route('companies.switch', $company) }}" class="inline">
                                            @csrf
                                            <button class="text-brand-600 hover:text-brand-800 font-medium">Switch</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('companies.edit', $company) }}" class="text-brand-600 hover:text-brand-800 font-medium">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-ui.empty-state title="No companies yet" description="Create your first company to get started." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
