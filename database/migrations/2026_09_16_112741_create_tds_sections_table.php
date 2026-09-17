<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tds_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('section', 20);
            $table->string('description')->nullable();
            $table->decimal('rate', 5, 2);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tds_sections');
    }
};
