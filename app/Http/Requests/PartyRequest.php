<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * A party is either a Vendor or a Customer, never both — the form only
     * ever submits a single `role`; this derives the two stored booleans from
     * it so the rest of the create/update pipeline (and every existing query
     * filtering by is_vendor/is_customer) doesn't need to change. Done here
     * rather than via merge()+passedValidation(), since validated() only
     * returns rule-declared keys and wouldn't otherwise pick up the merge.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated();

        $validated['is_vendor'] = $validated['role'] === 'vendor';
        $validated['is_customer'] = $validated['role'] === 'customer';
        unset($validated['role']);

        if ($key !== null) {
            return data_get($validated, $key, $default);
        }

        return $validated;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(['vendor', 'customer'])],
            'company_name' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'state' => ['nullable', Rule::in(config('india.states'))],
            'city' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'pan' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'ifsc' => ['nullable', 'string', 'max:20'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
