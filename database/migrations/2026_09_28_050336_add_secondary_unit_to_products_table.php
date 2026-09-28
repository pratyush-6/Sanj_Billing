<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('secondary_unit_id')->nullable()->after('unit_id')->constrained('units')->nullOnDelete();
            $table->decimal('conversion_factor', 15, 4)->nullable()->after('secondary_unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['secondary_unit_id']);
            $table->dropColumn(['secondary_unit_id', 'conversion_factor']);
        });
    }
};
