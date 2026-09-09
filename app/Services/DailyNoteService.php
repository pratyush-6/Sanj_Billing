<?php

namespace App\Services;

use App\Models\Company;
use App\Models\DailyNote;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DailyNoteService
{
    public function __construct(private AuditLogService $auditLog) {}

    public function create(array $data, Company $company, User $creator): DailyNote
    {
        return DB::transaction(function () use ($data, $company, $creator) {
            $note = DailyNote::create([
                ...$data,
                'company_id' => $company->id,
                'created_by' => $creator->id,
            ]);

            $this->auditLog->log('Daily Note Created', 'Daily Note', $note, null, $note->toArray());

            return $note;
        });
    }

    public function update(DailyNote $note, array $data): DailyNote
    {
        return DB::transaction(function () use ($note, $data) {
            $old = $note->toArray();
            $note->update($data);

            $this->auditLog->log('Daily Note Updated', 'Daily Note', $note, $old, $note->toArray());

            return $note;
        });
    }

    public function delete(DailyNote $note): void
    {
        DB::transaction(function () use ($note) {
            $old = $note->toArray();
            $note->delete();

            $this->auditLog->log('Daily Note Deleted', 'Daily Note', null, $old, null);
        });
    }

    /**
     * Note counts per day for the given month, keyed by day-of-month (1-31) —
     * used to render the calendar's note indicator dots without needing every
     * note's full content up front.
     */
    public function countsForMonth(int $companyId, Carbon $monthStart): array
    {
        $monthEnd = $monthStart->copy()->endOfMonth();

        return DailyNote::where('company_id', $companyId)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->selectRaw('DAY(date) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->all();
    }

    public function forDate(int $companyId, string $date): Collection
    {
        return DailyNote::where('company_id', $companyId)
            ->whereDate('date', $date)
            ->with('creator')
            ->orderBy('time')
            ->orderBy('id')
            ->get();
    }
}
