<x-app-layout>
    <x-slot name="header"><x-ui.page-header title="New Account" /></x-slot>
    <div class="max-w-xl">
        <x-ui.card>
            <form method="POST" action="{{ route('bank-accounts.store') }}" class="space-y-6">
                @csrf
                @include('masters.bank-accounts._form', ['bankAccount' => null])
                <div class="flex items-center gap-4 border-t border-ink-100 pt-6">
                    <x-primary-button>{{ __('Create') }}</x-primary-button>
                    <a href="{{ route('bank-accounts.index') }}" class="text-sm text-ink-500 hover:text-ink-700">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
