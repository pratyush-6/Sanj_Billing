<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Edit Sub Category" />
    </x-slot>

    <div class="max-w-2xl">
        <x-ui.card>
            <form method="POST" action="{{ route('expense-sub-categories.update', $subCategory) }}" class="space-y-6">
                @csrf
                @method('PUT')
                @include('expense-sub-categories._form', ['subCategory' => $subCategory, 'categories' => $categories])

                <div class="flex items-center gap-4 border-t border-ink-100 pt-6">
                    <x-primary-button>{{ __('Update') }}</x-primary-button>
                    <a href="{{ route('expense-sub-categories.index') }}" class="text-sm text-ink-500 hover:text-ink-700">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
