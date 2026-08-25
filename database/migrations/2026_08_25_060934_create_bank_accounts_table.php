<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('account_name');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc', 20)->nullable();
            $table->string('branch')->nullable();
            $table->string('account_type')->default('bank');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'account_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
