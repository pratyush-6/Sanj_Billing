<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('company_id')
            ->select('id', 'company_id')
            ->orderBy('id')
            ->each(function ($user) {
                DB::table('company_user')->insertOrIgnore([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        DB::table('company_user')->orderBy('id')->each(function ($row) {
            DB::table('users')->where('id', $row->user_id)->update(['company_id' => $row->company_id]);
        });
    }
};
