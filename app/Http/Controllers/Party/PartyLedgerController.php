<?php

namespace App\Http\Controllers\Party;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Services\PartyLedgerService;

class PartyLedgerController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private PartyLedgerService $partyLedgerService) {}

    public function index()
    {
        $company = current_company_or_fail();

        $balances = $this->partyLedgerService->outstandingBalances($company)
            ->sortByDesc(fn ($row) => abs($row['balance']))
            ->values();

        return view('parties.ledger.index', ['balances' => $balances]);
    }

    public function show(Party $party)
    {
        $this->ensureBelongsToCurrentCompany($party);

        return view('parties.ledger.show', [
            'party' => $party,
            'statement' => $this->partyLedgerService->statementFor($party),
            'itemHistory' => $this->partyLedgerService->itemWiseHistory($party),
        ]);
    }
}
