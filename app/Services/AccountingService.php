<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FinancialYear;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AccountingService
{
    public function seedChartOfAccounts(Company $company): void
    {
        if (Account::where('company_id', $company->id)->exists()) {
            return;
        }

        $assets = $this->createSystemAccount($company->id, 'Assets', 'Asset', 'assets-group');
        $this->createSystemAccount($company->id, 'Cash & Bank', 'Asset', 'cash-bank-group', $assets->id);
        $this->createSystemAccount($company->id, 'Accounts Receivable', 'Asset', 'accounts-receivable', $assets->id);
        $this->createSystemAccount($company->id, 'Fixed Assets', 'Asset', 'fixed-assets', $assets->id);
        $this->createSystemAccount($company->id, 'Input GST', 'Asset', 'input-gst', $assets->id);
        $this->createSystemAccount($company->id, 'Other Assets', 'Asset', 'other-assets', $assets->id);

        $liabilities = $this->createSystemAccount($company->id, 'Liabilities', 'Liability', 'liabilities-group');
        $this->createSystemAccount($company->id, 'Accounts Payable', 'Liability', 'accounts-payable', $liabilities->id);
        $this->createSystemAccount($company->id, 'GST Payable', 'Liability', 'gst-payable', $liabilities->id);
        $this->createSystemAccount($company->id, 'TDS Payable', 'Liability', 'tds-payable', $liabilities->id);
        $this->createSystemAccount($company->id, 'Loans', 'Liability', 'loans', $liabilities->id);
        $this->createSystemAccount($company->id, 'Other Liabilities', 'Liability', 'other-liabilities', $liabilities->id);

        $equity = $this->createSystemAccount($company->id, 'Equity', 'Equity', 'equity-group');
        $this->createSystemAccount($company->id, 'Capital', 'Equity', 'capital', $equity->id);
        $this->createSystemAccount($company->id, 'Retained Earnings', 'Equity', 'retained-earnings', $equity->id);
        $this->createSystemAccount($company->id, 'Drawings / Personal Use', 'Equity', 'drawings', $equity->id);

        $income = $this->createSystemAccount($company->id, 'Income', 'Income', 'income-group');
        $this->createSystemAccount($company->id, 'Sales', 'Income', 'sales', $income->id);
        $this->createSystemAccount($company->id, 'Service Income', 'Income', 'service-income', $income->id);
        $this->createSystemAccount($company->id, 'Other Income', 'Income', 'other-income', $income->id);

        $this->createSystemAccount($company->id, 'Expenses', 'Expense', 'expenses-group');
    }

    public function mapExpenseCategory(ExpenseCategory $category): void
    {
        if ($category->account_id) {
            $category->account()->update(['name' => $category->name]);

            return;
        }

        $parent = $this->controlAccount($category->company_id, 'expenses-group');

        if (! $parent) {
            return;
        }

        $account = Account::create([
            'company_id' => $category->company_id,
            'parent_account_id' => $parent->id,
            'name' => $category->name,
            'type' => 'Expense',
            'is_system' => false,
            'status' => 'active',
        ]);

        $category->forceFill(['account_id' => $account->id])->saveQuietly();
    }

    public function mapBankAccount(BankAccount $bankAccount): void
    {
        if ($bankAccount->account_id) {
            $bankAccount->account()->update(['name' => $bankAccount->account_name]);

            return;
        }

        $parent = $this->controlAccount($bankAccount->company_id, 'cash-bank-group');

        if (! $parent) {
            return;
        }

        $account = Account::create([
            'company_id' => $bankAccount->company_id,
            'parent_account_id' => $parent->id,
            'name' => $bankAccount->account_name,
            'type' => 'Asset',
            'is_system' => false,
            'status' => 'active',
        ]);

        $bankAccount->forceFill(['account_id' => $account->id])->saveQuietly();

        $openingBalance = round((float) $bankAccount->opening_balance, 2);

        if ($openingBalance != 0) {
            $this->postOpeningBalance($bankAccount->company_id, $account, $openingBalance);
        }
    }

    /**
     * An opening balance is real money the account already held — it must enter the
     * books as a proper journal entry against Capital, not as a bare number bolted onto
     * the account. Otherwise the account carries value with no offsetting entry anywhere,
     * and the trial balance/balance sheet stop balancing as soon as any other transaction
     * is posted.
     */
    private function postOpeningBalance(int $companyId, Account $account, float $amount): void
    {
        $company = Company::find($companyId);
        $financialYear = $company?->activeFinancialYear()
            ?? FinancialYear::where('company_id', $companyId)->orderBy('start_date')->first();

        $capital = $this->controlAccount($companyId, 'capital');

        if (! $company || ! $financialYear || ! $capital) {
            return;
        }

        $lines = $amount > 0
            ? [
                ['account_id' => $account->id, 'debit' => $amount, 'credit' => 0],
                ['account_id' => $capital->id, 'debit' => 0, 'credit' => $amount],
            ]
            : [
                ['account_id' => $capital->id, 'debit' => abs($amount), 'credit' => 0],
                ['account_id' => $account->id, 'debit' => 0, 'credit' => abs($amount)],
            ];

        $this->postJournalEntry($company, $financialYear, $financialYear->start_date, "Opening balance: {$account->name}", null, $lines);
    }

    public function record(Expense $expense): ?JournalEntry
    {
        $totalAmount = (float) $expense->total_amount;

        if ($expense->status !== 'Approved' || $totalAmount == 0.0) {
            return null;
        }

        $expenseAccount = $expense->category?->account;
        $bankAccount = $expense->bankAccount?->account;

        if (! $expenseAccount || ! $bankAccount) {
            return null;
        }

        $r = (float) $expense->business_amount / $totalAmount;

        $lines = [];

        $expensePortion = round(((float) $expense->taxable_amount - (float) $expense->discount) * $r, 2);
        if ($expensePortion != 0) {
            $lines[] = ['account_id' => $expenseAccount->id, 'debit' => $expensePortion, 'credit' => 0];
        }

        $gstPortion = round((float) $expense->gst_amount * $r, 2);
        if ($gstPortion != 0 && ($inputGst = $this->controlAccount($expense->company_id, 'input-gst'))) {
            $lines[] = ['account_id' => $inputGst->id, 'debit' => $gstPortion, 'credit' => 0];
        }

        $personalPortion = round((float) $expense->personal_amount, 2);
        if ($personalPortion != 0 && ($drawings = $this->controlAccount($expense->company_id, 'drawings'))) {
            $lines[] = ['account_id' => $drawings->id, 'debit' => $personalPortion, 'credit' => 0];
        }

        $tdsAmount = round((float) $expense->tds_amount, 2);
        $bankOutflow = round($totalAmount - $tdsAmount, 2);

        if ($bankOutflow < 0) {
            // TDS withheld can never exceed the total payment — this expense's numbers
            // don't represent a coherent transaction. Refuse to post a broken entry
            // (e.g. a negative credit) rather than silently corrupting the books.
            return null;
        }

        if ($tdsAmount != 0 && ($tdsPayable = $this->controlAccount($expense->company_id, 'tds-payable'))) {
            $lines[] = ['account_id' => $tdsPayable->id, 'debit' => 0, 'credit' => $tdsAmount];
        }

        if ($bankOutflow != 0) {
            $lines[] = ['account_id' => $bankAccount->id, 'debit' => 0, 'credit' => $bankOutflow];
        }

        return $this->postJournalEntry(
            $expense->company,
            $expense->financialYear,
            $expense->expense_date,
            "Expense {$expense->expense_number}",
            $expense,
            $lines,
        );
    }

    public function revise(Expense $expense): void
    {
        $this->reverseSourceEntries($expense);

        if ($expense->status === 'Approved') {
            $this->record($expense);
        }
    }

    public function void(Expense $expense): void
    {
        $this->reverseSourceEntries($expense);
    }

    private function reverseSourceEntries(Expense $expense): void
    {
        $expense->journalEntries()->where('status', 'Posted')->get()
            ->each(fn (JournalEntry $entry) => $this->reverseJournalEntry($entry));
    }

    private function reverseJournalEntry(JournalEntry $entry): JournalEntry
    {
        return DB::transaction(function () use ($entry) {
            $reversal = JournalEntry::create([
                'company_id' => $entry->company_id,
                'financial_year_id' => $entry->financial_year_id,
                'entry_date' => now()->toDateString(),
                'entry_number' => $this->generateEntryNumber($entry->company, $entry->financialYear),
                'narration' => "Reversal of {$entry->entry_number}",
                'source_type' => $entry->source_type,
                'source_id' => $entry->source_id,
                'status' => 'Posted',
                'reverses_journal_entry_id' => $entry->id,
                'created_by' => Auth::id(),
            ]);

            foreach ($entry->items as $item) {
                $reversal->items()->create([
                    'account_id' => $item->account_id,
                    'debit' => $item->credit,
                    'credit' => $item->debit,
                    'narration' => $item->narration,
                ]);
            }

            $entry->update(['status' => 'Reversed']);

            return $reversal;
        });
    }

    private function postJournalEntry(Company $company, FinancialYear $financialYear, $entryDate, string $narration, ?Model $source, array $lines): ?JournalEntry
    {
        if (empty($lines)) {
            return null;
        }

        foreach ($lines as $line) {
            if ($line['debit'] < 0 || $line['credit'] < 0) {
                throw new RuntimeException('Journal entry line cannot carry a negative debit or credit.');
            }
        }

        $totalDebit = round(array_sum(array_column($lines, 'debit')), 2);
        $totalCredit = round(array_sum(array_column($lines, 'credit')), 2);

        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new RuntimeException("Journal entry does not balance: debit {$totalDebit} vs credit {$totalCredit}.");
        }

        return DB::transaction(function () use ($company, $financialYear, $entryDate, $narration, $source, $lines) {
            $entry = JournalEntry::create([
                'company_id' => $company->id,
                'financial_year_id' => $financialYear->id,
                'entry_date' => $entryDate,
                'entry_number' => $this->generateEntryNumber($company, $financialYear),
                'narration' => $narration,
                'source_type' => $source?->getMorphClass(),
                'source_id' => $source?->getKey(),
                'status' => 'Posted',
                'created_by' => Auth::id(),
            ]);

            foreach ($lines as $line) {
                $entry->items()->create($line);
            }

            return $entry;
        });
    }

    private function generateEntryNumber(Company $company, FinancialYear $financialYear): string
    {
        $fyCode = Str::of($financialYear->name)->after('FY ')->replace([' ', '/'], '-')->__toString();

        return DB::transaction(function () use ($company, $financialYear, $fyCode) {
            $count = JournalEntry::where('company_id', $company->id)
                ->where('financial_year_id', $financialYear->id)
                ->lockForUpdate()
                ->count();

            return "JE-{$fyCode}-".str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
        });
    }

    public function ledger(Account $account, ?string $from = null, ?string $to = null): array
    {
        $openingBalance = $from
            ? $this->computeBalances(collect([$account]), null, Carbon::parse($from)->subDay()->toDateString())[$account->id] ?? 0.0
            : 0.0;

        $items = JournalEntryItem::where('account_id', $account->id)
            ->whereHas('journalEntry', function ($query) use ($from, $to) {
                $query->when($from, fn ($q) => $q->whereDate('entry_date', '>=', $from))
                    ->when($to, fn ($q) => $q->whereDate('entry_date', '<=', $to));
            })
            ->with('journalEntry')
            ->get()
            ->sortBy(fn ($item) => $item->journalEntry->entry_date)
            ->values();

        $running = $openingBalance;

        $rows = $items->map(function ($item) use (&$running, $account) {
            $delta = $account->isDebitNormal()
                ? ((float) $item->debit - (float) $item->credit)
                : ((float) $item->credit - (float) $item->debit);
            $running += $delta;

            return (object) [
                'date' => $item->journalEntry->entry_date,
                'particulars' => $item->journalEntry->narration,
                'reference' => $item->journalEntry->entry_number,
                'debit' => (float) $item->debit,
                'credit' => (float) $item->credit,
                'balance' => $running,
            ];
        });

        return ['opening_balance' => $openingBalance, 'rows' => $rows, 'closing_balance' => $running];
    }

    public function trialBalance(Company $company, ?string $asOfDate = null): Collection
    {
        $accounts = Account::where('company_id', $company->id)
            ->whereDoesntHave('children')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $balances = $this->computeBalances($accounts, null, $asOfDate);

        return $accounts->map(function ($account) use ($balances) {
            $balance = $balances[$account->id] ?? 0.0;
            $debitNormal = $account->isDebitNormal();

            return (object) [
                'account' => $account,
                'debit' => $debitNormal ? max($balance, 0) : max(-$balance, 0),
                'credit' => $debitNormal ? max(-$balance, 0) : max($balance, 0),
            ];
        })->filter(fn ($row) => $row->debit != 0 || $row->credit != 0)->values();
    }

    public function profitAndLoss(Company $company, FinancialYear $financialYear): array
    {
        $incomeAccounts = Account::where('company_id', $company->id)->where('type', 'Income')->whereDoesntHave('children')->get();
        $expenseAccounts = Account::where('company_id', $company->id)->where('type', 'Expense')->whereDoesntHave('children')->get();

        $from = $financialYear->start_date->toDateString();
        $to = $financialYear->end_date->toDateString();

        $incomeBalances = $this->computeBalances($incomeAccounts, $from, $to);
        $expenseBalances = $this->computeBalances($expenseAccounts, $from, $to);

        $totalIncome = array_sum($incomeBalances);
        $totalExpense = array_sum($expenseBalances);

        $categoriesByAccountId = ExpenseCategory::where('company_id', $company->id)
            ->whereNotNull('account_id')
            ->get()
            ->keyBy('account_id');

        $expensesByNature = collect(config('expense.nature_options'))->mapWithKeys(fn ($nature) => [$nature => 0.0]);

        foreach ($expenseAccounts as $account) {
            $nature = $categoriesByAccountId->get($account->id)?->expense_nature ?? 'Other';
            $expensesByNature[$nature] = ($expensesByNature[$nature] ?? 0) + ($expenseBalances[$account->id] ?? 0);
        }

        return [
            'income_accounts' => $incomeAccounts->map(fn ($a) => (object) ['name' => $a->name, 'amount' => $incomeBalances[$a->id] ?? 0])->filter(fn ($r) => $r->amount != 0)->values(),
            'total_income' => $totalIncome,
            'expenses_by_nature' => $expensesByNature->filter(fn ($v) => $v != 0),
            'total_expense' => $totalExpense,
            'profit_before_tax' => $totalIncome - $totalExpense,
        ];
    }

    public function balanceSheet(Company $company, ?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?: now()->toDateString();

        $assetAccounts = Account::where('company_id', $company->id)->where('type', 'Asset')->whereDoesntHave('children')->get();
        $liabilityAccounts = Account::where('company_id', $company->id)->where('type', 'Liability')->whereDoesntHave('children')->get();
        $equityAccounts = Account::where('company_id', $company->id)->where('type', 'Equity')->whereDoesntHave('children')->get();

        $assetBalances = $this->computeBalances($assetAccounts, null, $asOfDate);
        $liabilityBalances = $this->computeBalances($liabilityAccounts, null, $asOfDate);
        $equityBalances = $this->computeBalances($equityAccounts, null, $asOfDate);

        $totalAssets = array_sum($assetBalances);
        $totalLiabilities = array_sum($liabilityBalances);
        $totalEquityExplicit = array_sum($equityBalances);

        $activeFinancialYear = $company->activeFinancialYear();
        $currentYearProfit = 0.0;
        $retainedEarnings = 0.0;

        if ($activeFinancialYear) {
            $incomeAccounts = Account::where('company_id', $company->id)->where('type', 'Income')->whereDoesntHave('children')->get();
            $expenseAccounts = Account::where('company_id', $company->id)->where('type', 'Expense')->whereDoesntHave('children')->get();

            $fyStart = $activeFinancialYear->start_date->toDateString();
            $currentYearTo = min($asOfDate, $activeFinancialYear->end_date->toDateString());

            $currentYearProfit = array_sum($this->computeBalances($incomeAccounts, $fyStart, $currentYearTo))
                - array_sum($this->computeBalances($expenseAccounts, $fyStart, $currentYearTo));

            $priorTo = $activeFinancialYear->start_date->copy()->subDay()->toDateString();

            $retainedEarnings = array_sum($this->computeBalances($incomeAccounts, null, $priorTo))
                - array_sum($this->computeBalances($expenseAccounts, null, $priorTo));
        }

        $totalEquity = $totalEquityExplicit + $currentYearProfit + $retainedEarnings;

        return [
            'as_of_date' => $asOfDate,
            'assets' => $assetAccounts->map(fn ($a) => (object) ['name' => $a->name, 'amount' => $assetBalances[$a->id] ?? 0])->filter(fn ($r) => $r->amount != 0)->values(),
            'total_assets' => $totalAssets,
            'liabilities' => $liabilityAccounts->map(fn ($a) => (object) ['name' => $a->name, 'amount' => $liabilityBalances[$a->id] ?? 0])->filter(fn ($r) => $r->amount != 0)->values(),
            'total_liabilities' => $totalLiabilities,
            'equity' => $equityAccounts->map(fn ($a) => (object) ['name' => $a->name, 'amount' => $equityBalances[$a->id] ?? 0])->filter(fn ($r) => $r->amount != 0)->values(),
            'current_year_profit' => $currentYearProfit,
            'retained_earnings' => $retainedEarnings,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
            'balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }

    public function liveBalances(Collection $accounts, ?string $asOfDate = null): array
    {
        return $this->computeBalances($accounts, null, $asOfDate);
    }

    public function liveBalance(Account $account, ?string $asOfDate = null): float
    {
        return $this->liveBalances(collect([$account]), $asOfDate)[$account->id] ?? 0.0;
    }

    /**
     * Every balance is derived purely from posted journal entries — including opening
     * balances, which postOpeningBalance() posts as a real entry against Capital rather
     * than storing as a bare number. That keeps this the single source of truth: no
     * separate "opening_balance + journal deltas" bookkeeping that could double-count or
     * drift, and the trial balance always balances by construction.
     */
    private function computeBalances(Collection $accounts, ?string $from, ?string $to): array
    {
        if ($accounts->isEmpty()) {
            return [];
        }

        $sums = JournalEntryItem::whereIn('account_id', $accounts->pluck('id'))
            ->whereHas('journalEntry', function ($query) use ($from, $to) {
                $query->when($from, fn ($q) => $q->whereDate('entry_date', '>=', $from))
                    ->when($to, fn ($q) => $q->whereDate('entry_date', '<=', $to));
            })
            ->selectRaw('account_id, SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        return $accounts->mapWithKeys(function ($account) use ($sums) {
            $sum = $sums->get($account->id);
            $debit = (float) ($sum->total_debit ?? 0);
            $credit = (float) ($sum->total_credit ?? 0);
            $delta = $account->isDebitNormal() ? $debit - $credit : $credit - $debit;

            return [$account->id => $delta];
        })->all();
    }

    private function controlAccount(int $companyId, string $key): ?Account
    {
        return Account::where('company_id', $companyId)->where('key', $key)->first();
    }

    private function createSystemAccount(int $companyId, string $name, string $type, ?string $key = null, ?int $parentId = null): Account
    {
        return Account::create([
            'company_id' => $companyId,
            'parent_account_id' => $parentId,
            'name' => $name,
            'type' => $type,
            'key' => $key,
            'is_system' => true,
            'status' => 'active',
        ]);
    }
}
