<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategoryRequest;
use App\Models\ProductCategory;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;

class ProductCategoryController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private MasterDataService $masterDataService) {}

    public function index()
    {
        $categories = ProductCategory::where('company_id', current_company()?->id)->orderBy('name')->get();

        return view('product-categories.index', ['categories' => $categories]);
    }

    public function create()
    {
        return view('product-categories.create');
    }

    public function store(ProductCategoryRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();

        $this->masterDataService->create(ProductCategory::class, [
            ...$request->validated(),
            'company_id' => $company->id,
        ], 'Product Category');

        return redirect()->route('product-categories.index')->with('status', 'Product category created.');
    }

    public function edit(ProductCategory $productCategory)
    {
        $this->ensureBelongsToCurrentCompany($productCategory);

        return view('product-categories.edit', ['category' => $productCategory]);
    }

    public function update(ProductCategoryRequest $request, ProductCategory $productCategory): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($productCategory);

        $this->masterDataService->update($productCategory, $request->validated(), 'Product Category');

        return redirect()->route('product-categories.index')->with('status', 'Product category updated.');
    }
}
