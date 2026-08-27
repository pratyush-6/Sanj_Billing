<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('code')->nullable();
            $table->string('name');
            $table->enum('type', ['Asset', 'Liability', 'Equity', 'Income', 'Expense']);
            $table->string('key')->nullable();
            $table->boolean('is_system')->default(false);
            $table->string('status')->default('active');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
