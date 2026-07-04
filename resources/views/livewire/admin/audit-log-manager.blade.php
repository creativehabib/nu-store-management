<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <div>
        <flux:heading size="xl">{{ __('Activity Log & Audit Trail') }}</flux:heading>
        <flux:subheading>{{ __('Review logins, requisition status changes, and inventory stock changes.') }}</flux:subheading>
    </div>

    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-zinc-200 dark:border-zinc-700 p-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-5 xl:flex-1">
                @if($canDeleteAuditLogs)
                    <flux:select wire:model="bulkAction" :label="__('Bulk Actions')">
                        <flux:select.option value="">{{ __('Bulk Actions') }}</flux:select.option>
                        <flux:select.option value="delete_selected">{{ __('Delete selected records') }}</flux:select.option>
                    </flux:select>
                @endif

                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :label="__('Search')" :placeholder="__('Search...')" />

                <flux:select wire:model.live="eventFilter" :label="__('Event')">
                    <flux:select.option value="">{{ __('All Events') }}</flux:select.option>
                    @foreach($events as $event)
                        <flux:select.option value="{{ $event }}">{{ $event }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input type="date" wire:model.live="startDate" :label="__('Start Date')" />
                <flux:input type="date" wire:model.live="endDate" :label="__('End Date')" />
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($canDeleteAuditLogs)
                    <flux:button variant="outline" icon="check" wire:click="applyBulkAction" wire:confirm="{{ __('Are you sure you want to apply this bulk action?') }}">
                        {{ __('Apply') }}
                    </flux:button>

                    <flux:button variant="danger" icon="trash" wire:click="deleteAllRecords" wire:confirm="{{ __('Are you sure you want to delete all audit logs? This cannot be undone.') }}">
                        {{ __('Delete all records') }}
                    </flux:button>
                @else
                    <flux:badge color="amber" icon="shield-exclamation">{{ __('Only super admin can delete logs') }}</flux:badge>
                @endif

                <flux:button variant="outline" icon="x-mark" wire:click="clearFilters">
                    {{ __('Clear filters') }}
                </flux:button>

                <flux:button variant="outline" icon="arrow-path" wire:click="reloadLogs">
                    {{ __('Reload') }}
                </flux:button>
            </div>
        </div>

        @if($canDeleteAuditLogs && count($selectedAuditLogs) > 0)
            <div class="border-b border-blue-100 bg-blue-50 px-4 py-2 text-sm text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-300">
                {{ trans_choice(':count audit log selected|:count audit logs selected', count($selectedAuditLogs), ['count' => count($selectedAuditLogs)]) }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                    <tr>
                        @if($canDeleteAuditLogs)
                            <th class="w-12 p-3 font-semibold">
                                <input type="checkbox" wire:model.live="selectPage" class="rounded border-zinc-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            </th>
                        @endif
                        <th class="w-20 p-3 font-semibold">{{ __('ID') }}</th>
                        <th class="p-3 font-semibold">{{ __('Action') }}</th>
                        @if($canDeleteAuditLogs)
                            <th class="w-36 p-3 text-right font-semibold">{{ __('Operations') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($auditLogs as $log)
                        <tr wire:key="audit-log-{{ $log->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                            @if($canDeleteAuditLogs)
                                <td class="p-3 align-middle">
                                    <input type="checkbox" wire:model.live="selectedAuditLogs" value="{{ $log->id }}" class="rounded border-zinc-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                </td>
                            @endif
                            <td class="p-3 align-middle font-medium text-slate-600 dark:text-slate-300">{{ $log->id }}</td>
                            <td class="p-3 align-middle">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                        <flux:icon.user class="size-5" />
                                    </div>

                                    <div class="min-w-0 space-y-1">
                                        <p class="text-zinc-800 dark:text-zinc-100">
                                            <span class="font-semibold">{{ $log->user->name ?? __('System') }}</span>
                                            @if($log->user?->role)
                                                <span class="rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-bold uppercase text-white">{{ $log->user->role }}</span>
                                            @endif
                                            <span>{{ $log->description }}</span>
                                        </p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $log->created_at->diffForHumans() }}
                                            @if($log->ip_address)
                                                <span class="text-blue-600 dark:text-blue-300">({{ $log->ip_address }})</span>
                                            @endif
                                            <span class="ml-2 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $log->event }}</span>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            @if($canDeleteAuditLogs)
                                <td class="p-3 align-middle text-right">
                                    <flux:button size="sm" variant="danger" icon="trash" wire:click="deleteRecord({{ $log->id }})" wire:confirm="{{ __('Are you sure you want to delete this audit log?') }}" :title="__('Delete')" />
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canDeleteAuditLogs ? 4 : 2 }}" class="p-8 text-center text-zinc-500">{{ __('No audit logs found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-zinc-200 px-4 py-3 text-sm text-slate-500 dark:border-zinc-700 md:flex-row md:items-center md:justify-between">
            <div>
                {{ __('Showing :from to :to of :total matching records', [
                    'from' => $auditLogs->firstItem() ?? 0,
                    'to' => $auditLogs->lastItem() ?? 0,
                    'total' => $auditLogs->total(),
                ]) }}
                <span class="ml-2 text-xs">{{ __('Total stored in backend: :count', ['count' => $totalAuditLogs]) }}</span>
            </div>

            {{ $auditLogs->links() }}
        </div>
    </div>
</div>
