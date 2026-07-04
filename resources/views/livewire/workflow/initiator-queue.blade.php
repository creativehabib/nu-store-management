<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <div class="flex flex-col gap-2 border-b pb-2 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Initiator Queue') }}</flux:heading>
            <flux:subheading>{{ __('Review requisitions, stock demand, and distribution-ready items from one table.') }}</flux:subheading>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:badge color="amber">{{ __('Pending') }}: {{ $requisitions->where('status', 'pending')->count() }}</flux:badge>
            <flux:badge color="red">{{ __('Returned') }}: {{ $requisitions->where('status', 'returned')->count() }}</flux:badge>
            <flux:badge color="green">{{ __('Ready') }}: {{ $requisitions->where('status', 'director_approved')->count() }}</flux:badge>
            <flux:badge color="indigo">{{ __('Distributed') }}: {{ $requisitions->where('status', 'distributed')->count() }}</flux:badge>
        </div>
    </div>

    <flux:card>
        <div class="mb-6 grid grid-cols-1 gap-3 lg:grid-cols-[1fr_220px_auto] lg:items-end">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                :label="__('Search')"
                placeholder="{{ __('Requisition no, applicant, PF, department, or product...') }}"
            />

            <flux:select wire:model.live="statusFilter" :label="__('Status')">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="returned">{{ __('Returned') }}</flux:select.option>
                <flux:select.option value="director_approved">{{ __('Approved (Ready)') }}</flux:select.option>
                <flux:select.option value="distributed">{{ __('Distributed') }}</flux:select.option>
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
                        <th class="p-3 font-semibold">#</th>
                        <th class="p-3 font-semibold">{{ __('Requisition') }}</th>
                        <th class="p-3 font-semibold">{{ __('Applicant & Department') }}</th>
                        <th class="p-3 font-semibold">{{ __('Items Summary') }}</th>
                        <th class="p-3 font-semibold text-center">{{ __('Demand') }}</th>
                        <th class="p-3 font-semibold text-center">{{ __('Age') }}</th>
                        <th class="p-3 font-semibold">{{ __('Status') }}</th>
                        <th class="p-3 font-semibold text-right">{{ __('Action') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($requisitions as $index => $req)
                        @php
                            $itemNames = $req->items
                                ->map(fn ($item) => $item->product?->name_bn ?? $item->product?->name_en ?? __('Unknown Item'))
                                ->take(2);
                            $remainingItems = max($req->items->count() - $itemNames->count(), 0);
                            $totalDemanded = $req->items->sum('demanded_qty');
                        @endphp

                        <tr class="align-top transition hover:bg-zinc-50 dark:hover:bg-zinc-800/70" wire:key="initiator-queue-row-{{ $req->id }}">
                            <td class="p-3 text-zinc-500">{{ $index + 1 }}</td>

                            <td class="p-3">
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $req->requisition_no }}</div>
                                <div class="mt-1 text-xs text-zinc-500">{{ $req->created_at->format('d M, Y h:i A') }}</div>
                            </td>

                            <td class="p-3">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $req->user->name }}</div>
                                <div class="mt-1 text-xs text-zinc-500">{{ __('PF:') }} {{ $req->user->pf_no }}</div>
                                <div class="mt-1 text-xs text-zinc-500">{{ $req->user->department->name ?? 'N/A' }}</div>
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

                            <td class="p-3 text-center">
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $req->created_at->diffForHumans(null, true) }}</div>
                                <div class="text-xs text-zinc-500">{{ __('ago') }}</div>
                            </td>

                            <td class="p-3">
                                @if($req->status === 'returned')
                                    <flux:badge color="red">{{ __('Returned') }}</flux:badge>
                                @elseif($req->status === 'director_approved')
                                    <flux:badge color="green">{{ __('Approved (Ready)') }}</flux:badge>
                                @elseif($req->status === 'distributed')
                                    <flux:badge color="indigo">{{ __('Distributed') }}</flux:badge>
                                @else
                                    <flux:badge color="amber">{{ __('Pending') }}</flux:badge>
                                @endif
                            </td>

                            <td class="p-3 text-right">
                                @if($req->status === 'pending' || $req->status === 'returned')
                                    <flux:button size="sm" variant="primary" icon="eye" wire:click="viewRequisition({{ $req->id }})">
                                        {{ __('View & Action') }}
                                    </flux:button>
                                @elseif($req->status === 'director_approved' || $req->status === 'distributed')
                                    <flux:button size="sm" variant="outline" icon="printer" href="{{ route('workflow.print', $req->id) }}" wire:navigate>
                                        {{ __('Print & Distribute') }}
                                    </flux:button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-zinc-500">
                                <p class="text-lg font-medium">{{ __('There are no requisitions in your queue.') }}</p>
                                <p class="mt-1 text-sm">{{ __('Try clearing the search or status filter.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </flux:card>

    <flux:modal name="view-action-modal" class="md:w-3/4 lg:w-2/3">
        @if($selectedRequisition)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Requisition Details:') }} {{ $selectedRequisition->requisition_no }}</flux:heading>
                    <p class="text-sm text-zinc-500 mt-1">{{ __('Applicant:') }} {{ $selectedRequisition->user->name }} ({{ $selectedRequisition->user->department->name ?? 'N/A' }})</p>
                </div>

                <flux:separator />

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-zinc-200 dark:border-zinc-700">
                        <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-800 border-b dark:border-zinc-700">
                            <th class="p-2 text-sm border-r dark:border-zinc-700">{{ __('Item Name') }}</th>
                            <th class="p-2 text-sm border-r dark:border-zinc-700 text-center">{{ __('Current Stock') }}</th>
                            <th class="p-2 text-sm border-r dark:border-zinc-700 text-center">{{ __('Demanded Quantity') }}</th>
                            <th class="p-2 text-sm text-center">{{ __('Determine Supply Quantity') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($selectedRequisition->items as $item)
                            <tr class="border-b dark:border-zinc-700">
                                <td class="p-2 border-r dark:border-zinc-700">{{ $item->product->name_bn }} <br><span class="text-xs text-zinc-500">{{ $item->purpose }}</span></td>
                                <td class="p-2 border-r dark:border-zinc-700 text-center font-bold text-blue-600">{{ $item->product->stock }}</td>
                                <td class="p-2 border-r dark:border-zinc-700 text-center">{{ $item->demanded_qty }}</td>
                                <td class="p-2 text-center w-32">
                                    <flux:input type="number" wire:model="suppliedQuantities.{{ $item->id }}" min="0" max="{{ $item->product->stock }}" />
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div>
                    <flux:textarea wire:model="comment" :label="__('Note / Comment (Optional)')" :placeholder="__('e.g., Sufficient in stock...')" rows="2" />
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="primary" icon="check-circle" wire:click="forwardRequisition" wire:confirm="{{ __('Are you sure you want to forward this to the next step?') }}">
                        {{ __('Forward to AD') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
