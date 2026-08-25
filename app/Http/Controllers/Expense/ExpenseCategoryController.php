<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseCategoryRequest;
use App\Models\Company;
use App\Models\ExpenseCategory;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;

class ExpenseCategoryController extends Controller
{
    public function __construct(private MasterDataService $masterDataService) {}

    public function index()
    {
        $categories = ExpenseCategory::with('subCategories')->orderBy('name')->get();

        return view('expense-categories.index', ['categories' => $categories]);
    }

    public function create()
    {
        return view('expense-categories.create', ['natureOptions' => config('expense.nature_options')]);
    }

    public function store(ExpenseCategoryRequest $request): RedirectResponse
    {
        $company = Company::firstOrFail();

        $this->masterDataService->create(ExpenseCategory::class, [
            ...$request->validated(),
            'company_id' => $company->id,
        ], 'Expense Category');

        return redirect()->route('expense-categories.index')->with('status', 'Category created.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expense-categories.edit', [
            'category' => $expenseCategory,
            'natureOptions' => config('expense.nature_options'),
        ]);
    }

    public function update(ExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $this->masterDataService->update($expenseCategory, $request->validated(), 'Expense Category');

        return redirect()->route('expense-categories.index')->with('status', 'Category updated.');
    }
}
