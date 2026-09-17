<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replaces the free-typed gst_rate decimal (added moments ago in Chunk 0b,
     * before the GST Rate Master existed) with a proper FK — no product has used
     * it yet, so this is a straight swap, not a data migration.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('gst_rate');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('gst_rate_id')->nullable()->after('hsn_code')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gst_rate_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('gst_rate', 5, 2)->nullable()->after('hsn_code');
        });
    }
};
