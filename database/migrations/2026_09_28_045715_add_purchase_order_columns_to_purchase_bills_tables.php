<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Goods Receipt is retired as an active workflow — new Purchase Bills
        // point straight at the Purchase Order/Item instead. The old columns
        // stay (made nullable) purely so historical bills keep resolving.
        Schema::table('purchase_bills', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')->nullable()->after('goods_receipt_id')->constrained()->restrictOnDelete();
            $table->foreignId('goods_receipt_id')->nullable()->change();
        });

        Schema::table('purchase_bill_items', function (Blueprint $table) {
            $table->foreignId('purchase_order_item_id')->nullable()->after('goods_receipt_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('goods_receipt_item_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_bill_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_item_id']);
            $table->dropColumn('purchase_order_item_id');
        });

        Schema::table('purchase_bills', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn('purchase_order_id');
        });
    }
};
