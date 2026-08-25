<x-app-layout>
    <x-slot name="header"><x-ui.page-header title="Edit Payment Method" /></x-slot>
    <div class="max-w-xl">
        <x-ui.card>
            <form method="POST" action="{{ route('payment-methods.update', $paymentMethod) }}" class="space-y-6">
                @csrf
                @method('PUT')
                @include('masters.payment-methods._form', ['paymentMethod' => $paymentMethod])
                <div class="flex items-center gap-4 border-t border-ink-100 pt-6">
                    <x-primary-button>{{ __('Update') }}</x-primary-button>
                    <a href="{{ route('payment-methods.index') }}" class="text-sm text-ink-500 hover:text-ink-700">Cancel</a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-app-layout>
