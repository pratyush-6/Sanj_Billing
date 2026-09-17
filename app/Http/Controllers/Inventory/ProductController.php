<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\GstRate;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Services\MasterDataService;
use App\Services\StockLevelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(
        private MasterDataService $masterDataService,
        private StockLevelService $stockLevelService,
    ) {}

    public function index(Request $request)
    {
        $companyId = current_company()?->id;

        $products = Product::where('company_id', $companyId)
            ->with(['category', 'unit'])
            ->when($request->filled('search'), fn ($query) => $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->string('search').'%')
                    ->orWhere('sku', 'like', '%'.$request->string('search').'%');
            }))
            ->when($request->filled('product_category_id'), fn ($query) => $query->where('product_category_id', $request->integer('product_category_id')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => ProductCategory::where('company_id', $companyId)->orderBy('name')->get(),
            'stockLevels' => $this->stockLevelService->currentStock(collect($products->items())),
        ]);
    }

    public function create()
    {
        return $this->formData();
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();

        $this->masterDataService->create(Product::class, [
            ...$request->validated(),
            'company_id' => $company->id,
        ], 'Product');

        return redirect()->route('products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product)
    {
        $this->ensureBelongsToCurrentCompany($product);

        return $this->formData($product);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($product);

        $this->masterDataService->update($product, $request->validated(), 'Product');

        return redirect()->route('products.index')->with('status', 'Product updated.');
    }

    private function formData(?Product $product = null)
    {
        $companyId = current_company()?->id;
        $view = $product ? 'products.edit' : 'products.create';

        return view($view, [
            'product' => $product,
            'categories' => ProductCategory::where('company_id', $companyId)->where('status', 'active')->orderBy('name')->get(),
            'units' => Unit::where('company_id', $companyId)->where('status', 'active')->orderBy('name')->get(),
            'gstRates' => GstRate::where('company_id', $companyId)->where('status', 'active')->orderBy('rate')->get(),
        ]);
    }
}
