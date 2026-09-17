<?php

namespace App\Services;

use App\Models\Company;
use App\Models\FinancialYear;
use App\Models\Party;
use App\Models\Payment;
use App\Models\User;
use App\Services\Concerns\GeneratesSequentialNumbers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentService
{
    use GeneratesSequentialNumbers;

    public function __construct(
        private AuditLogService $auditLog,
        private AccountingService $accountingService,
    ) {}

    /**
     * $source is whatever document this payment settles (a PurchaseBill today,
     * a SaleInvoice once that exists) — kept generic here so this service stays
     * reusable across both rather than knowing about either concretely.
     */
    public function create(array $data, Company $company, FinancialYear $financialYear, User $creator, string $direction, Party $party, Model $source): Payment
    {
        if ($financialYear->is_locked) {
            throw new RuntimeException('This financial year is locked. Payments cannot be recorded.');
        }

        $amount = round((float) $data['amount'], 2);

        if ($amount <= 0) {
            throw new RuntimeException('Payment amount must be greater than zero.');
        }

        if (method_exists($source, 'amountDue')) {
            $due = $source->amountDue();
            if ($amount > $due + 0.01) {
                throw new RuntimeException("Payment of {$amount} exceeds the amount due ({$due}).");
            }
        }

        return DB::transaction(function () use ($data, $company, $financialYear, $creator, $direction, $party, $source, $amount) {
            $payment = Payment::create([
                'company_id' => $company->id,
                'financial_year_id' => $financialYear->id,
                'party_id' => $party->id,
                'direction' => $direction,
                'payment_number' => $this->generateSequentialNumber(Payment::class, 'PAY', $company, $financialYear),
                'status' => 'Posted',
                'amount' => $amount,
                'payment_date' => $data['payment_date'],
                'bank_account_id' => $data['bank_account_id'],
                'payment_method_id' => $data['payment_method_id'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'source_type' => $source->getMorphClass(),
                'source_id' => $source->getKey(),
                'created_by' => $creator->id,
            ]);

            $payment->load('company', 'financialYear', 'bankAccount.account');

            $this->accountingService->recordPayment($payment);

            $this->auditLog->log('Payment Recorded', 'Payment', $payment, null, $payment->toArray());

            return $payment;
        });
    }

    public function cancel(Payment $payment): Payment
    {
        if ($payment->status === 'Cancelled') {
            throw new RuntimeException('This payment is already cancelled.');
        }

        return DB::transaction(function () use ($payment) {
            $payment->load('company', 'financialYear', 'bankAccount.account');

            $this->accountingService->voidPayment($payment);
            $payment->update(['status' => 'Cancelled']);

            $this->auditLog->log('Payment Cancelled', 'Payment', $payment, null, ['status' => 'Cancelled']);

            return $payment;
        });
    }
}
