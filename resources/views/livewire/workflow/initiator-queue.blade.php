<div class="max-w-6xl mx-auto space-y-6">
    <flux:heading size="xl" class="border-b pb-2">{{ __('Initiator Queue') }}</flux:heading>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700">
                    <th class="p-3 text-sm font-semibold">{{ __('Requisition No') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Applicant') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Department') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Date') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Status') }}</th>
                    <th class="p-3 text-sm font-semibold text-right">{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($requisitions as $req)
                    <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                        <td class="p-3 font-medium">{{ $req->requisition_no }}</td>
                        <td class="p-3">{{ $req->user->name }} <br><span class="text-xs text-zinc-500">{{ $req->user->pf_no }}</span></td>
                        <td class="p-3">{{ $req->user->department }}</td>
                        <td class="p-3">{{ $req->created_at->format('d M, Y') }}</td>

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
                        <td colspan="6" class="p-10 text-center text-zinc-500">
                            <p class="text-lg font-medium">{{ __('There are no requisitions in your queue.') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>

    <flux:modal name="view-action-modal" class="md:w-3/4 lg:w-2/3">
        @if($selectedRequisition)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Requisition Details:') }} {{ $selectedRequisition->requisition_no }}</flux:heading>
                    <p class="text-sm text-zinc-500 mt-1">{{ __('Applicant:') }} {{ $selectedRequisition->user->name }} ({{ $selectedRequisition->user->department }})</p>
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
        @else
            <div class="py-12 flex flex-col items-center justify-center text-zinc-500">
                <svg class="animate-spin h-8 w-8 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="font-medium text-lg">{{ __('Loading file, please wait...') }}</p>
            </div>
        @endif
    </flux:modal>
</div>
