<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$company->name" description="Business details used across the app and on reports." />
    </x-slot>

    <div class="max-w-4xl space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('companies.update', $company) }}" class="space-y-6">
                @csrf
                @method('PUT')
                @include('company._form', ['company' => $company])

                <div class="flex items-center gap-4 border-t border-ink-100 pt-6">
                    <x-primary-button>{{ __('Save Company') }}</x-primary-button>
                    <a href="{{ route('companies.index') }}" class="text-sm text-ink-500 hover:text-ink-700">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
