<?php

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Company::all()->each(function (Company $company) {
            $existingHead = Branch::where('company_id', $company->id)->whereNotNull('primary_company_id')->exists();

            if ($existingHead) {
                return;
            }

            $earliestBranch = Branch::where('company_id', $company->id)->orderBy('id')->first();

            if ($earliestBranch) {
                $earliestBranch->update(['primary_company_id' => $company->id]);

                return;
            }

            Branch::create([
                'company_id' => $company->id,
                'primary_company_id' => $company->id,
                'name' => 'Head Office',
                'code' => 'HO',
                'status' => 'active',
            ]);
        });
    }

    public function down(): void
    {
        // Intentionally irreversible: there is no record of which branches this
        // backfill created vs. promoted, so undoing it would be a guess.
    }
};
