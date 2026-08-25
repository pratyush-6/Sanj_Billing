<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="name" value="Vendor Name *" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $vendor?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="company_name" value="Company Name" />
        <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $vendor?->company_name)" />
        <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="contact_person" value="Contact Person" />
        <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full" :value="old('contact_person', $vendor?->contact_person)" />
        <x-input-error :messages="$errors->get('contact_person')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="mobile" value="Mobile" />
        <x-text-input id="mobile" name="mobile" type="text" class="mt-1 block w-full" :value="old('mobile', $vendor?->mobile)" />
        <x-input-error :messages="$errors->get('mobile')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $vendor?->email)" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="gstin" value="GSTIN" />
        <x-text-input id="gstin" name="gstin" type="text" class="mt-1 block w-full uppercase" :value="old('gstin', $vendor?->gstin)" />
        <x-input-error :messages="$errors->get('gstin')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="pan" value="PAN" />
        <x-text-input id="pan" name="pan" type="text" class="mt-1 block w-full uppercase" :value="old('pan', $vendor?->pan)" />
        <x-input-error :messages="$errors->get('pan')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="address" value="Address" />
        <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $vendor?->address)" />
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="state" value="State" />
        <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" :value="old('state', $vendor?->state)" />
    </div>

    <div>
        <x-input-label for="city" value="City" />
        <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $vendor?->city)" />
    </div>

    <div>
        <x-input-label for="pincode" value="Pincode" />
        <x-text-input id="pincode" name="pincode" type="text" class="mt-1 block w-full" :value="old('pincode', $vendor?->pincode)" />
    </div>

    <div>
        <x-input-label for="payment_terms" value="Payment Terms" />
        <x-text-input id="payment_terms" name="payment_terms" type="text" class="mt-1 block w-full" placeholder="e.g. Net 30" :value="old('payment_terms', $vendor?->payment_terms)" />
    </div>

    <div>
        <x-input-label for="bank_name" value="Bank Name" />
        <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" :value="old('bank_name', $vendor?->bank_name)" />
    </div>

    <div>
        <x-input-label for="account_number" value="Account Number" />
        <x-text-input id="account_number" name="account_number" type="text" class="mt-1 block w-full" :value="old('account_number', $vendor?->account_number)" />
    </div>

    <div>
        <x-input-label for="ifsc" value="IFSC" />
        <x-text-input id="ifsc" name="ifsc" type="text" class="mt-1 block w-full uppercase" :value="old('ifsc', $vendor?->ifsc)" />
    </div>

    <div>
        <x-input-label for="status" value="Status *" />
        <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            <option value="active" @selected(old('status', $vendor?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $vendor?->status) === 'inactive')>Inactive</option>
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>
</div>
