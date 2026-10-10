<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BranchService
{
    /**
     * A company's very first branch is always its head — no manual step needed for
     * the common case of a company that only ever has one branch.
     */
    public function create(array $data, Company $company): Branch
    {
        return DB::transaction(function () use ($data, $company) {
            $isFirstBranch = ! Branch::where('company_id', $company->id)->exists();

            $branch = Branch::create([
                ...$data,
                'company_id' => $company->id,
            ]);

            if ($isFirstBranch) {
                $this->makeHead($branch);
            }

            return $branch;
        });
    }

    /**
     * Unsets any previous head for the company before promoting this one — the
     * unique index on primary_company_id would catch a mistake here regardless,
     * but clearing first keeps the intent obvious.
     */
    public function makeHead(Branch $branch): Branch
    {
        return DB::transaction(function () use ($branch) {
            Branch::where('company_id', $branch->company_id)->update(['primary_company_id' => null]);
            $branch->update(['primary_company_id' => $branch->company_id]);

            return $branch->refresh();
        });
    }

    public function update(Branch $branch, array $data): Branch
    {
        if ($branch->is_primary && ($data['status'] ?? $branch->status) === 'inactive') {
            throw new RuntimeException('This is the head branch and cannot be deactivated. Make another branch head first.');
        }

        $branch->update($data);

        return $branch;
    }
}
