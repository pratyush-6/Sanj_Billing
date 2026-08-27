<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Payment-wise Expense Report" description="Spending by payment method and by account.">
            <x-slot name="actions">
                @include('reports._export-buttons', ['report' => 'payment-wise'])
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="space-y-4">
        <x-ui.card>
            @include('reports._filters', ['financialYears' => $financialYears])
        </x-ui.card>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <x-ui.card>
                <p class="text-sm font-semibold text-ink-900 dark:text-ink-50 mb-3">By Payment Method</p>
                @if ($paymentMethodRows->isEmpty())
                    <x-ui.empty-state title="No expenses found" />
                @else
                    <div class="h-56 mb-4">
                        <x-ui.chart type="doughnut" :data="[
                            'labels' => $paymentMethodRows->pluck('payment_method_name'),
                            'datasets' => [['data' => $paymentMethodRows->pluck('total'), 'backgroundColor' => ['#0d9488','#0891b2','#6366f1','#d946ef','#f59e0b','#ef4444','#10b981','#3b82f6','#a855f7','#f97316']]],
                        ]" />
                    </div>
                    <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800 text-sm">
                        @foreach ($paymentMethodRows as $row)
                            <tr class="divide-y divide-ink-100 dark:divide-ink-800">
                                <td class="py-2 text-ink-700 dark:text-ink-200">{{ $row->payment_method_name }}</td>
                                <td class="py-2 text-right font-medium text-ink-900 dark:text-ink-50">{{ number_format($row->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </x-ui.card>

            <x-ui.card>
                <p class="text-sm font-semibold text-ink-900 dark:text-ink-50 mb-3">By Bank / Cash Account</p>
                @if ($bankAccountRows->isEmpty())
                    <x-ui.empty-state title="No expenses found" />
                @else
                    <div class="h-56 mb-4">
                        <x-ui.chart type="doughnut" :data="[
                            'labels' => $bankAccountRows->pluck('account_name'),
                            'datasets' => [['data' => $bankAccountRows->pluck('total'), 'backgroundColor' => ['#0d9488','#0891b2','#6366f1','#d946ef','#f59e0b','#ef4444','#10b981','#3b82f6','#a855f7','#f97316']]],
                        ]" />
                    </div>
                    <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800 text-sm">
                        @foreach ($bankAccountRows as $row)
                            <tr class="divide-y divide-ink-100 dark:divide-ink-800">
                                <td class="py-2 text-ink-700 dark:text-ink-200">{{ $row->account_name }}</td>
                                <td class="py-2 text-right font-medium text-ink-900 dark:text-ink-50">{{ number_format($row->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
