<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Expense;
use App\Models\FinancialYear;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ExpenseService
{
    public function __construct(
        private AuditLogService $auditLog,
        private DocumentService $documentService,
    ) {}

    public function calculateAmounts(array $data): array
    {
        if (! empty($data['quantity']) && ! empty($data['rate'])) {
            $taxableAmount = round((float) $data['quantity'] * (float) $data['rate'], 2);
        } else {
            $taxableAmount = round((float) ($data['taxable_amount'] ?? 0), 2);
        }

        $discount = round((float) ($data['discount'] ?? 0), 2);
        $gstAmount = round((float) ($data['gst_amount'] ?? 0), 2);
        $tdsAmount = round((float) ($data['tds_amount'] ?? 0), 2);
        $totalAmount = round($taxableAmount - $discount + $gstAmount, 2);

        $natureOfUse = $data['nature_of_use'] ?? 'Business';

        [$businessAmount, $personalAmount] = match ($natureOfUse) {
            'Personal' => [0, $totalAmount],
            'Mixed' => [
                round((float) ($data['business_amount'] ?? 0), 2),
                round((float) ($data['personal_amount'] ?? 0), 2),
            ],
            default => [$totalAmount, 0],
        };

        if ($natureOfUse === 'Mixed' && abs(($businessAmount + $personalAmount) - $totalAmount) > 0.01) {
            throw ValidationException::withMessages([
                'business_amount' => 'Business + Personal amount must equal the total amount ('.number_format($totalAmount, 2).').',
            ]);
        }

        return [
            'taxable_amount' => $taxableAmount,
            'discount' => $discount,
            'gst_amount' => $gstAmount,
            'tds_amount' => $tdsAmount,
            'total_amount' => $totalAmount,
            'business_amount' => $businessAmount,
            'personal_amount' => $personalAmount,
        ];
    }

    public function findDuplicate(int $companyId, ?int $vendorId, ?string $invoiceNumber, ?string $invoiceDate, float $totalAmount, ?int $excludeId = null): ?Expense
    {
        if (! $vendorId || ! $invoiceNumber || ! $invoiceDate) {
            return null;
        }

        return Expense::query()
            ->where('company_id', $companyId)
            ->where('vendor_id', $vendorId)
            ->where('invoice_number', $invoiceNumber)
            ->where('invoice_date', $invoiceDate)
            ->where('total_amount', $totalAmount)
            ->where('status', '!=', 'Cancelled')
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->first();
    }

    public function generateExpenseNumber(Company $company, FinancialYear $financialYear): string
    {
        $fyCode = Str::of($financialYear->name)->after('FY ')->replace([' ', '/'], '-')->__toString();

        return DB::transaction(function () use ($company, $financialYear, $fyCode) {
            $count = Expense::where('company_id', $company->id)
                ->where('financial_year_id', $financialYear->id)
                ->lockForUpdate()
                ->count();

            return "EXP-{$fyCode}-".str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
        });
    }

    public function create(array $data, Company $company, FinancialYear $financialYear, User $creator, array $files = []): Expense
    {
        if ($financialYear->is_locked) {
            throw new RuntimeException('This financial year is locked. New expenses cannot be added.');
        }

        return DB::transaction(function () use ($data, $company, $financialYear, $creator, $files) {
            $amounts = $this->calculateAmounts($data);

            $expense = Expense::create([
                ...$data,
                ...$amounts,
                'company_id' => $company->id,
                'financial_year_id' => $financialYear->id,
                'expense_number' => $this->generateExpenseNumber($company, $financialYear),
                'created_by' => $creator->id,
                'status' => $data['status'] ?? 'Approved',
            ]);

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $this->documentService->store($expense, $file, 'Bill');
                }
            }

            $this->auditLog->log('Expense Created', 'Expense', $expense, null, $expense->toArray());

            return $expense;
        });
    }

    public function update(Expense $expense, array $data, array $files = []): Expense
    {
        if ($expense->status === 'Cancelled') {
            throw new RuntimeException('Cancelled expenses cannot be edited.');
        }

        if ($expense->financialYear->is_locked) {
            throw new RuntimeException('This financial year is locked. This expense cannot be edited.');
        }

        return DB::transaction(function () use ($expense, $data, $files) {
            $old = $expense->toArray();
            $amounts = $this->calculateAmounts($data);

            $expense->update([...$data, ...$amounts]);

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $this->documentService->store($expense, $file, 'Bill');
                }
            }

            $this->auditLog->log('Expense Updated', 'Expense', $expense, $old, $expense->toArray());

            return $expense;
        });
    }

    public function cancel(Expense $expense): Expense
    {
        if ($expense->financialYear->is_locked) {
            throw new RuntimeException('This financial year is locked. This expense cannot be cancelled.');
        }

        $expense->update(['status' => 'Cancelled']);
        $this->auditLog->log('Expense Cancelled', 'Expense', $expense, null, ['status' => 'Cancelled']);

        return $expense;
    }
}
