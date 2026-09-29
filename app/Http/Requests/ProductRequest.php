<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = current_company()?->id;
        $product = $this->route('product');

        return [
            'sku' => [
                'nullable', 'string', 'max:100',
                Rule::unique('products', 'sku')->where('company_id', $companyId)->ignore($product),
            ],
            'name' => ['required', 'string', 'max:255'],
            'hsn_code' => ['nullable', 'string', 'max:20'],
            'gst_rate_id' => ['nullable', Rule::exists('gst_rates', 'id')->where('company_id', $companyId)],
            'product_category_id' => ['nullable', Rule::exists('product_categories', 'id')->where('company_id', $companyId)],
            'unit_id' => ['nullable', Rule::exists('units', 'id')->where('company_id', $companyId)],
            'secondary_unit_id' => ['nullable', 'different:unit_id', Rule::exists('units', 'id')->where('company_id', $companyId)],
            'conversion_factor' => ['nullable', 'numeric', 'min:0.0001', 'required_with:secondary_unit_id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'min_stock_level' => ['nullable', 'numeric', 'min:0'],
            'max_stock_level' => ['nullable', 'numeric', 'min:0', 'gte:min_stock_level'],
            'status' => ['required', Rule::in(config('inventory.statuses'))],
        ];
    }
}
