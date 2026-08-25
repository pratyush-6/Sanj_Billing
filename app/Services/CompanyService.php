<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    public function __construct(private AuditLogService $auditLog) {}

    public function save(array $data, ?Company $company = null): Company
    {
        return DB::transaction(function () use ($data, $company) {
            if ($company) {
                $old = $company->toArray();
                $company->update($data);
                $this->auditLog->log('Company Updated', 'Company', $company, $old, $company->toArray());

                return $company;
            }

            $company = Company::create($data);
            $this->auditLog->log('Company Created', 'Company', $company, null, $company->toArray());

            return $company;
        });
    }
}
