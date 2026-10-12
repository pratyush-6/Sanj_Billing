<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\TdsSection;
use Illuminate\Database\Seeder;

class TdsSectionSeeder extends Seeder
{
    /**
     * The standard Indian TDS sections most small/medium businesses need. Rates are
     * the common non-individual/non-senior-citizen rate for each section — companies
     * can add their own sections or adjust these from Masters > TDS Sections.
     *
     * @return array<int, array{section: string, description: string, rate: float}>
     */
    public static function defaults(): array
    {
        return [
            ['section' => '194A', 'description' => 'Interest other than interest on securities', 'rate' => 10.00],
            ['section' => '194C', 'description' => 'Payment to contractors and sub-contractors', 'rate' => 2.00],
            ['section' => '194D', 'description' => 'Insurance commission', 'rate' => 5.00],
            ['section' => '194H', 'description' => 'Commission or brokerage', 'rate' => 5.00],
            ['section' => '194I(a)', 'description' => 'Rent on plant and machinery', 'rate' => 2.00],
            ['section' => '194I(b)', 'description' => 'Rent on land, building or furniture', 'rate' => 10.00],
            ['section' => '194J', 'description' => 'Fees for professional or technical services', 'rate' => 10.00],
            ['section' => '194Q', 'description' => 'Purchase of goods', 'rate' => 0.10],
        ];
    }

    /**
     * Backfills every existing company — safe to re-run, firstOrCreate keyed by
     * (company_id, section) never duplicates or overwrites a section someone has
     * already customised. New companies get the same list automatically via
     * CompanyService::seedDefaultMasters(), which calls self::defaults() directly.
     */
    public function run(): void
    {
        Company::all()->each(function (Company $company) {
            foreach (self::defaults() as $section) {
                TdsSection::firstOrCreate(
                    ['company_id' => $company->id, 'section' => $section['section']],
                    ['description' => $section['description'], 'rate' => $section['rate'], 'status' => 'active'],
                );
            }
        });
    }
}
