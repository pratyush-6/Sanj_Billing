<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Party;
use App\Models\PaymentMethod;
use App\Models\PurchaseOrder;
use App\Models\Unit;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ExpenseController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private ExpenseService $expenseService) {}

    public function index(Request $request)
    {
        $company = current_company();

        $expenses = Expense::query()
            ->with(['category', 'subCategory', 'vendor', 'paymentMethod', 'bankAccount', 'documents'])
            ->where('company_id', $company?->id)
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('expense_date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('expense_date', '<=', $request->date('date_to')))
            ->when($request->filled('expense_category_id'), fn ($query) => $query->where('expense_category_id', $request->integer('expense_category_id')))
            ->when($request->filled('vendor_id'), fn ($query) => $query->where('vendor_id', $request->integer('vendor_id')))
            ->when($request->filled('payment_method_id'), fn ($query) => $query->where('payment_method_id', $request->integer('payment_method_id')))
            ->when($request->filled('bank_account_id'), fn ($query) => $query->where('bank_account_id', $request->integer('bank_account_id')))
            ->when($request->filled('nature_of_use'), fn ($query) => $query->where('nature_of_use', $request->string('nature_of_use')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('financial_year_id'), fn ($query) => $query->where('financial_year_id', $request->integer('financial_year_id')))
            ->when($request->boolean('bill_available'), fn ($query) => $query->has('documents'))
            ->latest('expense_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('expenses.index', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::where('company_id', $company?->id)->orderBy('name')->get(),
            'vendors' => Party::where('company_id', $company?->id)->where('is_vendor', true)->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::where('company_id', $company?->id)->orderBy('name')->get(),
            'bankAccounts' => BankAccount::where('company_id', $company?->id)->orderBy('account_name')->get(),
            'financialYears' => $company?->financialYears()->orderByDesc('start_date')->get() ?? collect(),
            'natureOptions' => config('expense.use_options'),
        ]);
    }

    public function create()
    {
        return $this->formData();
    }

    public function store(ExpenseRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();
        $financialYear = $company->activeFinancialYear();

        if (! $financialYear) {
            return redirect()->route('expenses.create')->with('error', 'No active financial year. Please set one up first.');
        }

        $data = $request->validated();

        if (! $request->boolean('confirm_duplicate')) {
            $totalPreview = $this->expenseService->calculateAmounts($data)['total_amount'];
            $duplicate = $this->expenseService->findDuplicate(
                $company->id,
                $data['vendor_id'] ?? null,
                $data['invoice_number'] ?? null,
                $data['invoice_date'] ?? null,
                $totalPreview,
            );

            if ($duplicate) {
                return redirect()->route('expenses.create')
                    ->withInput($request->except('attachments'))
                    ->with('duplicate_warning', "A similar expense already exists: {$duplicate->expense_number} dated {$duplicate->expense_date->format('d-M-Y')} for ".number_format($duplicate->total_amount, 2).'.');
            }
        }

        try {
            $expense = $this->expenseService->create(
                $data,
                $company,
                $financialYear,
                Auth::user(),
                $request->file('attachments', []),
            );
        } catch (RuntimeException $exception) {
            return redirect()->route('expenses.create')->withInput($request->except('attachments'))->with('error', $exception->getMessage());
        }

        return redirect()->route('expenses.index')->with('status', "Expense {$expense->expense_number} saved.");
    }

    public function edit(Expense $expense)
    {
        $this->ensureBelongsToCurrentCompany($expense);

        return $this->formData($expense);
    }

    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($expense);

        $data = $request->validated();

        if (! $request->boolean('confirm_duplicate')) {
            $totalPreview = $this->expenseService->calculateAmounts($data)['total_amount'];
            $duplicate = $this->expenseService->findDuplicate(
                $expense->company_id,
                $data['vendor_id'] ?? null,
                $data['invoice_number'] ?? null,
                $data['invoice_date'] ?? null,
                $totalPreview,
                $expense->id,
            );

            if ($duplicate) {
                return redirect()->route('expenses.edit', $expense)
                    ->withInput($request->except('attachments'))
                    ->with('duplicate_warning', "A similar expense already exists: {$duplicate->expense_number} dated {$duplicate->expense_date->format('d-M-Y')} for ".number_format($duplicate->total_amount, 2).'.');
            }
        }

        try {
            $this->expenseService->update($expense, $data, $request->file('attachments', []));
        } catch (RuntimeException $exception) {
            return redirect()->route('expenses.edit', $expense)->withInput($request->except('attachments'))->with('error', $exception->getMessage());
        }

        return redirect()->route('expenses.index')->with('status', "Expense {$expense->expense_number} updated.");
    }

    public function cancel(Expense $expense): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($expense);

        try {
            $this->expenseService->cancel($expense);
        } catch (RuntimeException $exception) {
            return redirect()->route('expenses.index')->with('error', $exception->getMessage());
        }

        return redirect()->route('expenses.index')->with('status', "Expense {$expense->expense_number} cancelled.");
    }

    private function formData(?Expense $expense = null)
    {
        $company = current_company_or_fail();

        return view('expenses.form', [
            'expense' => $expense,
            'categories' => ExpenseCategory::where('company_id', $company->id)->where('status', 'active')->with('subCategories')->orderBy('name')->get(),
            'vendors' => Party::where('company_id', $company->id)->where('is_vendor', true)->where('status', 'active')->orderBy('name')->get(),
            'purchaseOrders' => PurchaseOrder::where('company_id', $company->id)->whereNotIn('status', ['Draft', 'Cancelled'])->with('vendor')->orderByDesc('po_date')->get(),
            'units' => Unit::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(),
            'bankAccounts' => BankAccount::where('company_id', $company->id)->where('status', 'active')->orderBy('account_name')->get(),
            'natureOptions' => config('expense.use_options'),
            'expenseNatureOptions' => config('expense.nature_options'),
            'activeFinancialYear' => $company->activeFinancialYear(),
        ]);
    }
}
