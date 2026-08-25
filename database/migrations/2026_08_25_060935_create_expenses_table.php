<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financial_year_id')->constrained()->restrictOnDelete();
            $table->string('expense_number');
            $table->date('expense_date');
            $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('expense_sub_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description')->nullable();

            $table->decimal('quantity', 15, 2)->nullable();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('rate', 15, 2)->nullable();

            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('gst_amount', 15, 2)->default(0);
            $table->decimal('tds_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->string('nature_of_use')->default('Business');
            $table->decimal('business_amount', 15, 2)->default(0);
            $table->decimal('personal_amount', 15, 2)->default(0);

            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();

            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('expense_nature')->nullable();

            $table->string('status')->default('draft');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'expense_number']);
            $table->index(['company_id', 'financial_year_id', 'expense_date']);
            $table->index(['vendor_id', 'invoice_number', 'invoice_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
