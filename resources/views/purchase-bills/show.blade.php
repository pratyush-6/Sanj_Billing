<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="'Purchase Bill '.$bill->bill_number">
            <x-slot name="actions">
                <a href="{{ route('purchase-bills.print', $bill) }}" target="_blank">
                    <x-secondary-button>Print</x-secondary-button>
                </a>
                <a href="{{ route('purchase-bills.index') }}" class="text-sm text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200">&larr; Back</a>
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

        <x-ui.card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 flex-1">
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Vendor</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $bill->party->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Goods Receipt</div>
                        <div class="text-sm font-medium mt-1">
                            <a href="{{ route('goods-receipts.show', $bill->goodsReceipt) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $bill->goodsReceipt->grn_number }}</a>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Bill Date</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $bill->bill_date->format('d-M-Y') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ink-400 dark:text-ink-500 uppercase tracking-wide">Due Date</div>
                        <div class="text-sm font-medium text-ink-900 dark:text-ink-50 mt-1">{{ $bill->due_date?->format('d-M-Y') ?? '—' }}</div>
                    </div>
                </div>
                <x-ui.badge :variant="match($bill->status) { 'Posted' => 'success', 'Cancelled' => 'danger', default => 'neutral' }" class="text-sm">{{ $bill->status }}</x-ui.badge>
            </div>

            @if ($bill->notes)
                <p class="text-sm text-ink-600 dark:text-ink-300 mt-4 border-t border-ink-100 dark:border-ink-800 pt-4">{{ $bill->notes }}</p>
            @endif

            @can('purchase-bills.manage')
                <div class="flex flex-wrap items-center gap-3 mt-6 border-t border-ink-100 dark:border-ink-800 pt-4">
                    @if ($bill->status === 'Draft')
                        <a href="{{ route('purchase-bills.edit', $bill) }}">
                            <x-secondary-button>Edit</x-secondary-button>
                        </a>
                        <form method="POST" action="{{ route('purchase-bills.post', $bill) }}" onsubmit="return confirm('Post this bill? It will affect your accounts and cannot be edited afterwards.');">
                            @csrf
                            <x-primary-button type="submit">Post Bill</x-primary-button>
                        </form>
                    @endif
                    @if ($bill->status !== 'Cancelled')
                        <form method="POST" action="{{ route('purchase-bills.cancel', $bill) }}" onsubmit="return confirm('Cancel this purchase bill?');">
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
                        @foreach ($bill->items as $item)
                            <tr>
                                <td class="px-4 py-3.5 text-sm text-ink-900 dark:text-ink-50">{{ $item->goodsReceiptItem->purchaseOrderItem->product->name }}</td>
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
                            <td colspan="5" class="px-4 py-2 text-right text-sm text-ink-700 dark:text-ink-200">{{ number_format($bill->taxable_amount, 2) }}</td>
                        </tr>
                        @if ($bill->cgst_amount > 0 || $bill->sgst_amount > 0)
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-right text-sm text-ink-600 dark:text-ink-300">CGST + SGST</td>
                                <td colspan="5" class="px-4 py-2 text-right text-sm text-ink-700 dark:text-ink-200">{{ number_format($bill->cgst_amount + $bill->sgst_amount, 2) }}</td>
                            </tr>
                        @endif
                        @if ($bill->igst_amount > 0)
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-right text-sm text-ink-600 dark:text-ink-300">IGST</td>
                                <td colspan="5" class="px-4 py-2 text-right text-sm text-ink-700 dark:text-ink-200">{{ number_format($bill->igst_amount, 2) }}</td>
                            </tr>
                        @endif
                        @if ($bill->tds_amount > 0)
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-right text-sm text-ink-600 dark:text-ink-300">TDS ({{ $bill->tdsSection?->section }})</td>
                                <td colspan="5" class="px-4 py-2 text-right text-sm text-rose-600 dark:text-rose-400">−{{ number_format($bill->tds_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="border-t border-ink-200 dark:border-ink-700">
                            <td colspan="4" class="px-4 py-3 text-right text-sm font-semibold text-ink-800 dark:text-ink-100">Total</td>
                            <td colspan="5" class="px-4 py-3 text-right text-sm font-semibold text-ink-900 dark:text-ink-50">{{ number_format($bill->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-sm text-ink-600 dark:text-ink-300">Paid</td>
                            <td colspan="5" class="px-4 py-2 text-right text-sm text-emerald-600 dark:text-emerald-400">{{ number_format($bill->amountPaid(), 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-sm font-semibold text-ink-800 dark:text-ink-100">Amount Due</td>
                            <td colspan="5" class="px-4 py-2 text-right text-sm font-semibold {{ $bill->amountDue() > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-ink-900 dark:text-ink-50' }}">{{ number_format($bill->amountDue(), 2) }}</td>
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
                            @can('purchase-bills.manage')
                                <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Actions</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @forelse ($bill->payments as $payment)
                            <tr>
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900 dark:text-ink-50">{{ $payment->payment_number }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600 dark:text-ink-300">{{ $payment->payment_date->format('d-M-Y') }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-700 dark:text-ink-200">{{ number_format($payment->amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-sm">
                                    <x-ui.badge :variant="$payment->status === 'Posted' ? 'success' : 'danger'">{{ $payment->status }}</x-ui.badge>
                                </td>
                                @can('purchase-bills.manage')
                                    <td class="px-4 py-3.5 text-right text-sm">
                                        @if ($payment->status === 'Posted')
                                            <form method="POST" action="{{ route('purchase-bills.payments.cancel', [$bill, $payment]) }}" onsubmit="return confirm('Cancel this payment?');">
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

                @can('purchase-bills.manage')
                    @if ($bill->status === 'Posted' && $bill->amountDue() > 0)
                        <form method="POST" action="{{ route('purchase-bills.payments.store', $bill) }}" class="flex flex-wrap items-end gap-3 px-4 py-4 border-t border-ink-100 dark:border-ink-800">
                            @csrf
                            <div>
                                <x-input-label value="Amount *" class="!mb-1" />
                                <input type="number" step="0.01" min="0.01" max="{{ $bill->amountDue() }}" name="amount" value="{{ $bill->amountDue() }}" class="w-32 bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500" required>
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
