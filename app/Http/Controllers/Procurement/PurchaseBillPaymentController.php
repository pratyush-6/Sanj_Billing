<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\PurchaseBill;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class PurchaseBillPaymentController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private PaymentService $paymentService) {}

    public function store(PaymentRequest $request, PurchaseBill $purchaseBill): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($purchaseBill);

        if ($purchaseBill->status !== 'Posted') {
            return redirect()->route('purchase-bills.show', $purchaseBill)->with('error', 'Only a posted purchase bill can be paid.');
        }

        $company = current_company_or_fail();
        $financialYear = $company->activeFinancialYear();

        if (! $financialYear) {
            return redirect()->route('purchase-bills.show', $purchaseBill)->with('error', 'No active financial year. Please set one up first.');
        }

        $purchaseBill->load('payments', 'party');

        try {
            $this->paymentService->create($request->validated(), $company, $financialYear, Auth::user(), 'Out', $purchaseBill->party, $purchaseBill);
        } catch (RuntimeException $exception) {
            return redirect()->route('purchase-bills.show', $purchaseBill)->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-bills.show', $purchaseBill)->with('status', 'Payment recorded.');
    }

    public function cancel(PurchaseBill $purchaseBill, Payment $payment): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($purchaseBill);
        abort_unless($payment->source_type === PurchaseBill::class && $payment->source_id === $purchaseBill->id, 404);

        try {
            $this->paymentService->cancel($payment);
        } catch (RuntimeException $exception) {
            return redirect()->route('purchase-bills.show', $purchaseBill)->with('error', $exception->getMessage());
        }

        return redirect()->route('purchase-bills.show', $purchaseBill)->with('status', 'Payment cancelled.');
    }
}
