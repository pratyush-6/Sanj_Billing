<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('quotation_approvals');
        Schema::dropIfExists('vendor_quotation_items');
        Schema::dropIfExists('vendor_quotations');
    }

    public function down(): void
    {
        // Intentionally irreversible: the Vendor Quotation module is fully
        // removed, not disabled — its data is archived, not migrated forward.
    }
};
