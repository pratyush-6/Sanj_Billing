<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorRequest;
use App\Models\Company;
use App\Models\Vendor;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct(private MasterDataService $masterDataService) {}

    public function index(Request $request)
    {
        $vendors = Vendor::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('vendors.index', ['vendors' => $vendors]);
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function store(VendorRequest $request): RedirectResponse
    {
        $company = Company::firstOrFail();

        $this->masterDataService->create(Vendor::class, [
            ...$request->validated(),
            'company_id' => $company->id,
        ], 'Vendor');

        return redirect()->route('vendors.index')->with('status', 'Vendor created.');
    }

    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', ['vendor' => $vendor]);
    }

    public function update(VendorRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->masterDataService->update($vendor, $request->validated(), 'Vendor');

        return redirect()->route('vendors.index')->with('status', 'Vendor updated.');
    }

    public function show(Vendor $vendor)
    {
        $expenses = $vendor->expenses()->latest('expense_date')->paginate(15);

        return view('vendors.show', ['vendor' => $vendor, 'expenses' => $expenses]);
    }
}
