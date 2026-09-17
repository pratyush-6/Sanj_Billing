<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GstRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = current_company()?->id;
        $gstRate = $this->route('gstRate');

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('gst_rates', 'name')->where('company_id', $companyId)->ignore($gstRate),
            ],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
