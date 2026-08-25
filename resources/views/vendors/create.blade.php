<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="New Vendor" />
    </x-slot>

    <div class="max-w-3xl">
        <x-ui.card>
            <form method="POST" action="{{ route('vendors.store') }}" class="space-y-6">
                @csrf
                @include('vendors._form', ['vendor' => null])

                <div class="flex items-center gap-4 border-t border-ink-100 pt-6">
                    <x-primary-button>{{ __('Create Vendor') }}</x-primary-button>
                    <a href="{{ route('vendors.index') }}" class="text-sm text-ink-500 hover:text-ink-700">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
