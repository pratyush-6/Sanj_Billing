<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="New Sub Category" />
    </x-slot>

    <div class="max-w-2xl">
        <x-ui.card>
            <form method="POST" action="{{ route('expense-sub-categories.store') }}" class="space-y-6">
                @csrf
                @include('expense-sub-categories._form', ['subCategory' => null, 'categories' => $categories])

                <div class="flex items-center gap-4 border-t border-ink-100 pt-6">
                    <x-primary-button>{{ __('Create') }}</x-primary-button>
                    <a href="{{ route('expense-sub-categories.index') }}" class="text-sm text-ink-500 hover:text-ink-700">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
