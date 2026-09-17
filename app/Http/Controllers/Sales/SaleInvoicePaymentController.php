<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\SaleInvoice;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class SaleInvoicePaymentController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private PaymentService $paymentService) {}

    public function store(PaymentRequest $request, SaleInvoice $saleInvoice): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($saleInvoice);

        if ($saleInvoice->status !== 'Posted') {
            return redirect()->route('sale-invoices.show', $saleInvoice)->with('error', 'Only a posted sale invoice can receive payments.');
        }

        $company = current_company_or_fail();
        $financialYear = $company->activeFinancialYear();

        if (! $financialYear) {
            return redirect()->route('sale-invoices.show', $saleInvoice)->with('error', 'No active financial year. Please set one up first.');
        }

        $saleInvoice->load('payments', 'party');

        try {
            $this->paymentService->create($request->validated(), $company, $financialYear, Auth::user(), 'In', $saleInvoice->party, $saleInvoice);
        } catch (RuntimeException $exception) {
            return redirect()->route('sale-invoices.show', $saleInvoice)->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('sale-invoices.show', $saleInvoice)->with('status', 'Payment recorded.');
    }

    public function cancel(SaleInvoice $saleInvoice, Payment $payment): RedirectResponse
    {
        $this->ensureBelongsToCurrentCompany($saleInvoice);
        abort_unless($payment->source_type === SaleInvoice::class && $payment->source_id === $saleInvoice->id, 404);

        try {
            $this->paymentService->cancel($payment);
        } catch (RuntimeException $exception) {
            return redirect()->route('sale-invoices.show', $saleInvoice)->with('error', $exception->getMessage());
        }

        return redirect()->route('sale-invoices.show', $saleInvoice)->with('status', 'Payment cancelled.');
    }
}
