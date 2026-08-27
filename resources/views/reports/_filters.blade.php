@php($showFinancialYear = $showFinancialYear ?? true)

<form method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end text-sm">
    <div>
        <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">From</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
    </div>
    <div>
        <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">To</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
    </div>
    @if ($showFinancialYear)
        <div>
            <label class="block text-xs text-ink-500 dark:text-ink-400 mb-1">Financial Year</label>
            <select name="financial_year_id" class="w-full bg-white dark:bg-ink-800 text-ink-900 dark:text-ink-100 border-ink-300 dark:border-ink-600 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500">
                <option value="">All</option>
                @foreach ($financialYears as $fy)
                    <option value="{{ $fy->id }}" @selected(request('financial_year_id') == $fy->id)>{{ $fy->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="flex gap-2">
        <x-secondary-button type="submit" class="w-full justify-center">Filter</x-secondary-button>
        <a href="{{ url()->current() }}" class="inline-flex items-center px-3 py-2 text-ink-500 dark:text-ink-400 hover:text-ink-700 dark:hover:text-ink-200 text-sm">Reset</a>
    </div>
</form>
