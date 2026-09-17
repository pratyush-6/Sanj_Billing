<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\GstRateRequest;
use App\Models\GstRate;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;

class GstRateController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private MasterDataService $masterDataService) {}

    public function index()
    {
        $gstRates = GstRate::where('company_id', current_company()?->id)->orderBy('rate')->get();

        return view('masters.gst-rates.index', ['gstRates' => $gstRates]);
    }

    public function create()
    {
        return view('masters.gst-rates.create');
    }

    public function store(GstRateRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();

        $this->masterDataService->create(GstRate::class, [
            ...$request->validated(),
            'company_id' => $company->id,
        ], 'GST Rate');

        return redirect()->route('gst-rates.index')->with('status', 'GST rate created.');
    }

    public function edit(GstRate $gstRate)
    {
        $this->ensureBelongsToCurrentCompany($gstRate);

        return view('masters.gst-rates.edit', ['gstRate' => $gstRate]);
    }

    public function update(GstRateRequest $request, GstRate $gstRate): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($gstRate);

        $this->masterDataService->update($gstRate, $request->validated(), 'GST Rate');

        return redirect()->route('gst-rates.index')->with('status', 'GST rate updated.');
    }
}
