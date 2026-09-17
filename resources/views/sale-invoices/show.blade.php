<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="'Sale Invoice '.$invoice->invoice_number">
            <x-slot name="actions">
                <a href="{{ route('sale-invoices.print', $invoice) }}" target="_blank">
                    <x-secondary-button>Print</x-secondary-button>
                </a>
                <a href="{{ route('sale-invoices.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back to Sale Invoices</a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <div class="max-w-4xl space-y-4">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        @php
            $sourceChallans = $invoice->items->pluck('deliveryChallanItem.deliveryChallan')->filter()->unique('id');
        @endphp
        @if ($sourceChallans->isNotEmpty())
            <x-ui.alert variant="info">
                Billed from delivery challan(s):
                @foreach ($sourceChallans as $challan)
                    <a href="{{ route('delivery-challans.show', $challan) }}" class="underline font-medium">{{ $challan->challan_number }}</a>@if (! $loop->last), @endif
                @endforeach
            </x-ui.alert>
        @endif

        <x-ui.card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 flex-1">
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Customer</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $invoice->party->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Invoice Date</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $invoice->invoice_date->format('d-M-Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Due Date</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $invoice->due_date?->format('d-M-Y') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Created By</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $invoice->creator?->name ?? '—' }}</div>
                    </div>
                </div>
                <x-ui.badge :variant="match($invoice->status) { 'Posted' => 'success', 'Cancelled' => 'danger', default => 'neutral' }" class="text-sm">{{ $invoice->status }}</x-ui.badge>
            </div>

            @if ($invoice->notes)
                <p class="text-sm text-ink-600 dark:text-ink-300 mt-4 border-t border-ink-100 dark:border-ink-800 pt-4">{{ $invoice->notes }}</p>
            @endif

            @can('sale-invoices.manage')
                <div class="flex flex-wrap items-center gap-3 mt-6 border-t border-ink-100 dark:border-ink-800 pt-4">
                    @if ($invoice->status === 'Draft')
                        <a href="{{ route('sale-invoices.edit', $invoice) }}">
                            <x-secondary-button>Edit</x-secondary-button>
                        </a>
                        <form method="POST" action="{{ route('sale-invoices.post', $invoice) }}" onsubmit="return confirm('Post this invoice? It will affect your accounts and cannot be edited afterwards.');">
                            @csrf
                            <x-primary-button type="submit">Post Invoice</x-primary-button>
                        </form>
                    @endif
                    @if ($invoice->status !== 'Cancelled')
                        <form method="POST" action="{{ route('sale-invoices.cancel', $invoice) }}" onsubmit="return confirm('Cancel this sale invoice?');">
                            @csrf
                            <x-danger-button type="submit">Cancel</x-danger-button>
                        </form>
                    @endif
                </div>
            @endcan
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/80 dark:bg-ink-800/80">
                <h3 class="text-sm font-semibold text-ink-800 dark:text-ink-100">Line Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">HSN</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Rate</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Taxable</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">CGST</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">SGST</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">IGST</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($invoice->items as $item)
                            <tr>
                                <td class="px-4 py-3.5 text-sm text-ink-900 dark:text-ink-50">{{ $item->product->name }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $item->hsn_code ?? '—' }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->quantity, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->taxable_amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->cgst_amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->sgst_amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-600 dark:text-ink-300">{{ number_format($item->igst_amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-700 dark:text-ink-200 font-medium">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-ink-200 dark:border-ink-700">
                            <td colspan="4" class="px-4 py-2 text-right text-sm text-ink-600 dark:text-ink-300">Taxable Amount</td>
                            <td colspan="5" class="px-4 py-2 text-right text-sm text-ink-700 dark:text-ink-200">{{ number_format($invoice->taxable_amount, 2) }}</td>
                        </tr>
                        @if ($invoice->cgst_amount > 0 || $invoice->sgst_amount > 0)
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-right text-sm text-ink-600 dark:text-ink-300">CGST + SGST</td>
                                <td colspan="5" class="px-4 py-2 text-right text-sm text-ink-700 dark:text-ink-200">{{ number_format($invoice->cgst_amount + $invoice->sgst_amount, 2) }}</td>
                            </tr>
                        @endif
                        @if ($invoice->igst_amount > 0)
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-right text-sm text-ink-600 dark:text-ink-300">IGST</td>
                                <td colspan="5" class="px-4 py-2 text-right text-sm text-ink-700 dark:text-ink-200">{{ number_format($invoice->igst_amount, 2) }}</td>
                            </tr>
                        @endif
                        @if ($invoice->tds_amount > 0)
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-right text-sm text-ink-600 dark:text-ink-300">TDS ({{ $invoice->tdsSection?->section }})</td>
                                <td colspan="5" class="px-4 py-2 text-right text-sm text-rose-600 dark:text-rose-400">−{{ number_format($invoice->tds_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="border-t border-ink-200 dark:border-ink-700">
                            <td colspan="4" class="px-4 py-3 text-right text-sm font-semibold text-ink-800 dark:text-ink-100">Total</td>
                            <td colspan="5" class="px-4 py-3 text-right text-sm font-semibold text-ink-900 dark:text-ink-50">{{ number_format($invoice->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-sm text-ink-600 dark:text-ink-300">Paid</td>
                            <td colspan="5" class="px-4 py-2 text-right text-sm text-emerald-600 dark:text-emerald-400">{{ number_format($invoice->amountPaid(), 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-sm font-semibold text-ink-800 dark:text-ink-100">Amount Due</td>
                            <td colspan="5" class="px-4 py-2 text-right text-sm font-semibold {{ $invoice->amountDue() > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-ink-900 dark:text-ink-50' }}">{{ number_format($invoice->amountDue(), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="px-4 py-3 border-b border-ink-100 dark:border-ink-800 bg-ink-50/80 dark:bg-ink-800/80">
                <h3 class="text-sm font-semibold text-ink-800 dark:text-ink-100">Payments</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Payment #</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Status</th>
                            @can('sale-invoices.manage')
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Actions</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($invoice->payments as $payment)
                            <tr>
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50">{{ $payment->payment_number }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $payment->payment_date->format('d-M-Y') }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-700 dark:text-ink-200">{{ number_format($payment->amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="$payment->status === 'Posted' ? 'success' : 'danger'">{{ $payment->status }}</x-ui.badge>
                                </td>
                                @can('sale-invoices.manage')
                                    <td class="px-4 py-3.5 text-right text-sm">
                                        @if ($payment->status === 'Posted')
                                            <form method="POST" action="{{ route('sale-invoices.payments.cancel', [$invoice, $payment]) }}" onsubmit="return confirm('Cancel this payment?');">
                                                @csrf
                                                <button type="submit" class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-300 font-medium">Cancel</button>
                                            </form>
                                        @endif
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-ui.empty-state title="No payments recorded yet" /></td></tr>
                        @endforelse
                    </tbody>
                </table>

                @can('sale-invoices.manage')
                    @if ($invoice->status === 'Posted' && $invoice->amountDue() > 0)
                        <form method="POST" action="{{ route('sale-invoices.payments.store', $invoice) }}" class="flex flex-wrap items-end gap-3 px-4 py-4 border-t border-ink-100 dark:border-ink-800">
                            @csrf
                            <div>
                                <x-input-label value="Amount *" class="!mb-1" />
                                <input type="number" step="0.01" min="0.01" max="{{ $invoice->amountDue() }}" name="amount" value="{{ $invoice->amountDue() }}" class="w-32 bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500" required>
                            </div>
                            <div>
                                <x-input-label value="Date *" class="!mb-1" />
                                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500" required>
                            </div>
                            <div>
                                <x-input-label value="Bank / Cash Account *" class="!mb-1" />
                                <select name="bank_account_id" class="bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                    <option value="">Select</option>
                                    @foreach ($bankAccounts as $bankAccount)
                                        <option value="{{ $bankAccount->id }}">{{ $bankAccount->account_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label value="Payment Method *" class="!mb-1" />
                                <select name="payment_method_id" class="bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500" required>
                                    <option value="">Select</option>
                                    @foreach ($paymentMethods as $paymentMethod)
                                        <option value="{{ $paymentMethod->id }}">{{ $paymentMethod->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label value="Reference #" class="!mb-1" />
                                <input type="text" name="reference_number" class="w-32 bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                            </div>
                            <x-secondary-button type="submit">Record Payment</x-secondary-button>
                        </form>
                    @endif
                @endcan
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
