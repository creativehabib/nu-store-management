<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <flux:heading size="xl" class="border-b pb-2">{{ __('Purpose Management') }}</flux:heading>

    <flux:card>
        <form wire:submit="save" class="flex flex-col md:flex-row items-end gap-4">
            <div class="flex-1 w-full">
                <flux:input wire:model="name" :label="__('Purpose Name')" :placeholder="__('e.g. Official Use')" required />
            </div>

            <div class="w-full md:w-auto pb-2">
                <flux:checkbox wire:model="is_active" :label="__('Active')" />
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                @if($isEditMode)
                    <flux:button type="button" variant="outline" wire:click="resetFields">{{ __('Cancel') }}</flux:button>
                @endif
                <flux:button type="submit" variant="primary" class="w-full md:w-auto">
                    {{ $isEditMode ? __('Update') : __('Save') }}
                </flux:button>
            </div>
        </form>
    </flux:card>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700">
                    <th class="p-3 text-sm font-semibold">#</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Purpose Name') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Status') }}</th>
                    <th class="p-3 text-sm font-semibold text-right">{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($purposes as $index => $purpose)
                    <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition" wire:key="purpose-{{ $purpose->id }}">
                        <td class="p-3">{{ $purposes->firstItem() + $index }}</td>
                        <td class="p-3 font-medium">{{ $purpose->name }}</td>
                        <td class="p-3">
                            <flux:badge :color="$purpose->is_active ? 'green' : 'zinc'">
                                {{ $purpose->is_active ? __('Active') : __('Inactive') }}
                            </flux:badge>
                        </td>
                        <td class="p-3 text-right flex justify-end gap-2">
                            <flux:button size="sm" variant="outline" icon="pencil" wire:click="edit({{ $purpose->id }})" />
                            <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $purpose->id }})" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-zinc-500">
                            {{ __('No purposes found.') }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $purposes->links() }}
        </div>
    </flux:card>

    <x-delete-modal name="delete-purpose-modal" action="executeDelete"/>
</div>
