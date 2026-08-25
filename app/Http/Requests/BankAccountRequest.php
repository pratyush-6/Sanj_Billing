<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_name' => ['required', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'ifsc' => ['nullable', 'string', 'max:20'],
            'branch' => ['nullable', 'string', 'max:255'],
            'account_type' => ['required', Rule::in(['bank', 'cash', 'credit_card', 'upi'])],
            'opening_balance' => ['required', 'numeric'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
