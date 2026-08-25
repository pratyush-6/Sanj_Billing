<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $expense ? 'Edit Expense '.$expense->expense_number : 'New Expense' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (! $activeFinancialYear)
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 text-sm">
                    No active financial year. <a href="{{ route('financial-years.index') }}" class="underline">Set one up</a> before recording expenses.
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 text-sm">{{ session('error') }}</div>
            @endif

            @if (session('duplicate_warning'))
                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 text-sm">
                    <p class="font-medium">Possible duplicate expense</p>
                    <p class="mt-1">{{ session('duplicate_warning') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST"
                      action="{{ $expense ? route('expenses.update', $expense) : route('expenses.store') }}"
                      enctype="multipart/form-data"
                      class="space-y-6"
                      x-data="{
                          quantity: {{ old('quantity', $expense?->quantity) ?: 'null' }},
                          rate: {{ old('rate', $expense?->rate) ?: 'null' }},
                          taxableAmount: {{ old('taxable_amount', $expense?->taxable_amount) ?: 0 }},
                          discount: {{ old('discount', $expense?->discount) ?: 0 }},
                          gstAmount: {{ old('gst_amount', $expense?->gst_amount) ?: 0 }},
                          natureOfUse: '{{ old('nature_of_use', $expense?->nature_of_use ?? 'Business') }}',
                          get computedTaxable() {
                              return (this.quantity && this.rate) ? (parseFloat(this.quantity) * parseFloat(this.rate)) : parseFloat(this.taxableAmount || 0);
                          },
                          get total() {
                              return (this.computedTaxable - parseFloat(this.discount || 0) + parseFloat(this.gstAmount || 0)).toFixed(2);
                          }
                      }">
                    @csrf
                    @if ($expense)
                        @method('PUT')
                    @endif
                    @if (session('duplicate_warning'))
                        <input type="hidden" name="confirm_duplicate" value="1">
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="expense_date" value="Expense Date *" />
                            <x-text-input id="expense_date" name="expense_date" type="date" class="mt-1 block w-full" :value="old('expense_date', $expense?->expense_date?->format('Y-m-d') ?? date('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('expense_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="expense_category_id" value="Category *" />
                            <select id="expense_category_id" name="expense_category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('expense_category_id', $expense?->expense_category_id) === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('expense_category_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="expense_sub_category_id" value="Sub Category" />
                            <select id="expense_sub_category_id" name="expense_sub_category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">None</option>
                                @foreach ($categories as $category)
                                    @foreach ($category->subCategories as $sub)
                                        <option value="{{ $sub->id }}" @selected((string) old('expense_sub_category_id', $expense?->expense_sub_category_id) === (string) $sub->id)>{{ $category->name }} &raquo; {{ $sub->name }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('expense_sub_category_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="vendor_id" value="Vendor" />
                            <select id="vendor_id" name="vendor_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">None</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" @selected((string) old('vendor_id', $expense?->vendor_id) === (string) $vendor->id)>{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('vendor_id')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="description" value="Description" />
                            <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description', $expense?->description)" />
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <p class="text-sm font-semibold text-gray-600 mb-4">Amount &mdash; enter Quantity + Rate, or a fixed Taxable Amount</p>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-6">
                            <div>
                                <x-input-label for="quantity" value="Quantity" />
                                <x-text-input id="quantity" name="quantity" type="number" step="0.01" x-model="quantity" class="mt-1 block w-full" />
                                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="unit_id" value="Unit" />
                                <select id="unit_id" name="unit_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">—</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}" @selected((string) old('unit_id', $expense?->unit_id) === (string) $unit->id)>{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="rate" value="Rate" />
                                <x-text-input id="rate" name="rate" type="number" step="0.01" x-model="rate" class="mt-1 block w-full" />
                                <x-input-error :messages="$errors->get('rate')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="taxable_amount" value="Taxable Amount" />
                                <x-text-input id="taxable_amount" name="taxable_amount" type="number" step="0.01" x-model="taxableAmount" :disabled="false" class="mt-1 block w-full" />
                                <p class="text-xs text-gray-500 mt-1" x-show="quantity && rate">Auto: qty &times; rate</p>
                                <x-input-error :messages="$errors->get('taxable_amount')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="discount" value="Discount" />
                                <x-text-input id="discount" name="discount" type="number" step="0.01" x-model="discount" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <x-input-label for="gst_amount" value="GST Amount" />
                                <x-text-input id="gst_amount" name="gst_amount" type="number" step="0.01" x-model="gstAmount" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <x-input-label for="tds_amount" value="TDS Amount" />
                                <x-text-input id="tds_amount" name="tds_amount" type="number" step="0.01" :value="old('tds_amount', $expense?->tds_amount)" class="mt-1 block w-full" />
                            </div>
                            <div class="flex flex-col justify-end">
                                <p class="text-xs text-gray-500">Total Amount</p>
                                <p class="text-lg font-semibold text-gray-900" x-text="'₹ ' + total"></p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <p class="text-sm font-semibold text-gray-600 mb-4">Business / Personal</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="nature_of_use" value="Nature of Use *" />
                                <select id="nature_of_use" name="nature_of_use" x-model="natureOfUse" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    @foreach ($natureOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('nature_of_use')" class="mt-2" />
                            </div>
                            <div x-show="natureOfUse === 'Mixed'">
                                <x-input-label for="business_amount" value="Business Portion" />
                                <x-text-input id="business_amount" name="business_amount" type="number" step="0.01" :value="old('business_amount', $expense?->business_amount)" class="mt-1 block w-full" />
                                <x-input-error :messages="$errors->get('business_amount')" class="mt-2" />
                            </div>
                            <div x-show="natureOfUse === 'Mixed'">
                                <x-input-label for="personal_amount" value="Personal Portion" />
                                <x-text-input id="personal_amount" name="personal_amount" type="number" step="0.01" :value="old('personal_amount', $expense?->personal_amount)" class="mt-1 block w-full" />
                                <x-input-error :messages="$errors->get('personal_amount')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <p class="text-sm font-semibold text-gray-600 mb-4">Payment</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="payment_method_id" value="Payment Mode *" />
                                <select id="payment_method_id" name="payment_method_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select</option>
                                    @foreach ($paymentMethods as $method)
                                        <option value="{{ $method->id }}" @selected((string) old('payment_method_id', $expense?->payment_method_id) === (string) $method->id)>{{ $method->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('payment_method_id')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="bank_account_id" value="Bank / Cash Account *" />
                                <select id="bank_account_id" name="bank_account_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select</option>
                                    @foreach ($bankAccounts as $account)
                                        <option value="{{ $account->id }}" @selected((string) old('bank_account_id', $expense?->bank_account_id) === (string) $account->id)>{{ $account->account_name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('bank_account_id')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="expense_nature" value="Expense Nature" />
                                <select id="expense_nature" name="expense_nature" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Select</option>
                                    @foreach ($expenseNatureOptions as $option)
                                        <option value="{{ $option }}" @selected(old('expense_nature', $expense?->expense_nature) === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <p class="text-sm font-semibold text-gray-600 mb-4">Invoice / Bill</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="invoice_number" value="Invoice Number" />
                                <x-text-input id="invoice_number" name="invoice_number" type="text" class="mt-1 block w-full" :value="old('invoice_number', $expense?->invoice_number)" />
                                <x-input-error :messages="$errors->get('invoice_number')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="invoice_date" value="Invoice Date" />
                                <x-text-input id="invoice_date" name="invoice_date" type="date" class="mt-1 block w-full" :value="old('invoice_date', $expense?->invoice_date?->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('invoice_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="attachments" value="Attach Bill(s)" />
                                <input id="attachments" name="attachments[]" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm">
                                <x-input-error :messages="$errors->get('attachments')" class="mt-2" />
                            </div>
                        </div>

                        @if ($expense && $expense->documents->isNotEmpty())
                            <div class="mt-4 space-y-1">
                                @foreach ($expense->documents as $document)
                                    <a href="{{ route('documents.download', $document) }}" class="text-sm text-indigo-600 hover:underline block">📎 {{ $document->name }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div>
                        <x-input-label for="notes" value="Notes" />
                        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('notes', $expense?->notes) }}</textarea>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ $expense ? __('Update Expense') : __('Save Expense') }}</x-primary-button>
                        <a href="{{ route('expenses.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
