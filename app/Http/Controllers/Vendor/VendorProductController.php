<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\VendorProductRequest;
use App\Models\Party;
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

    public function store(VendorProductRequest $request, Party $party): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($party);

        $this->masterDataService->create(VendorProduct::class, [
            ...$request->validated(),
            'vendor_id' => $party->id,
        ], 'Vendor Product');

        return redirect()->route('parties.show', $party)->with('status', 'Product pricing added.');
    }

    public function update(VendorProductRequest $request, Party $party, VendorProduct $vendorProduct): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($party);
        abort_unless($vendorProduct->vendor_id === $party->id, 404);

        $this->masterDataService->update($vendorProduct, $request->validated(), 'Vendor Product');

        return redirect()->route('parties.show', $party)->with('status', 'Product pricing updated.');
    }

    public function destroy(Party $party, VendorProduct $vendorProduct): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($party);
        abort_unless($vendorProduct->vendor_id === $party->id, 404);

        $this->auditLog->log('Vendor Product Removed', 'Vendor Product', null, $vendorProduct->toArray(), null);
        $vendorProduct->delete();

        return redirect()->route('parties.show', $party)->with('status', 'Product pricing removed.');
    }
}
