<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="New Company" description="Business details used across the app and on reports." />
    </x-slot>

    <div class="max-w-4xl">
        <x-ui.card>
            <form method="POST" action="{{ route('companies.store') }}" class="space-y-6">
                @csrf
                @include('company._form', ['company' => null])

                <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                    <x-primary-button>{{ __('Create Company') }}</x-primary-button>
                    <a href="{{ route('companies.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
