<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = current_company()?->id;
        $branch = $this->route('branch');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('branches', 'code')->where('company_id', $companyId)->ignore($branch),
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'state' => ['nullable', Rule::in(config('india.states'))],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', Rule::exists('company_user', 'user_id')->where('company_id', $companyId)],
        ];
    }
}
