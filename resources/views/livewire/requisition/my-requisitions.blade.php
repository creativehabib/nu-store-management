<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <flux:heading size="xl" class="border-b pb-2">{{ __('My Requisitions (Tracking & History)') }}</flux:heading>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700">
                    <th class="p-3 text-sm font-semibold">{{ __('Requisition No') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Application Date') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Number of Items') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Current Status') }}</th>
                    <th class="p-3 text-sm font-semibold text-right">{{ __('Tracking') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($requisitions as $req)
                    <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                        <td class="p-3 font-medium">{{ $req->requisition_no }}</td>
                        <td class="p-3">{{ $req->created_at->format('d M, Y (h:i A)') }}</td>
                        <td class="p-3">{{ $req->items()->count() }} {{ __('items') }}</td>
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
                        <td class="p-3 text-right">
                            <flux:button size="sm" variant="outline" icon="clock" wire:click="viewHistory({{ $req->id }})">
                                {{ __('View History') }}
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-zinc-500">
                            <p class="text-lg font-medium">{{ __('You have not submitted any requisition yet.') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $requisitions->links() }}
        </div>
    </flux:card>

    <flux:modal name="history-modal" class="md:w-3/4 lg:w-2/3">
        @if($selectedRequisition)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Tracking Details:') }} {{ $selectedRequisition->requisition_no }}</flux:heading>
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
