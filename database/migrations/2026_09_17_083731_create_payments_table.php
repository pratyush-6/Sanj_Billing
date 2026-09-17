<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financial_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('party_id')->constrained()->restrictOnDelete();
            $table->string('direction');
            $table->string('payment_number');
            $table->string('status')->default('Posted');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->foreignId('bank_account_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->nullableMorphs('source');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'payment_number']);
            $table->index(['party_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
