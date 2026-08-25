<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CompanyContextService
{
    public function current(): ?Company
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        $companyId = Session::get('current_company_id');

        if ($companyId) {
            $company = $this->accessibleCompaniesQuery($user)->find($companyId);

            if ($company) {
                return $company;
            }
        }

        $company = $this->accessibleCompaniesQuery($user)->orderBy('name')->first();

        if ($company) {
            Session::put('current_company_id', $company->id);
        }

        return $company;
    }

    public function accessibleCompanies(): Collection
    {
        $user = Auth::user();

        if (! $user) {
            return new Collection;
        }

        return $this->accessibleCompaniesQuery($user)->orderBy('name')->get();
    }

    public function switchTo(int $companyId): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        $company = $this->accessibleCompaniesQuery($user)->find($companyId);

        if (! $company) {
            return false;
        }

        Session::put('current_company_id', $company->id);

        return true;
    }

    private function accessibleCompaniesQuery(User $user): Builder
    {
        if ($user->hasRole('Super Admin')) {
            return Company::query();
        }

        return Company::query()->whereHas('users', fn ($query) => $query->where('users.id', $user->id));
    }
}
