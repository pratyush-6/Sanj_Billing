<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('Other');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('documentable_type');
            $table->unsignedBigInteger('documentable_id');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['documentable_type', 'documentable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
