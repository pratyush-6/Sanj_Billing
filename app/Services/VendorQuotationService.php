<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\User;
use App\Models\VendorQuotation;
use App\Services\Concerns\GeneratesSequentialNumbers;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VendorQuotationService
{
    use GeneratesSequentialNumbers;

    public function __construct(private AuditLogService $auditLog) {}

    public function create(array $data, Company $company, FinancialYear $financialYear, User $creator): VendorQuotation
    {
        return DB::transaction(function () use ($data, $company, $financialYear, $creator) {
            $items = $data['items'];
            unset($data['items']);

            $quotation = VendorQuotation::create([
                ...$data,
                'company_id' => $company->id,
                'financial_year_id' => $financialYear->id,
                'quotation_number' => $this->generateSequentialNumber(VendorQuotation::class, 'QUO', $company, $financialYear),
                'status' => 'Draft',
                'created_by' => $creator->id,
            ]);

            $this->syncItems($quotation, $items);

            $this->auditLog->log('Vendor Quotation Created', 'Vendor Quotation', $quotation, null, $quotation->toArray());

            return $quotation;
        });
    }

    public function update(VendorQuotation $quotation, array $data): VendorQuotation
    {
        if ($quotation->status !== 'Draft') {
            throw new RuntimeException('Only draft quotations can be edited.');
        }

        return DB::transaction(function () use ($quotation, $data) {
            $old = $quotation->toArray();
            $items = $data['items'];
            unset($data['items']);

            $quotation->update($data);
            $this->syncItems($quotation, $items);

            $this->auditLog->log('Vendor Quotation Updated', 'Vendor Quotation', $quotation, $old, $quotation->toArray());

            return $quotation;
        });
    }

    public function submit(VendorQuotation $quotation): VendorQuotation
    {
        if ($quotation->status !== 'Draft') {
            throw new RuntimeException('Only draft quotations can be submitted.');
        }

        if ($quotation->items()->count() === 0) {
            throw new RuntimeException('Add at least one line item before submitting.');
        }

        $quotation->update(['status' => 'Submitted']);
        $this->auditLog->log('Vendor Quotation Submitted', 'Vendor Quotation', $quotation, null, ['status' => 'Submitted']);

        return $quotation;
    }

    private function syncItems(VendorQuotation $quotation, array $items): void
    {
        $quotation->items()->delete();

        foreach ($items as $item) {
            $quantity = round((float) $item['quantity'], 2);
            $unitPrice = round((float) $item['unit_price'], 2);

            $quotation->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'amount' => round($quantity * $unitPrice, 2),
                'notes' => $item['notes'] ?? null,
            ]);
        }
    }
}
