<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Maps existing free-text "state" values onto the canonical India GST
     * state/UT list (config/india.php), case/whitespace-insensitive. Any
     * value that doesn't match anything is left untouched and reported here
     * for manual review, rather than guessed at.
     */
    public function up(): void
    {
        $canonicalByLower = collect(config('india.states'))
            ->mapWithKeys(fn (string $name) => [strtolower(trim($name)) => $name]);

        foreach (['companies', 'parties'] as $table) {
            $values = DB::table($table)->whereNotNull('state')->distinct()->pluck('state');

            foreach ($values as $value) {
                $key = strtolower(trim($value));
                $canonical = $canonicalByLower->get($key);

                if ($canonical === null) {
                    if (trim($value) !== '') {
                        echo "  [state-normalize] {$table}.state '{$value}' does not match any known state — left as-is, review manually.\n";
                    }

                    continue;
                }

                if ($canonical !== $value) {
                    DB::table($table)->where('state', $value)->update(['state' => $canonical]);
                }
            }
        }
    }

    public function down(): void
    {
        // Normalization is not reversible — the original free-text values aren't recoverable.
    }
};
