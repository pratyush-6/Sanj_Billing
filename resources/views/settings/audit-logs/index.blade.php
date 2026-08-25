<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Audit Logs" description="Every recorded change, who made it, and when." />
    </x-slot>

    <div class="space-y-4">
        <x-ui.card>
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-xs font-medium text-ink-500 uppercase mb-1">Module</label>
                    <select name="module" class="border-ink-300 rounded-lg shadow-sm text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
                        @endforeach
                    </select>
                </div>
                <x-secondary-button type="submit">Filter</x-secondary-button>
            </form>
        </x-ui.card>

        <x-ui.card :padded="false">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-100">
                    <thead class="bg-ink-50/80">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Date/Time</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">Module</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-ink-500 uppercase tracking-wide">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-ink-50/60">
                                <td class="px-4 py-3.5 text-sm text-ink-600 whitespace-nowrap">{{ $log->created_at->format('d-M-Y H:i:s') }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600">{{ $log->user?->name ?? 'System' }}</td>
                                <td class="px-4 py-3.5 text-sm font-medium text-ink-900">{{ $log->action }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-600">{{ $log->module }}</td>
                                <td class="px-4 py-3.5 text-sm text-ink-400">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-ui.empty-state title="No audit log entries yet" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
