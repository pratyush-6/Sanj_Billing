<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'expense_nature' => ['nullable', 'string', Rule::in(config('expense.nature_options'))],
            'tax_applicable' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
