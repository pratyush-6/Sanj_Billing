<?php

namespace App\Http\Requests;

use App\Models\ExpenseCategory;
use App\Services\ExpenseService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            try {
                $amounts = app(ExpenseService::class)->calculateAmounts($this->all());
            } catch (ValidationException) {
                return;
            }

            $tds = (float) ($this->input('tds_amount') ?? 0);

            if ($tds > $amounts['total_amount'] + 0.01) {
                $validator->errors()->add(
                    'tds_amount',
                    'TDS amount cannot exceed the total payable amount ('.number_format($amounts['total_amount'], 2).').',
                );
            }
        });
    }

    public function rules(): array
    {
        $companyId = current_company()?->id;

        return [
            'expense_date' => ['required', 'date'],
            'expense_category_id' => ['required', Rule::exists('expense_categories', 'id')->where('company_id', $companyId)],
            'expense_sub_category_id' => [
                'nullable',
                Rule::exists('expense_sub_categories', 'id')->whereIn(
                    'expense_category_id',
                    ExpenseCategory::where('company_id', $companyId)->pluck('id')
                ),
            ],
            'vendor_id' => ['nullable', Rule::exists('vendors', 'id')->where('company_id', $companyId)],
            'description' => ['nullable', 'string', 'max:500'],

            'quantity' => ['nullable', 'numeric', 'min:0', 'required_with:rate'],
            'unit_id' => ['nullable', Rule::exists('units', 'id')->where('company_id', $companyId)],
            'rate' => ['nullable', 'numeric', 'min:0', 'required_with:quantity'],
            'taxable_amount' => ['required_without_all:quantity,rate', 'nullable', 'numeric', 'min:0'],

            'discount' => ['nullable', 'numeric', 'min:0'],
            'gst_amount' => ['nullable', 'numeric', 'min:0'],
            'tds_amount' => ['nullable', 'numeric', 'min:0'],

            'nature_of_use' => ['required', Rule::in(config('expense.use_options'))],
            'business_amount' => ['required_if:nature_of_use,Mixed', 'nullable', 'numeric', 'min:0'],
            'personal_amount' => ['required_if:nature_of_use,Mixed', 'nullable', 'numeric', 'min:0'],

            'payment_method_id' => ['required', Rule::exists('payment_methods', 'id')->where('company_id', $companyId)],
            'bank_account_id' => ['required', Rule::exists('bank_accounts', 'id')->where('company_id', $companyId)],

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
