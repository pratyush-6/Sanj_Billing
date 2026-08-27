<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Chart of Accounts" description="Accounts are created and mapped automatically from your expense categories and bank/cash accounts." />
    </x-slot>

    <x-ui.card :padded="false">
        <div class="divide-y divide-ink-100 dark:divide-ink-800">
            @foreach ($tree as $node)
                @include('accounting.chart-of-accounts._node', ['node' => $node, 'depth' => 0])
            @endforeach
        </div>
    </x-ui.card>
</x-app-layout>
