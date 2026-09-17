<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'financial_year_id',
        'expense_number',
        'expense_date',
        'expense_category_id',
        'expense_sub_category_id',
        'vendor_id',
        'purchase_order_id',
        'description',
        'quantity',
        'unit_id',
        'rate',
        'taxable_amount',
        'discount',
        'gst_amount',
        'tds_amount',
        'total_amount',
        'nature_of_use',
        'business_amount',
        'personal_amount',
        'payment_method_id',
        'bank_account_id',
        'invoice_number',
        'invoice_date',
        'expense_nature',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'invoice_date' => 'date',
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'business_amount' => 'decimal:2',
        'personal_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseSubCategory::class, 'expense_sub_category_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'vendor_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function journalEntries(): MorphMany
    {
        return $this->morphMany(JournalEntry::class, 'source');
    }
}
