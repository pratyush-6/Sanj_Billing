<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;

class ExpenseCategoryController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private MasterDataService $masterDataService) {}

    public function index()
    {
        $categories = ExpenseCategory::where('company_id', current_company()?->id)
            ->with('subCategories')
            ->orderBy('name')
            ->get();

        return view('expense-categories.index', ['categories' => $categories]);
    }

    public function create()
    {
        return view('expense-categories.create', ['natureOptions' => config('expense.nature_options')]);
    }

    public function store(ExpenseCategoryRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();

        $this->masterDataService->create(ExpenseCategory::class, [
            ...$request->validated(),
            'company_id' => $company->id,
        ], 'Expense Category');

        return redirect()->route('expense-categories.index')->with('status', 'Category created.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        $this->ensureBelongsToCurrentCompany($expenseCategory);

        return view('expense-categories.edit', [
            'category' => $expenseCategory,
            'natureOptions' => config('expense.nature_options'),
        ]);
    }

    public function update(ExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($expenseCategory);

        $this->masterDataService->update($expenseCategory, $request->validated(), 'Expense Category');

        return redirect()->route('expense-categories.index')->with('status', 'Category updated.');
    }
}
