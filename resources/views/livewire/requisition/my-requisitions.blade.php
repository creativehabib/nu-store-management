<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <div class="flex flex-col gap-2 border-b pb-2 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">{{ __('My Requisitions (Tracking & History)') }}</flux:heading>
            <flux:subheading>{{ __('Track every submitted demand, item summary, and approval timeline from one place.') }}</flux:subheading>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <flux:card class="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20">
            <flux:text size="sm" class="text-blue-600 dark:text-blue-400">{{ __('Total Submitted') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $totalRequisitions }}</flux:heading>
        </flux:card>
        <flux:card class="border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20">
            <flux:text size="sm" class="text-amber-600 dark:text-amber-400">{{ __('In Progress') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $inProgressRequisitions }}</flux:heading>
        </flux:card>
        <flux:card class="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20">
            <flux:text size="sm" class="text-green-600 dark:text-green-400">{{ __('Completed') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $completedRequisitions }}</flux:heading>
        </flux:card>
        <flux:card class="border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20">
            <flux:text size="sm" class="text-red-600 dark:text-red-400">{{ __('Returned') }}</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $returnedRequisitions }}</flux:heading>
        </flux:card>
    </div>

    <flux:card>
        <div class="mb-6 grid grid-cols-1 gap-3 lg:grid-cols-[1fr_240px_auto] lg:items-end">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                :label="__('Search')"
                placeholder="{{ __('Search requisition no or product name...') }}"
            />

            <flux:select wire:model.live="statusFilter" :label="__('Status')">
                <flux:select.option value="">{{ __('All Status') }}</flux:select.option>
                <flux:select.option value="department_director_review">{{ __('Department Director Review') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending with Initiator') }}</flux:select.option>
                <flux:select.option value="initiator_checked">{{ __('Checked by Initiator') }}</flux:select.option>
                <flux:select.option value="ad_approved">{{ __('AD Approved') }}</flux:select.option>
                <flux:select.option value="dd_approved">{{ __('DD Approved') }}</flux:select.option>
                <flux:select.option value="director_approved">{{ __('Ready for Distribution') }}</flux:select.option>
                <flux:select.option value="distributed">{{ __('Distributed') }}</flux:select.option>
                <flux:select.option value="returned">{{ __('Returned') }}</flux:select.option>
            </flux:select>

            <flux:button type="button" variant="outline" icon="x-mark" wire:click="clearFilters">
                {{ __('Clear') }}
            </flux:button>
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left text-sm">
                    <thead class="bg-zinc-100 text-xs uppercase tracking-wide text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                    <tr>
                        <th class="p-3 font-semibold">{{ __('Requisition') }}</th>
                        <th class="p-3 font-semibold">{{ __('Items Summary') }}</th>
                        <th class="p-3 font-semibold text-center">{{ __('Demand') }}</th>
                        <th class="p-3 font-semibold">{{ __('Current Status') }}</th>
                        <th class="p-3 font-semibold">{{ __('Last Updated') }}</th>
                        <th class="p-3 font-semibold text-right">{{ __('Tracking') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($requisitions as $req)
                        @php
                            $itemNames = $req->items
                                ->map(fn ($item) => $item->product?->name_bn ?? $item->product?->name_en ?? __('Unknown Item'))
                                ->take(2);
                            $remainingItems = max($req->items->count() - $itemNames->count(), 0);
                            $totalDemanded = $req->items->sum('demanded_qty');
                        @endphp

                        <tr class="align-top transition hover:bg-zinc-50 dark:hover:bg-zinc-800/70" wire:key="my-requisition-{{ $req->id }}">
                            <td class="p-3">
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $req->requisition_no }}</div>
                                <div class="mt-1 text-xs text-zinc-500">{{ $req->created_at->format('d M, Y h:i A') }}</div>
                            </td>

                            <td class="p-3">
                                @if($req->items->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($itemNames as $itemName)
                                            <flux:badge color="zinc">{{ $itemName }}</flux:badge>
                                        @endforeach

                                        @if($remainingItems > 0)
                                            <flux:badge color="blue">+{{ $remainingItems }} {{ __('more') }}</flux:badge>
                                        @endif
                                    </div>
                                    <div class="mt-2 text-xs text-zinc-500">{{ $req->items->count() }} {{ __('item(s)') }}</div>
                                @else
                                    <span class="text-xs text-zinc-500">{{ __('No items attached') }}</span>
                                @endif
                            </td>

                            <td class="p-3 text-center">
                                <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $totalDemanded }}</div>
                                <div class="text-xs text-zinc-500">{{ __('pcs') }}</div>
                            </td>

                            <td class="p-3">
                                @if($req->status === 'department_director_review')
                                    <flux:badge color="purple">{{ __('Pending (with Department Director)') }}</flux:badge>
                                @elseif($req->status === 'pending')
                                    <flux:badge color="amber">{{ __('Pending (with Initiator)') }}</flux:badge>
                                @elseif($req->status === 'initiator_checked')
                                    <flux:badge color="blue">{{ __('Checked (with AD)') }}</flux:badge>
                                @elseif($req->status === 'ad_approved')
                                    <flux:badge color="indigo">{{ __('AD Approved (with DD)') }}</flux:badge>
                                @elseif($req->status === 'dd_approved')
                                    <flux:badge color="purple">{{ __('DD Approved (with Director)') }}</flux:badge>
                                @elseif($req->status === 'director_approved')
                                    <flux:badge color="green">{{ __('Director Approved (Ready)') }}</flux:badge>
                                @elseif($req->status === 'distributed')
                                    <flux:badge color="zinc">{{ __('Distributed (Completed)') }}</flux:badge>
                                @elseif($req->status === 'returned')
                                    <flux:badge color="red">{{ __('Returned') }}</flux:badge>
                                @endif
                            </td>

                            <td class="p-3">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $req->updated_at->diffForHumans() }}</div>
                                <div class="mt-1 text-xs text-zinc-500">{{ $req->updated_at->format('d M, Y h:i A') }}</div>
                            </td>

                            <td class="p-3 text-right">
                                <flux:button size="sm" variant="outline" icon="clock" wire:click="viewHistory({{ $req->id }})">
                                    {{ __('View History') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-zinc-500">
                                <p class="text-lg font-medium">{{ __('You have not submitted any requisition yet.') }}</p>
                                <p class="mt-1 text-sm">{{ __('Try clearing the search or status filter if you expected results.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $requisitions->links() }}
        </div>
    </flux:card>

    <flux:modal name="history-modal" class="md:w-3/4 lg:w-2/3">
        @if($selectedRequisition)
            <div class="space-y-6">
                <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Tracking Details:') }} {{ $selectedRequisition->requisition_no }}</flux:heading>
                        <flux:subheading>{{ __('Submitted on') }} {{ $selectedRequisition->created_at->format('d M, Y h:i A') }}</flux:subheading>
                    </div>
                    <flux:badge color="{{ $selectedRequisition->status === 'returned' ? 'red' : ($selectedRequisition->status === 'distributed' ? 'green' : 'blue') }}">
                        {{ ucwords(str_replace('_', ' ', $selectedRequisition->status)) }}
                    </flux:badge>
                </div>

                <flux:separator />

                <div>
                    <h3 class="font-semibold mb-2">{{ __('Item Details & Approval:') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse border border-zinc-200 dark:border-zinc-700 text-xs">
                            <thead>
                            <tr class="bg-zinc-50 dark:bg-zinc-800 border-b dark:border-zinc-700">
                                <th class="p-2 border-r dark:border-zinc-700">{{ __('Item Name') }}</th>
                                <th class="p-2 border-r dark:border-zinc-700 text-center">{{ __('You Requested') }}</th>
                                <th class="p-2 text-center text-green-600">{{ __('Approved Quantity') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($selectedRequisition->items as $item)
                                <tr class="border-b dark:border-zinc-700">
                                    <td class="p-2 border-r dark:border-zinc-700">{{ $item->product->name_bn }}</td>
                                    <td class="p-2 border-r dark:border-zinc-700 text-center">{{ $item->demanded_qty }}</td>
                                    <td class="p-2 text-center font-bold text-green-600">{{ $item->supplied_qty }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold mb-4">{{ __('Approval History (Timeline):') }}</h3>

                    @if(empty($selectedRequisition->approval_history))
                        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 text-amber-600 rounded-lg text-sm border border-amber-200 dark:border-amber-800">
                            {{ __('No officer has viewed this yet. It is waiting in the store queue.') }}
                        </div>
                    @else
                        <div class="space-y-4 border-l-2 border-zinc-200 dark:border-zinc-700 ml-3 pl-4">
                            @foreach($selectedRequisition->approval_history as $history)
                                <div class="relative">
                                    <div class="absolute -left-6 mt-1.5 h-3 w-3 rounded-full {{ $history['action'] === 'returned' ? 'bg-red-500' : 'bg-green-500' }}"></div>

                                    <div class="bg-zinc-50 dark:bg-zinc-800 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-semibold text-sm">{{ $history['name'] }} <span class="text-xs font-normal text-zinc-500">({{ ucwords(str_replace('_', ' ', $history['role'])) }})</span></p>
                                                <p class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($history['date'])->format('d M, Y - h:i A') }}</p>
                                            </div>
                                            <div>
                                                @if($history['action'] === 'returned')
                                                    <flux:badge color="red" size="sm">{{ __('Returned') }}</flux:badge>
                                                @else
                                                    <flux:badge color="green" size="sm">{{ __('Approved / Forwarded') }}</flux:badge>
                                                @endif
                                            </div>
                                        </div>

                                        @if(!empty($history['comment']))
                                            <div class="mt-2 text-sm bg-white dark:bg-zinc-900 p-2 rounded border border-zinc-100 dark:border-zinc-700">
                                                <strong>{{ __('Comment:') }}</strong> {{ $history['comment'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex justify-end mt-4">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Close') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @else
            <div class="py-12 flex flex-col items-center justify-center text-zinc-500">
                <svg class="animate-spin h-8 w-8 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="font-medium text-lg">{{ __('Loading history, please wait...') }}</p>
            </div>
        @endif
    </flux:modal>
</div>
