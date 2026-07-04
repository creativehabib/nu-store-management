<div class="space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Activity Log & Audit Trail') }}</flux:heading>
        <flux:subheading>{{ __('Review logins, requisition status changes, and inventory stock changes.') }}</flux:subheading>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :label="__('Search')" :placeholder="__('User, PF, event, or description...')" />
        <flux:select wire:model.live="eventFilter" :label="__('Event')">
            <flux:select.option value="">{{ __('All Events') }}</flux:select.option>
            @foreach($events as $event)
                <flux:select.option value="{{ $event }}">{{ $event }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                <tr>
                    <th class="p-3 font-semibold">{{ __('Time') }}</th>
                    <th class="p-3 font-semibold">{{ __('User') }}</th>
                    <th class="p-3 font-semibold">{{ __('Event') }}</th>
                    <th class="p-3 font-semibold">{{ __('Description') }}</th>
                    <th class="p-3 font-semibold">{{ __('IP') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($auditLogs as $log)
                    <tr>
                        <td class="p-3 whitespace-nowrap text-zinc-600 dark:text-zinc-300">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                        <td class="p-3">
                            <div class="font-medium">{{ $log->user->name ?? __('System') }}</div>
                            <div class="text-xs text-zinc-500">{{ $log->user->pf_no ?? $log->user->email ?? 'N/A' }}</div>
                        </td>
                        <td class="p-3"><flux:badge color="blue">{{ $log->event }}</flux:badge></td>
                        <td class="p-3 text-zinc-700 dark:text-zinc-200">{{ $log->description }}</td>
                        <td class="p-3 text-zinc-500">{{ $log->ip_address ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-zinc-500">{{ __('No audit logs found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $auditLogs->links() }}
</div>
