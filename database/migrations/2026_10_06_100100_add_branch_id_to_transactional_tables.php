<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $documentTables = [
        'expenses',
        'purchase_orders',
        'purchase_bills',
        'sale_orders',
        'delivery_challans',
        'sale_invoices',
        'payments',
        'stock_adjustments',
    ];

    public function up(): void
    {
        foreach ($this->documentTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->foreignId('branch_id')->nullable()->after('company_id')->constrained('branches')->restrictOnDelete();
                $table->index(['company_id', 'branch_id', 'financial_year_id'], "{$tableName}_company_branch_fy_index");
            });
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained('branches')->restrictOnDelete();
            $table->index(['company_id', 'branch_id', 'financial_year_id'], 'stock_movements_company_branch_fy_index');
            $table->index(['product_id', 'branch_id'], 'stock_movements_product_branch_index');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained('branches')->restrictOnDelete();
            $table->index(['company_id', 'branch_id', 'entry_date'], 'journal_entries_company_branch_date_index');
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->uuid('assignment_group')->nullable()->after('branch_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropColumn('assignment_group');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex('journal_entries_company_branch_date_index');
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stock_movements_product_branch_index');
            $table->dropIndex('stock_movements_company_branch_fy_index');
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        foreach (array_reverse($this->documentTables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropIndex("{$tableName}_company_branch_fy_index");
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }
    }
};
