<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->where('name', 'vendors.manage')->update(['name' => 'parties.manage']);
    }

    public function down(): void
    {
        DB::table('permissions')->where('name', 'parties.manage')->update(['name' => 'vendors.manage']);
    }
};
