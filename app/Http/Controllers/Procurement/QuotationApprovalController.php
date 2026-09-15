<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuotationApprovalRequest;
use App\Models\VendorQuotation;
use App\Services\QuotationApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class QuotationApprovalController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private QuotationApprovalService $approvalService) {}

    public function store(QuotationApprovalRequest $request, VendorQuotation $quotation): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($quotation);

        $decision = $request->string('decision')->toString();

        try {
            $this->approvalService->act($quotation, Auth::user(), $decision, $request->input('comments'));
        } catch (RuntimeException $exception) {
            return redirect()->route('vendor-quotations.show', $quotation)->with('error', $exception->getMessage());
        }

        return redirect()->route('vendor-quotations.show', $quotation)->with('status', "Quotation {$decision}.");
    }
}
