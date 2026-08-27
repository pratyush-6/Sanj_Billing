<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FinancialYear;
use App\Models\JournalEntryItem;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\AccountingService;
use App\Services\ExpenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingServiceTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private FinancialYear $financialYear;

    private ExpenseCategory $category;

    private BankAccount $bankAccount;

    private PaymentMethod $paymentMethod;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'Test Co', 'status' => 'active']);

        $this->financialYear = FinancialYear::create([
            'company_id' => $this->company->id,
            'name' => 'FY 2026-27',
            'start_date' => '2026-04-01',
            'end_date' => '2027-03-31',
            'is_active' => true,
        ]);

        $this->category = ExpenseCategory::create([
            'company_id' => $this->company->id,
            'name' => 'Electricity',
            'expense_nature' => 'Operating Expense',
            'status' => 'active',
        ]);

        $this->bankAccount = BankAccount::create([
            'company_id' => $this->company->id,
            'account_name' => 'HDFC Current',
            'account_type' => 'bank',
            'opening_balance' => 10000,
            'status' => 'active',
        ]);

        $this->paymentMethod = PaymentMethod::create([
            'company_id' => $this->company->id,
            'name' => 'Bank Transfer',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create();
    }

    private function createExpense(array $overrides = []): Expense
    {
        $data = array_merge([
            'expense_date' => '2026-05-10',
            'expense_category_id' => $this->category->id,
            'taxable_amount' => 1000,
            'discount' => 0,
            'gst_amount' => 180,
            'tds_amount' => 100,
            'nature_of_use' => 'Business',
            'payment_method_id' => $this->paymentMethod->id,
            'bank_account_id' => $this->bankAccount->id,
            'status' => 'Approved',
        ], $overrides);

        return app(ExpenseService::class)->create($data, $this->company, $this->financialYear, $this->user);
    }

    public function test_expense_posts_a_balanced_journal_entry(): void
    {
        $expense = $this->createExpense();

        $entry = $expense->journalEntries()->sole();

        $this->assertSame('Posted', $entry->status);
        $this->assertEqualsWithDelta(
            (float) $entry->items->sum('debit'),
            (float) $entry->items->sum('credit'),
            0.01,
        );

        // taxable(1000) + gst(180) = 1180 total; tds(100) withheld; bank outflow = 1080
        $this->assertEqualsWithDelta(1000.0, (float) $entry->items->where('account_id', $this->category->fresh()->account_id)->sum('debit'), 0.01);
        $this->assertEqualsWithDelta(1080.0, (float) $entry->items->where('account_id', $this->bankAccount->fresh()->account_id)->sum('credit'), 0.01);
    }

    public function test_personal_expense_posts_to_drawings_not_expense_account(): void
    {
        $expense = $this->createExpense([
            'nature_of_use' => 'Personal',
            'gst_amount' => 0,
            'tds_amount' => 0,
        ]);

        $entry = $expense->journalEntries()->sole();
        $categoryAccountId = $this->category->fresh()->account_id;

        $this->assertSame(0, $entry->items->where('account_id', $categoryAccountId)->count());

        $drawings = \App\Models\Account::where('company_id', $this->company->id)->where('key', 'drawings')->firstOrFail();
        $this->assertEqualsWithDelta(1000.0, (float) $entry->items->where('account_id', $drawings->id)->sum('debit'), 0.01);
    }

    public function test_mixed_expense_splits_proportionally_between_expense_and_drawings(): void
    {
        $expense = $this->createExpense([
            'nature_of_use' => 'Mixed',
            'business_amount' => 708.0, // 60% of total (1180)
            'personal_amount' => 472.0, // 40% of total
        ]);

        $entry = $expense->journalEntries()->sole();
        $categoryAccountId = $this->category->fresh()->account_id;
        $drawings = \App\Models\Account::where('company_id', $this->company->id)->where('key', 'drawings')->firstOrFail();

        // r = 708 / 1180 = 0.6 -> expense portion = 0.6 * 1000 = 600, gst portion = 0.6 * 180 = 108
        $this->assertEqualsWithDelta(600.0, (float) $entry->items->where('account_id', $categoryAccountId)->sum('debit'), 0.5);
        $this->assertEqualsWithDelta(472.0, (float) $entry->items->where('account_id', $drawings->id)->sum('debit'), 0.01);
        $this->assertEqualsWithDelta(
            (float) $entry->items->sum('debit'),
            (float) $entry->items->sum('credit'),
            0.01,
        );
    }

    public function test_updating_expense_reverses_old_entry_and_posts_new_one(): void
    {
        $expense = $this->createExpense(['taxable_amount' => 1000, 'gst_amount' => 0, 'tds_amount' => 0]);
        $originalEntry = $expense->journalEntries()->sole();

        app(ExpenseService::class)->update($expense, [
            'expense_date' => '2026-05-10',
            'expense_category_id' => $this->category->id,
            'taxable_amount' => 2000,
            'discount' => 0,
            'gst_amount' => 0,
            'tds_amount' => 0,
            'nature_of_use' => 'Business',
            'payment_method_id' => $this->paymentMethod->id,
            'bank_account_id' => $this->bankAccount->id,
        ]);

        $originalEntry->refresh();
        $this->assertSame('Reversed', $originalEntry->status);

        $accountingService = app(AccountingService::class);
        $bankAccount = $this->bankAccount->fresh();
        $balance = $accountingService->liveBalance($bankAccount->account);

        // opening 10000 - 2000 (new expense amount) = 8000; the reversed+reposted net effect
        $this->assertEqualsWithDelta(8000.0, $balance, 0.01);
    }

    public function test_cancelling_expense_reverses_journal_entry_to_net_zero(): void
    {
        $expense = $this->createExpense(['taxable_amount' => 1500, 'gst_amount' => 0, 'tds_amount' => 0]);

        app(ExpenseService::class)->cancel($expense);

        $entries = $expense->journalEntries()->get();
        $this->assertCount(2, $entries); // original (Reversed) + reversal (Posted)

        $itemIds = $entries->pluck('id');
        $netDebit = JournalEntryItem::whereIn('journal_entry_id', $itemIds)->sum('debit');
        $netCredit = JournalEntryItem::whereIn('journal_entry_id', $itemIds)->sum('credit');

        $this->assertEqualsWithDelta((float) $netDebit, (float) $netCredit, 0.01);

        $accountingService = app(AccountingService::class);
        $balance = $accountingService->liveBalance($this->bankAccount->fresh()->account);
        $this->assertEqualsWithDelta(10000.0, $balance, 0.01);
    }

    public function test_trial_balance_always_balances(): void
    {
        $this->createExpense(['taxable_amount' => 1000, 'gst_amount' => 180, 'tds_amount' => 100]);
        $this->createExpense(['taxable_amount' => 500, 'gst_amount' => 0, 'tds_amount' => 0, 'nature_of_use' => 'Personal']);

        $rows = app(AccountingService::class)->trialBalance($this->company, '2027-03-31');

        $this->assertEqualsWithDelta((float) $rows->sum('debit'), (float) $rows->sum('credit'), 0.01);
    }

    public function test_zero_amount_expense_does_not_post_a_journal_entry(): void
    {
        $expense = $this->createExpense(['taxable_amount' => 0, 'gst_amount' => 0, 'tds_amount' => 0]);

        $this->assertSame(0, $expense->journalEntries()->count());
    }
}
