<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('vendors', 'parties');

        Schema::table('parties', function (Blueprint $table) {
            $table->boolean('is_vendor')->default(true)->after('name');
            $table->boolean('is_customer')->default(false)->after('is_vendor');
        });
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn(['is_vendor', 'is_customer']);
        });

        Schema::rename('parties', 'vendors');
    }
};
