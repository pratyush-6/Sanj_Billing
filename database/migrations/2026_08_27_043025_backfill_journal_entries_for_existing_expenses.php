<?php

use App\Models\Expense;
use App\Services\AccountingService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $accounting = app(AccountingService::class);

        Expense::where('status', 'Approved')
            ->whereDoesntHave('journalEntries')
            ->orderBy('id')
            ->each(fn (Expense $expense) => $accounting->record($expense));
    }

    public function down(): void
    {
        // No-op: posted journal entries are additive and safe to leave in place.
    }
};
