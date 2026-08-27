<div class="flex items-center gap-2">
    @foreach (['xlsx' => 'Excel', 'csv' => 'CSV', 'pdf' => 'PDF'] as $format => $label)
        <a href="{{ route('reports.export', array_merge(['report' => $report, 'format' => $format], request()->query())) }}"
           class="inline-flex items-center px-3 py-1.5 rounded-lg border border-ink-200 dark:border-ink-700 text-xs font-medium text-ink-600 dark:text-ink-300 hover:bg-ink-50 dark:hover:bg-ink-800">
            {{ $label }}
        </a>
    @endforeach
</div>
