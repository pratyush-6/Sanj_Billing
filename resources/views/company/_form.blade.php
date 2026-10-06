<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="name" :value="ui_label('Company Name *')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $company?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="legal_name" :value="ui_label('Legal Name')" />
        <x-text-input id="legal_name" name="legal_name" type="text" class="mt-1 block w-full" :value="old('legal_name', $company?->legal_name)" />
        <x-input-error :messages="$errors->get('legal_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="business_type" :value="ui_label('Business Type')" />
        <input list="business-types" id="business_type" name="business_type" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500" value="{{ old('business_type', $company?->business_type) }}" />
        <datalist id="business-types">
            <option value="Proprietorship" />
            <option value="Partnership" />
            <option value="LLP" />
            <option value="Private Limited" />
            <option value="Public Limited" />
            <option value="Individual" />
            <option value="Other" />
        </datalist>
        <x-input-error :messages="$errors->get('business_type')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="pan" :value="ui_label('PAN')" />
        <x-text-input id="pan" name="pan" type="text" class="mt-1 block w-full uppercase" :value="old('pan', $company?->pan)" />
        <x-input-error :messages="$errors->get('pan')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="tan" :value="ui_label('TAN')" />
        <x-text-input id="tan" name="tan" type="text" class="mt-1 block w-full uppercase" :value="old('tan', $company?->tan)" />
        <x-input-error :messages="$errors->get('tan')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="gstin" :value="ui_label('GSTIN')" />
        <x-text-input id="gstin" name="gstin" type="text" class="mt-1 block w-full uppercase" :value="old('gstin', $company?->gstin)" />
        <x-input-error :messages="$errors->get('gstin')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="cin" :value="ui_label('CIN')" />
        <x-text-input id="cin" name="cin" type="text" class="mt-1 block w-full uppercase" :value="old('cin', $company?->cin)" />
        <x-input-error :messages="$errors->get('cin')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="ui_label('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $company?->email)" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" :value="ui_label('Phone')" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $company?->phone)" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="website" :value="ui_label('Website')" />
        <x-text-input id="website" name="website" type="text" class="mt-1 block w-full" :value="old('website', $company?->website)" />
        <x-input-error :messages="$errors->get('website')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="address" :value="ui_label('Address')" />
        <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $company?->address)" />
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="state" :value="ui_label('State')" />
        <select id="state" name="state" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">Select</option>
            @foreach (config('india.states') as $state)
                <option value="{{ $state }}" @selected(old('state', $company?->state) === $state)>{{ $state }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('state')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="city" :value="ui_label('City')" />
        <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $company?->city)" />
        <x-input-error :messages="$errors->get('city')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="pincode" :value="ui_label('Pincode')" />
        <x-text-input id="pincode" name="pincode" type="text" class="mt-1 block w-full" :value="old('pincode', $company?->pincode)" />
        <x-input-error :messages="$errors->get('pincode')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="business_description" :value="ui_label('Business Description')" />
        <textarea id="business_description" name="business_description" rows="3" class="mt-1 block w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">{{ old('business_description', $company?->business_description) }}</textarea>
        <x-input-error :messages="$errors->get('business_description')" class="mt-2" />
    </div>
</div>
