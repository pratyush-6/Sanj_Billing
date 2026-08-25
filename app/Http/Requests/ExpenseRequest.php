<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_date' => ['required', 'date'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'expense_sub_category_id' => ['nullable', 'exists:expense_sub_categories,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'description' => ['nullable', 'string', 'max:500'],

            'quantity' => ['nullable', 'numeric', 'min:0', 'required_with:rate'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'rate' => ['nullable', 'numeric', 'min:0', 'required_with:quantity'],
            'taxable_amount' => ['required_without_all:quantity,rate', 'nullable', 'numeric', 'min:0'],

            'discount' => ['nullable', 'numeric', 'min:0'],
            'gst_amount' => ['nullable', 'numeric', 'min:0'],
            'tds_amount' => ['nullable', 'numeric', 'min:0'],

            'nature_of_use' => ['required', Rule::in(config('expense.use_options'))],
            'business_amount' => ['required_if:nature_of_use,Mixed', 'nullable', 'numeric', 'min:0'],
            'personal_amount' => ['required_if:nature_of_use,Mixed', 'nullable', 'numeric', 'min:0'],

            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],

            'invoice_number' => ['nullable', 'string', 'max:100'],
            'invoice_date' => ['nullable', 'date'],
            'expense_nature' => ['nullable', 'string', Rule::in(config('expense.nature_options'))],

            'status' => ['nullable', Rule::in(['Draft', 'Approved'])],
            'notes' => ['nullable', 'string'],

            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            'confirm_duplicate' => ['nullable', 'boolean'],
        ];
    }
}
