<?php

namespace App\Http\Controllers\Party;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\PartyRequest;
use App\Models\Party;
use App\Models\Product;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PartyController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private MasterDataService $masterDataService) {}

    public function index(Request $request)
    {
        $parties = Party::query()
            ->where('company_id', current_company()?->id)
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('parties.index', ['parties' => $parties]);
    }

    public function create()
    {
        return view('parties.create');
    }

    public function store(PartyRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();

        $this->masterDataService->create(Party::class, [
            ...$request->validated(),
            'company_id' => $company->id,
        ], 'Party');

        return redirect()->route('parties.index')->with('status', 'Party created.');
    }

    public function edit(Party $party)
    {
        $this->ensureBelongsToCurrentCompany($party);

        return view('parties.edit', ['party' => $party]);
    }

    public function update(PartyRequest $request, Party $party): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($party);

        $this->masterDataService->update($party, $request->validated(), 'Party');

        return redirect()->route('parties.index')->with('status', 'Party updated.');
    }

    public function show(Party $party)
    {
        $this->ensureBelongsToCurrentCompany($party);

        $expenses = $party->expenses()->latest('expense_date')->paginate(15);
        $vendorProducts = $party->vendorProducts()->with('product')->get();
        $availableProducts = Product::where('company_id', $party->company_id)
            ->where('status', 'active')
            ->whereNotIn('id', $vendorProducts->pluck('product_id'))
            ->orderBy('name')
            ->get();

        return view('parties.show', [
            'party' => $party,
            'expenses' => $expenses,
            'vendorProducts' => $vendorProducts,
            'availableProducts' => $availableProducts,
            'totalSpent' => (clone $party->expenses())->where('status', '!=', 'Cancelled')->sum('total_amount'),
        ]);
    }
}
