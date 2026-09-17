<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\TdsSectionRequest;
use App\Models\TdsSection;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;

class TdsSectionController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private MasterDataService $masterDataService) {}

    public function index()
    {
        $tdsSections = TdsSection::where('company_id', current_company()?->id)->orderBy('section')->get();

        return view('masters.tds-sections.index', ['tdsSections' => $tdsSections]);
    }

    public function create()
    {
        return view('masters.tds-sections.create');
    }

    public function store(TdsSectionRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();

        $this->masterDataService->create(TdsSection::class, [
            ...$request->validated(),
            'company_id' => $company->id,
        ], 'TDS Section');

        return redirect()->route('tds-sections.index')->with('status', 'TDS section created.');
    }

    public function edit(TdsSection $tdsSection)
    {
        $this->ensureBelongsToCurrentCompany($tdsSection);

        return view('masters.tds-sections.edit', ['tdsSection' => $tdsSection]);
    }

    public function update(TdsSectionRequest $request, TdsSection $tdsSection): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($tdsSection);

        $this->masterDataService->update($tdsSection, $request->validated(), 'TDS Section');

        return redirect()->route('tds-sections.index')->with('status', 'TDS section updated.');
    }
}
