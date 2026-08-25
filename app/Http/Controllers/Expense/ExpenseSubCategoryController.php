<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseSubCategoryRequest;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubCategory;
use App\Services\MasterDataService;
use Illuminate\Http\RedirectResponse;

class ExpenseSubCategoryController extends Controller
{
    public function __construct(private MasterDataService $masterDataService) {}

    public function index()
    {
        $subCategories = ExpenseSubCategory::with('category')->orderBy('name')->get();

        return view('expense-sub-categories.index', ['subCategories' => $subCategories]);
    }

    public function create()
    {
        return view('expense-sub-categories.create', [
            'categories' => ExpenseCategory::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(ExpenseSubCategoryRequest $request): RedirectResponse
    {
        $this->masterDataService->create(ExpenseSubCategory::class, $request->validated(), 'Expense Sub Category');

        return redirect()->route('expense-sub-categories.index')->with('status', 'Sub category created.');
    }

    public function edit(ExpenseSubCategory $expenseSubCategory)
    {
        return view('expense-sub-categories.edit', [
            'subCategory' => $expenseSubCategory,
            'categories' => ExpenseCategory::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(ExpenseSubCategoryRequest $request, ExpenseSubCategory $expenseSubCategory): RedirectResponse
    {
        $this->masterDataService->update($expenseSubCategory, $request->validated(), 'Expense Sub Category');

        return redirect()->route('expense-sub-categories.index')->with('status', 'Sub category updated.');
    }
}
