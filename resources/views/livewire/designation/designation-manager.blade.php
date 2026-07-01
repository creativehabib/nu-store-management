<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <flux:heading size="xl" class="border-b pb-2">{{ __('Designation Management') }}</flux:heading>

    <flux:card>
        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="md:col-span-2">
                <flux:input wire:model="title" :label="__('Designation Title')" :placeholder="__('e.g. Director, Section Officer')" required />
            </div>

            <div>
                <flux:input type="number" wire:model="rank" :label="__('Rank (Hierarchy)')" :placeholder="__('e.g. 1, 2, 3')" required />
            </div>

            <div class="flex gap-2">
                @if($isEditMode)
                    <flux:button type="button" variant="outline" wire:click="resetFields">{{ __('Cancel') }}</flux:button>
                @endif
                <flux:button type="submit" variant="primary" class="w-full">
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
                    <th class="p-3 font-semibold">{{ __('Title') }}</th>
                    <th class="p-3 font-semibold">{{ __('Rank Level') }}</th>
                    <th class="p-3 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($designations as $desig)
                    <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                        <td class="p-3 font-medium">{{ $desig->title }}</td>
                        <td class="p-3">
                            <flux:badge color="indigo" size="sm">{{ __('Rank: ') }} {{ $desig->rank }}</flux:badge>
                        </td>
                        <td class="p-3 text-right flex justify-end gap-2">
                            <flux:button size="sm" variant="outline" icon="pencil" wire:click="edit({{ $desig->id }})" />
                            <flux:button size="sm" variant="danger" icon="trash" wire:click="confirmDelete({{ $desig->id }})" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-4 text-center text-zinc-500">{{ __('No designations found.') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>

    <x-delete-modal name="delete-designation-modal" action="executeDelete"/>
</div>
