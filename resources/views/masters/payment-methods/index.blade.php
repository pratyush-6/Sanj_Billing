<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Payment Methods" description="Ways expenses can be paid — Cash, UPI, NEFT, etc." />
    </x-slot>

    <div class="max-w-2xl space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        <div class="flex justify-end">
            <a href="{{ route('payment-methods.create') }}">
                <x-primary-button>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    New Payment Method
                </x-primary-button>
            </a>
        </div>

        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100">
                    <thead class="bg-ink-50/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @forelse ($paymentMethods as $method)
                            <tr class="hover:bg-ink-50/60">
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900">{{ $method->name }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="$method->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($method->status) }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3.5 text-right text-sm">
                                    <a href="{{ route('payment-methods.edit', $method) }}" class="text-brand-600 hover:text-brand-800 font-medium">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-ui.empty-state title="No payment methods yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
