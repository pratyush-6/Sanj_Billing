<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TdsSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = current_company()?->id;
        $tdsSection = $this->route('tdsSection');

        return [
            'section' => [
                'required', 'string', 'max:20',
                Rule::unique('tds_sections', 'section')->where('company_id', $companyId)->ignore($tdsSection),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
