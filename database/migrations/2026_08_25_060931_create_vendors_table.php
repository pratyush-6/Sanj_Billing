<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('gstin', 20)->nullable();
            $table->string('pan', 20)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc', 20)->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['company_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
