<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Party extends Model
{
    use HasFactory;

    protected $table = 'parties';

    protected $fillable = [
        'company_id',
        'name',
        'is_vendor',
        'is_customer',
        'company_name',
        'contact_person',
        'mobile',
        'email',
        'address',
        'state',
        'city',
        'pincode',
        'gstin',
        'pan',
        'bank_name',
        'account_number',
        'ifsc',
        'payment_terms',
        'status',
    ];

    protected $casts = [
        'is_vendor' => 'boolean',
        'is_customer' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'vendor_id');
    }

    public function vendorProducts(): HasMany
    {
        return $this->hasMany(VendorProduct::class, 'vendor_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(VendorQuotation::class, 'vendor_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'vendor_id');
    }

    public function purchaseBills(): HasMany
    {
        return $this->hasMany(PurchaseBill::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function saleOrders(): HasMany
    {
        return $this->hasMany(SaleOrder::class);
    }

    public function deliveryChallans(): HasMany
    {
        return $this->hasMany(DeliveryChallan::class);
    }

    public function saleInvoices(): HasMany
    {
        return $this->hasMany(SaleInvoice::class);
    }
}
