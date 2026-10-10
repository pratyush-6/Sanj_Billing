<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            // Null for every non-head branch, company_id for the one head branch.
            // A unique index on a nullable column lets MySQL allow unlimited NULLs
            // but only one row per company_id value — the database itself guarantees
            // a company can never end up with two head branches.
            $table->foreignId('primary_company_id')->nullable()->after('company_id')
                ->unique()->constrained('companies')->nullOnDelete();
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->boolean('is_primary')->storedAs('primary_company_id is not null')->after('primary_company_id');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('is_primary');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['primary_company_id']);
            $table->dropUnique(['primary_company_id']);
            $table->dropColumn('primary_company_id');
        });
    }
};
