<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$product->name" />
    </x-slot>

    <div class="max-w-3xl space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6">
                @csrf
                @method('PUT')
                @include('products._form', ['product' => $product, 'categories' => $categories, 'units' => $units])

                <div class="flex items-center gap-4 border-t border-ink-100 dark:border-ink-800 pt-6">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                    <a href="{{ route('products.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
