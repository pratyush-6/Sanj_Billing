<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use App\Services\BranchContextService;
use Illuminate\Http\RedirectResponse;

class BranchController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    public function index()
    {
        $company = current_company_or_fail();

        return view('settings.branches.index', [
            'branches' => Branch::where('company_id', $company->id)->withCount('users')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('settings.branches.form', [
            'branch' => null,
            'companyUsers' => $this->companyUsers(),
            'assignedUserIds' => [],
        ]);
    }

    public function store(BranchRequest $request): RedirectResponse
    {
        $company = current_company_or_fail();

        $branch = Branch::create([...$request->safe()->except('user_ids'), 'company_id' => $company->id]);
        $branch->users()->sync($request->input('user_ids', []));

        return redirect()->route('branches.index')->with('status', "Branch {$branch->name} created.");
    }

    public function edit(Branch $branch)
    {
        $this->ensureBelongsToCurrentCompany($branch);

        return view('settings.branches.form', [
            'branch' => $branch,
            'companyUsers' => $this->companyUsers(),
            'assignedUserIds' => $branch->users()->pluck('users.id')->all(),
        ]);
    }

    public function update(BranchRequest $request, Branch $branch): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($branch);

        $branch->update($request->safe()->except('user_ids'));
        $branch->users()->sync($request->input('user_ids', []));

        return redirect()->route('branches.index')->with('status', "Branch {$branch->name} saved.");
    }

    public function switch(Branch $branch): RedirectResponse
    {
        if (! $this->branchContext->switchTo($branch->id)) {
            abort(403);
        }

        return redirect()->route('dashboard')->with('status', "Switched to branch {$branch->name}.");
    }

    public function switchAll(): RedirectResponse
    {
        $this->branchContext->switchToAll();

        return redirect()->route('dashboard')->with('status', 'Showing all branches.');
    }

    private function ensureBelongsToCurrentCompany(Branch $branch): void
    {
        abort_unless($branch->company_id === current_company_or_fail()->id, 404);
    }

    private function companyUsers()
    {
        return current_company_or_fail()->users()->orderBy('name')->get();
    }
}
