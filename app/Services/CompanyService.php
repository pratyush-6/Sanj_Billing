<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Company;
use App\Models\PaymentMethod;
use App\Models\Unit;
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
            $this->seedDefaultMasters($company);

            return $company;
        });
    }

    private function seedDefaultMasters(Company $company): void
    {
        foreach (['Cash', 'Bank', 'UPI', 'Credit Card', 'Debit Card', 'Cheque', 'NEFT', 'RTGS', 'IMPS', 'Other'] as $name) {
            PaymentMethod::create(['company_id' => $company->id, 'name' => $name]);
        }

        foreach (['Piece', 'Bottle', 'Kg', 'Gram', 'Liter', 'Meter', 'Hour', 'Day', 'Month', 'Box', 'Packet', 'Other'] as $name) {
            Unit::create(['company_id' => $company->id, 'name' => $name]);
        }

        BankAccount::create([
            'company_id' => $company->id,
            'account_name' => 'Cash',
            'account_type' => 'cash',
            'opening_balance' => 0,
        ]);
    }
}
