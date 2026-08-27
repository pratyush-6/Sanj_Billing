<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    use EnsuresCompanyOwnership;

    public function index(Request $request)
    {
        $company = current_company_or_fail();
        $financialYearId = $request->filled('financial_year_id')
            ? $request->integer('financial_year_id')
            : $company->activeFinancialYear()?->id;

        $entries = JournalEntry::where('company_id', $company->id)
            ->when($financialYearId, fn ($query) => $query->where('financial_year_id', $financialYearId))
            ->with('items.account')
            ->latest('entry_date')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('accounting.journal.index', [
            'entries' => $entries,
            'financialYears' => $company->financialYears()->orderByDesc('start_date')->get(),
            'selectedFinancialYearId' => $financialYearId,
        ]);
    }

    public function show(JournalEntry $journalEntry)
    {
        $this->ensureBelongsToCurrentCompany($journalEntry);

        return view('accounting.journal.show', [
            'entry' => $journalEntry->load(['items.account', 'reverses', 'reversedBy', 'creator', 'source']),
        ]);
    }
}
