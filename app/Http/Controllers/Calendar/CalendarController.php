<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Concerns\EnsuresCompanyOwnership;
use App\Http\Controllers\Controller;
use App\Http\Requests\DailyNoteRequest;
use App\Models\DailyNote;
use App\Services\DailyNoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    use EnsuresCompanyOwnership;

    public function __construct(private DailyNoteService $notes) {}

    public function index(Request $request)
    {
        $company = current_company_or_fail();

        $month = $this->resolveMonth($request->string('month')->toString());
        $selectedDate = $this->resolveDate($request->string('date')->toString());

        return view('calendar.index', [
            'monthPayload' => $this->monthPayload($company->id, $month, $selectedDate),
            'notesPayload' => $this->notesPayload($company->id, $selectedDate),
            'categories' => config('calendar.note_categories'),
        ]);
    }

    public function month(Request $request): JsonResponse
    {
        $company = current_company_or_fail();
        $month = $this->resolveMonth($request->string('month')->toString());
        $selectedDate = $this->resolveDate($request->string('date')->toString());

        return response()->json($this->monthPayload($company->id, $month, $selectedDate));
    }

    public function notesForDate(Request $request): JsonResponse
    {
        $company = current_company_or_fail();
        $date = $this->resolveDate($request->string('date')->toString());

        return response()->json($this->notesPayload($company->id, $date));
    }

    public function store(DailyNoteRequest $request): JsonResponse
    {
        $company = current_company_or_fail();
        $note = $this->notes->create($request->validated(), $company, Auth::user());

        return response()->json(['note' => $this->notePayload($note)], 201);
    }

    public function update(DailyNoteRequest $request, DailyNote $dailyNote): JsonResponse
    {
        $this->ensureBelongsToCurrentCompany($dailyNote);

        $note = $this->notes->update($dailyNote, $request->validated());

        return response()->json(['note' => $this->notePayload($note)]);
    }

    public function destroy(DailyNote $dailyNote): JsonResponse
    {
        $this->ensureBelongsToCurrentCompany($dailyNote);

        $this->notes->delete($dailyNote);

        return response()->json(['status' => 'deleted']);
    }

    private function resolveMonth(?string $value): Carbon
    {
        try {
            return $value ? Carbon::createFromFormat('Y-m', $value)->startOfMonth() : now()->startOfMonth();
        } catch (\Exception) {
            return now()->startOfMonth();
        }
    }

    private function resolveDate(?string $value): Carbon
    {
        try {
            return $value ? Carbon::parse($value)->startOfDay() : now()->startOfDay();
        } catch (\Exception) {
            return now()->startOfDay();
        }
    }

    private function monthPayload(int $companyId, Carbon $month, Carbon $selectedDate): array
    {
        $counts = $this->notes->countsForMonth($companyId, $month);

        $gridStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $days = collect();
        $cursor = $gridStart->copy();

        while ($cursor->lte($gridEnd)) {
            $inMonth = $cursor->month === $month->month;

            $days->push([
                'date' => $cursor->toDateString(),
                'day' => $cursor->day,
                'inMonth' => $inMonth,
                'isToday' => $cursor->isToday(),
                'isSelected' => $cursor->isSameDay($selectedDate),
                'noteCount' => $inMonth ? ($counts[$cursor->day] ?? 0) : 0,
            ]);
            $cursor->addDay();
        }

        return [
            'month' => $month->format('Y-m'),
            'label' => $month->format('F Y'),
            'weeks' => $days->chunk(7)->values()->map(fn ($week) => $week->values())->all(),
        ];
    }

    private function notesPayload(int $companyId, Carbon $date): array
    {
        return [
            'date' => $date->toDateString(),
            'label' => $date->format('F j, Y'),
            'notes' => $this->notes->forDate($companyId, $date->toDateString())
                ->map(fn ($note) => $this->notePayload($note))
                ->values()
                ->all(),
        ];
    }

    private function notePayload(DailyNote $note): array
    {
        return [
            'id' => $note->id,
            'date' => $note->date->toDateString(),
            'title' => $note->title,
            'content' => $note->content,
            'time' => $note->time ? Carbon::parse($note->time)->format('H:i') : null,
            'time_label' => $note->time ? Carbon::parse($note->time)->format('g:i A') : null,
            'category' => $note->category,
            'created_by' => $note->creator?->name,
        ];
    }
}
