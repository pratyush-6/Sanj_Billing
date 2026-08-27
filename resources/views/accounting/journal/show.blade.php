<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="$entry->entry_number" :description="$entry->entry_date->format('d-M-Y').' · '.$entry->narration" />
    </x-slot>

    <div class="max-w-3xl space-y-4">
        <div class="flex items-center gap-3">
            <x-ui.badge :variant="$entry->status === 'Posted' ? 'success' : 'neutral'">{{ $entry->status }}</x-ui.badge>
            @if ($entry->reverses)
                <a href="{{ route('accounting.journal.show', $entry->reverses) }}" class="text-xs text-ink-500 dark:text-ink-400 hover:text-brand-600 dark:hover:text-brand-400">
                    Reverses {{ $entry->reverses->entry_number }}
                </a>
            @endif
            @if ($entry->reversedBy->isNotEmpty())
                <a href="{{ route('accounting.journal.show', $entry->reversedBy->first()) }}" class="text-xs text-ink-500 dark:text-ink-400 hover:text-brand-600 dark:hover:text-brand-400">
                    Reversed by {{ $entry->reversedBy->first()->entry_number }}
                </a>
            @endif
        </div>

        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100 dark:divide-ink-800">
                    <thead class="bg-ink-50/80 dark:bg-ink-800/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Account</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Debit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-ink-500 dark:text-ink-400 uppercase tracking-wide">Credit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @foreach ($entry->items as $item)
                            <tr>
                                <td class="px-4 py-3.5 text-sm text-ink-800 dark:text-ink-100">{{ $item->account->name }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-900 dark:text-ink-50">{{ $item->debit > 0 ? number_format($item->debit, 2) : '—' }}</td>
                                <td class="px-4 py-3.5 text-sm text-right text-ink-900 dark:text-ink-50">{{ $item->credit > 0 ? number_format($item->credit, 2) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-ink-50/60 dark:bg-ink-800/60 font-semibold">
                            <td class="px-4 py-3 text-sm text-ink-900 dark:text-ink-50">Total</td>
                            <td class="px-4 py-3 text-sm text-right text-ink-900 dark:text-ink-50">{{ number_format($entry->items->sum('debit'), 2) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-ink-900 dark:text-ink-50">{{ number_format($entry->items->sum('credit'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-ui.card>

        @if ($entry->source)
            <p class="text-xs text-ink-400 dark:text-ink-500">
                Source: {{ class_basename($entry->source_type) }}
                @if (method_exists($entry->source, 'getKey'))
                    #{{ $entry->source->getKey() }}
                @endif
            </p>
        @endif
    </div>
</x-app-layout>
