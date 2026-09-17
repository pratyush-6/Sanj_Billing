<?php

use App\Models\Company;
use App\Services\AccountingService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $accounting = app(AccountingService::class);

        Company::orderBy('id')->each(function (Company $company) use ($accounting) {
            $accounting->ensureTdsReceivableAccount($company);
        });
    }

    public function down(): void
    {
        // No-op: the TDS Receivable account is additive and safe to leave in place.
    }
};
