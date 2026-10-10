<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guarded: on a server where this migration previously failed partway
        // through (adding a generated is_primary column after this one could error
        // on some MySQL/MariaDB builds — "head" is now derived in PHP instead, see
        // Branch::isPrimary()), this column may already exist from that earlier
        // attempt. Re-running must not try to add it — and its foreign key — twice.
        if (! Schema::hasColumn('branches', 'primary_company_id')) {
            Schema::table('branches', function (Blueprint $table) {
                // Null for every non-head branch, company_id for the one head branch.
                // A unique index on a nullable column lets MySQL allow unlimited
                // NULLs but only one row per company_id value — the database itself
                // guarantees a company can never end up with two head branches.
                $table->foreignId('primary_company_id')->nullable()->after('company_id')
                    ->unique()->constrained('companies')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['primary_company_id']);
            $table->dropUnique(['primary_company_id']);
            $table->dropColumn('primary_company_id');
        });
    }
};
