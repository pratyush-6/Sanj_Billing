<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\VendorProductRequest;
use App\Models\Vendor;
use App\Models\VendorProduct;
use App\Services\AuditLogService;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;

class VendorProductController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(
        private MasterDataService $masterDataService,
        private AuditLogService $auditLog,
    ) {}

    public function store(VendorProductRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($vendor);

        $this->masterDataService->create(VendorProduct::class, [
            ...$request->validated(),
            'vendor_id' => $vendor->id,
        ], 'Vendor Product');

        return redirect()->route('vendors.show', $vendor)->with('status', 'Product pricing added.');
    }

    public function update(VendorProductRequest $request, Vendor $vendor, VendorProduct $vendorProduct): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($vendor);
        abort_unless($vendorProduct->vendor_id === $vendor->id, 404);

        $this->masterDataService->update($vendorProduct, $request->validated(), 'Vendor Product');

        return redirect()->route('vendors.show', $vendor)->with('status', 'Product pricing updated.');
    }

    public function destroy(Vendor $vendor, VendorProduct $vendorProduct): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($vendor);
        abort_unless($vendorProduct->vendor_id === $vendor->id, 404);

        $this->auditLog->log('Vendor Product Removed', 'Vendor Product', null, $vendorProduct->toArray(), null);
        $vendorProduct->delete();

        return redirect()->route('vendors.show', $vendor)->with('status', 'Product pricing removed.');
    }
}
