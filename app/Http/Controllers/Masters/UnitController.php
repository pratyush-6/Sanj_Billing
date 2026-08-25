<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\Company;
use App\Models\Unit;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;

class UnitController extends Controller
{
    public function __construct(private MasterDataService $masterDataService) {}

    public function index()
    {
        return view('masters.units.index', ['units' => Unit::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('masters.units.create');
    }

    public function store(UnitRequest $request): RedirectResponse
    {
        $company = Company::firstOrFail();

        $this->masterDataService->create(Unit::class, [
            ...$request->validated(),
            'company_id' => $company->id,
        ], 'Unit');

        return redirect()->route('units.index')->with('status', 'Unit created.');
    }

    public function edit(Unit $unit)
    {
        return view('masters.units.edit', ['unit' => $unit]);
    }

    public function update(UnitRequest $request, Unit $unit): RedirectResponse
    {
        $this->masterDataService->update($unit, $request->validated(), 'Unit');

        return redirect()->route('units.index')->with('status', 'Unit updated.');
    }
}
