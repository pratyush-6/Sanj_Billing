<?php

namespace App\Services;

use App\Models\QuotationApproval;
use App\Models\User;
use App\Models\VendorQuotation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuotationApprovalService
{
    public function __construct(private AuditLogService $auditLog) {}

    public function act(VendorQuotation $quotation, User $approver, string $decision, ?string $comments = null): QuotationApproval
    {
        if ($quotation->status !== 'Submitted') {
            throw new RuntimeException('Only submitted quotations can be approved or rejected.');
        }

        if ($quotation->created_by === $approver->id) {
            throw new RuntimeException('You cannot approve or reject a quotation you submitted yourself.');
        }

        return DB::transaction(function () use ($quotation, $approver, $decision, $comments) {
            $approval = QuotationApproval::create([
                'vendor_quotation_id' => $quotation->id,
                'approver_id' => $approver->id,
                'level' => 1,
                'status' => $decision,
                'comments' => $comments,
                'acted_at' => now(),
            ]);

            $quotation->update(['status' => $decision]);

            $this->auditLog->log("Vendor Quotation {$decision}", 'Vendor Quotation', $quotation, null, ['status' => $decision, 'comments' => $comments]);

            return $approval;
        });
    }
}
