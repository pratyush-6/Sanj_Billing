<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="account_name" value="Account Name *" />
        <x-text-input id="account_name" name="account_name" type="text" class="mt-1 block w-full" :value="old('account_name', $bankAccount?->account_name)" required />
        <x-input-error :messages="$errors->get('account_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="account_type" value="Account Type *" />
        <select id="account_type" name="account_type" class="mt-1 block w-full border-ink-300 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
            @foreach (['bank' => 'Bank', 'cash' => 'Cash', 'credit_card' => 'Credit Card', 'upi' => 'UPI'] as $value => $label)
                <option value="{{ $value }}" @selected(old('account_type', $bankAccount?->account_type ?? 'bank') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('account_type')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="bank_name" value="Bank Name" />
        <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" :value="old('bank_name', $bankAccount?->bank_name)" />
    </div>

    <div>
        <x-input-label for="branch" value="Branch" />
        <x-text-input id="branch" name="branch" type="text" class="mt-1 block w-full" :value="old('branch', $bankAccount?->branch)" />
    </div>

    <div>
        <x-input-label for="account_number" value="Account Number" />
        <x-text-input id="account_number" name="account_number" type="text" class="mt-1 block w-full" :value="old('account_number', $bankAccount?->account_number)" />
    </div>

    <div>
        <x-input-label for="ifsc" value="IFSC" />
        <x-text-input id="ifsc" name="ifsc" type="text" class="mt-1 block w-full uppercase" :value="old('ifsc', $bankAccount?->ifsc)" />
    </div>

    <div>
        <x-input-label for="opening_balance" value="Opening Balance *" />
        <x-text-input id="opening_balance" name="opening_balance" type="number" step="0.01" class="mt-1 block w-full" :value="old('opening_balance', $bankAccount?->opening_balance ?? 0)" required :readonly="(bool) $bankAccount" />
        @if ($bankAccount)
            <p class="text-xs text-ink-400 mt-1">Opening balance can't be changed after creation.</p>
        @endif
        <x-input-error :messages="$errors->get('opening_balance')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Status *" />
        <select id="status" name="status" class="mt-1 block w-full border-ink-300 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" required>
            <option value="active" @selected(old('status', $bankAccount?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $bankAccount?->status) === 'inactive')>Inactive</option>
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>
</div>
