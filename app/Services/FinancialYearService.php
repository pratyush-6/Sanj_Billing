<?php

namespace App\Services;

use App\Models\FinancialYear;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinancialYearService
{
    public function __construct(private AuditLogService $auditLog) {}

    public function create(array $data): FinancialYear
    {
        return DB::transaction(function () use ($data) {
            $financialYear = FinancialYear::create($data);

            $this->auditLog->log('Financial Year Created', 'Financial Year', $financialYear, null, $financialYear->toArray());

            return $financialYear;
        });
    }

    public function update(FinancialYear $financialYear, array $data): FinancialYear
    {
        if ($financialYear->is_locked) {
            throw new RuntimeException('Locked financial years cannot be modified.');
        }

        $old = $financialYear->toArray();
        $financialYear->update($data);

        $this->auditLog->log('Financial Year Updated', 'Financial Year', $financialYear, $old, $financialYear->toArray());

        return $financialYear;
    }

    public function activate(FinancialYear $financialYear): FinancialYear
    {
        if ($financialYear->is_closed) {
            throw new RuntimeException('Closed financial years cannot be activated.');
        }

        return DB::transaction(function () use ($financialYear) {
            FinancialYear::where('company_id', $financialYear->company_id)
                ->where('id', '!=', $financialYear->id)
                ->update(['is_active' => false]);

            $financialYear->update(['is_active' => true]);

            $this->auditLog->log('Financial Year Activated', 'Financial Year', $financialYear, null, ['is_active' => true]);

            return $financialYear;
        });
    }

    public function lock(FinancialYear $financialYear): FinancialYear
    {
        $financialYear->update(['is_locked' => true]);
        $this->auditLog->log('Financial Year Locked', 'Financial Year', $financialYear, null, ['is_locked' => true]);

        return $financialYear;
    }

    public function unlock(FinancialYear $financialYear): FinancialYear
    {
        $financialYear->update(['is_locked' => false]);
        $this->auditLog->log('Financial Year Unlocked', 'Financial Year', $financialYear, null, ['is_locked' => false]);

        return $financialYear;
    }

    public function close(FinancialYear $financialYear): FinancialYear
    {
        $financialYear->update(['is_closed' => true, 'is_active' => false, 'is_locked' => true]);
        $this->auditLog->log('Financial Year Closed', 'Financial Year', $financialYear, null, ['is_closed' => true]);

        return $financialYear;
    }
}
