<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <flux:heading size="xl" class="border-b pb-2">{{ __('Approval Queue') }}</flux:heading>

    @php
        $user = auth()->user();
        $isGlobalAdmin = in_array($user->role, ['admin', 'super_admin']);

        $storeRoles = ['initiator', 'assistant_director', 'deputy_director', 'director'];
        $isCentralStoreOfficer = setting('store_mode', 'departmental') === 'centralized'
                              && $user->department_id == setting('central_store_dept_id', 1)
                              && in_array($user->role, $storeRoles);

        $canFilterAllDepartments = $isGlobalAdmin || $isCentralStoreOfficer;
    @endphp

    <flux:card class="bg-zinc-50 dark:bg-zinc-800/50">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :label="__('Search')" :placeholder="__('Requisition No, Name or PF No...')" />
            </div>

            <div>
                <flux:input type="date" wire:model.live="start_date" :label="__('Start Date')" />
            </div>

            <div>
                <flux:input type="date" wire:model.live="end_date" :label="__('End Date')" />
            </div>

            <div>
                @if($canFilterAllDepartments)
                    <flux:select wire:model.live="department_id" :placeholder="__('Select Department')" :label="__('Department')">
                        <flux:select.option value="">{{ __('All Departments') }}</flux:select.option>
                        @foreach($departments as $dept)
                            <flux:select.option value="{{ $dept->id }}">{{ $dept->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @else
                    <flux:input disabled :label="__('Department')" :value="$user->department->name ?? 'N/A'" />
                @endif
            </div>
        </div>
    </flux:card>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700">
                    <th class="p-3 text-sm font-semibold">{{ __('Requisition No') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Applicant') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Department') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Date') }}</th>
                    <th class="p-3 text-sm font-semibold text-right">{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($requisitions as $req)
                    <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                        <td class="p-3 font-medium">{{ $req->requisition_no }}</td>
                        <td class="p-3">{{ $req->user->name }} <br><span class="text-xs text-zinc-500">{{ $req->user->pf_no }}</span></td>
                        <td class="p-3">{{ $req->user->department->name ?? 'N/A' }}</td>
                        <td class="p-3">{{ $req->created_at->format('d M, Y') }}</td>
                        <td class="p-3 text-right">
                            <flux:button size="sm" variant="primary" icon="document-magnifying-glass" wire:click="viewRequisition({{ $req->id }})">
                                {{ __('View & Approve') }}
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-zinc-500">
                            <p class="text-lg font-medium">{{ __('No requisitions found.') }}</p>
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

    <flux:modal name="view-action-modal" class="md:w-3/4 lg:w-2/3">

        @if($selectedRequisition)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Requisition No:') }} {{ $selectedRequisition->requisition_no }}</flux:heading>
                    <p class="text-sm text-zinc-500 mt-1">{{ __('Applicant:') }} {{ $selectedRequisition->user->name }} ({{ $selectedRequisition->user->department->name ?? 'N/A' }})</p>
                </div>

                <flux:separator />

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-zinc-200 dark:border-zinc-700">
                        <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-800 border-b dark:border-zinc-700">
                            <th class="p-2 text-sm border-r dark:border-zinc-700">{{ __('Item Name') }}</th>
                            <th class="p-2 text-sm border-r dark:border-zinc-700 text-center">{{ __('Current Stock') }}</th>
                            <th class="p-2 text-sm border-r dark:border-zinc-700 text-center">{{ __('Demand') }}</th>
                            <th class="p-2 text-sm text-center">{{ __('Supply (Determine)') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($selectedRequisition->items as $item)
                            <tr class="border-b dark:border-zinc-700">
                                <td class="p-2 border-r dark:border-zinc-700">{{ $item->product->name_bn }}</td>
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
                    <flux:textarea wire:model="comment" :label="__('Note / Comment (Optional)')" :placeholder="__('e.g., Approved...')" rows="2" />
                </div>

                <div class="flex justify-between items-center mt-4">
                    <flux:button variant="danger" icon="arrow-uturn-left" wire:click="processAction('return')" wire:confirm="{{ __('Are you sure you want to send this back?') }}">
                        {{ __('Send Back') }}
                    </flux:button>

                    <div class="flex gap-2">
                        <flux:modal.close>
                            <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                        </flux:modal.close>

                        <flux:button variant="primary" icon="check-badge" wire:click="processAction('approve')" wire:confirm="{{ __('Are you sure you want to approve this?') }}">
                            {{ __('Approve & Forward') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        @else
            <div class="py-12 flex flex-col items-center justify-center text-zinc-500">
                <svg class="animate-spin h-8 w-8 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="font-medium text-lg">{{ __('Loading file, please wait...') }}</p>
            </div>
        @endif

    </flux:modal>
</div>
