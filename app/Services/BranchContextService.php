<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BranchContextService
{
    public const ALL = 'all';

    public function __construct(private CompanyContextService $companyContext) {}

    public function current(): ?Branch
    {
        $user = Auth::user();

        if (! $user || $this->isAllBranches()) {
            return null;
        }

        $branchId = Session::get('current_branch_id');

        if ($branchId) {
            $branch = $this->accessibleBranchesQuery($user)->find($branchId);

            if ($branch) {
                return $branch;
            }
        }

        $branch = $this->accessibleBranchesQuery($user)->orderBy('name')->first();

        if ($branch) {
            Session::put('current_branch_id', $branch->id);
        }

        return $branch;
    }

    public function isAllBranches(): bool
    {
        return Session::get('current_branch_id') === self::ALL;
    }

    /**
     * @return array{branchId: ?int, allBranches: bool}
     */
    public function scope(): array
    {
        return [
            'branchId' => $this->current()?->id,
            'allBranches' => $this->isAllBranches(),
        ];
    }

    /**
     * Scope in the shape ReportService and AccountingService take: null is every
     * branch, otherwise the selected branch (null id meaning unassigned).
     */
    public function reportScope(): ?array
    {
        $scope = $this->scope();

        return $scope['allBranches'] ? null : ['branchId' => $scope['branchId']];
    }

    public function applyTo(Builder $query, string $column = 'branch_id'): Builder
    {
        $scope = $this->scope();

        if ($scope['allBranches']) {
            return $query;
        }

        return $scope['branchId'] === null
            ? $query->whereNull($column)
            : $query->where(fn ($query) => $query->where($column, $scope['branchId'])->orWhereNull($column));
    }

    public function accessibleBranches(): Collection
    {
        $user = Auth::user();

        if (! $user) {
            return new Collection;
        }

        return $this->accessibleBranchesQuery($user)->orderBy('name')->get();
    }

    public function switchTo(int $branchId): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        $branch = $this->accessibleBranchesQuery($user)->find($branchId);

        if (! $branch) {
            return false;
        }

        Session::put('current_branch_id', $branch->id);

        return true;
    }

    public function switchToAll(): bool
    {
        if (! Auth::user()) {
            return false;
        }

        Session::put('current_branch_id', self::ALL);

        return true;
    }

    public function reset(): void
    {
        Session::forget('current_branch_id');
    }

    private function accessibleBranchesQuery(User $user): Builder
    {
        $companyId = $this->companyContext->current()?->id;

        $query = Branch::query()->where('company_id', $companyId)->where('status', 'active');

        if ($user->hasRole('Super Admin')) {
            return $query;
        }

        return $query->whereHas('users', fn ($query) => $query->where('users.id', $user->id));
    }
}
