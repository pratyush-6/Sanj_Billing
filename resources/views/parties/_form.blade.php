<div class="space-y-8">
    <x-ui.section title="Basic Details">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <x-input-label for="name" value="Name *" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $party?->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="company_name" value="Company Name" />
                <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $party?->company_name)" />
                <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label value="This party is a *" />
                <div class="mt-1 flex items-center gap-6">
                    <label class="inline-flex items-center gap-2 text-sm text-ink-700 dark:text-ink-200">
                        <input type="checkbox" name="is_vendor" value="1" @checked(old('is_vendor', $party?->is_vendor ?? true)) class="rounded bg-white dark:bg-ink-800 border-ink-300 dark:border-ink-600 text-brand-600 dark:text-brand-400 focus:ring-brand-500">
                        Vendor (you buy from them)
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-ink-700 dark:text-ink-200">
                        <input type="checkbox" name="is_customer" value="1" @checked(old('is_customer', $party?->is_customer ?? false)) class="rounded bg-white dark:bg-ink-800 border-ink-300 dark:border-ink-600 text-brand-600 dark:text-brand-400 focus:ring-brand-500">
                        Customer (you sell to them)
                    </label>
                </div>
                <x-input-error :messages="$errors->get('is_vendor')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="contact_person" value="Contact Person" />
                <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full" :value="old('contact_person', $party?->contact_person)" />
                <x-input-error :messages="$errors->get('contact_person')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="mobile" value="Mobile" />
                <x-text-input id="mobile" name="mobile" type="text" class="mt-1 block w-full" :value="old('mobile', $party?->mobile)" />
                <x-input-error :messages="$errors->get('mobile')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $party?->email)" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="status" value="Status *" />
                <select id="status" name="status" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
                    <option value="active" @selected(old('status', $party?->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $party?->status) === 'inactive')>Inactive</option>
                </select>
                <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>
        </div>
    </x-ui.section>

    <x-ui.section title="Address">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2">
                <x-input-label for="address" value="Address" />
                <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $party?->address)" />
                <x-input-error :messages="$errors->get('address')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="state" value="State" />
                <select id="state" name="state" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">Select</option>
                    @foreach (config('india.states') as $state)
                        <option value="{{ $state }}" @selected(old('state', $party?->state) === $state)>{{ $state }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('state')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="city" value="City" />
                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $party?->city)" />
            </div>
            <div>
                <x-input-label for="pincode" value="Pincode" />
                <x-text-input id="pincode" name="pincode" type="text" class="mt-1 block w-full" :value="old('pincode', $party?->pincode)" />
            </div>
        </div>
    </x-ui.section>

    <x-ui.section title="Tax & Payment">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <x-input-label for="gstin" value="GSTIN" />
                <x-text-input id="gstin" name="gstin" type="text" class="mt-1 block w-full uppercase" :value="old('gstin', $party?->gstin)" />
                <x-input-error :messages="$errors->get('gstin')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="pan" value="PAN" />
                <x-text-input id="pan" name="pan" type="text" class="mt-1 block w-full uppercase" :value="old('pan', $party?->pan)" />
                <x-input-error :messages="$errors->get('pan')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="payment_terms" value="Payment Terms" />
                <x-text-input id="payment_terms" name="payment_terms" type="text" class="mt-1 block w-full" placeholder="e.g. Net 30" :value="old('payment_terms', $party?->payment_terms)" />
            </div>
            <div>
                <x-input-label for="bank_name" value="Bank Name" />
                <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" :value="old('bank_name', $party?->bank_name)" />
            </div>
            <div>
                <x-input-label for="account_number" value="Account Number" />
                <x-text-input id="account_number" name="account_number" type="text" class="mt-1 block w-full" :value="old('account_number', $party?->account_number)" />
            </div>
            <div>
                <x-input-label for="ifsc" value="IFSC" />
                <x-text-input id="ifsc" name="ifsc" type="text" class="mt-1 block w-full uppercase" :value="old('ifsc', $party?->ifsc)" />
            </div>
        </div>
    </x-ui.section>
</div>
