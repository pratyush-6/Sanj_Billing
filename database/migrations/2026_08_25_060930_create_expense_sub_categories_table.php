<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['expense_category_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_sub_categories');
    }
};
