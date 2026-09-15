<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = current_company()?->id;
        $vendorProduct = $this->route('vendorProduct');

        // product_id is only submitted (and only changeable) when adding a new
        // pricing row; the inline edit-in-place row form only ever updates
        // price/sku/lead-time/preferred, never re-points it at a different product.
        $productIdRules = $vendorProduct
            ? ['prohibited']
            : [
                'required',
                Rule::exists('products', 'id')->where('company_id', $companyId),
                Rule::unique('vendor_products', 'product_id')->where('vendor_id', $this->route('vendor')?->id),
            ];

        return [
            'product_id' => $productIdRules,
            'vendor_sku' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'is_preferred' => ['nullable', 'boolean'],
        ];
    }
}
