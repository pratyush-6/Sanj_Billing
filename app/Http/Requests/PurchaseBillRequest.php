<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = current_company()?->id;

        return [
            'bill_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:bill_date'],
            'tds_section_id' => ['nullable', Rule::exists('tds_sections', 'id')->where('company_id', $companyId)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
