<div class="max-w-4xl mx-auto space-y-6">
    <flux:heading size="xl" class="border-b pb-2">{{ __('Product Category Management') }}</flux:heading>

    <flux:card>
        <form wire:submit="save" class="flex flex-col md:flex-row items-end gap-4">
            <div class="flex-1 w-full">
                <flux:input wire:model="name" :label="__('Category Name')" :placeholder="__('e.g. Stationery & Office Supplies')" required />
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <flux:button type="submit" variant="primary" class="w-full md:w-auto">
                    {{ $isEditMode ? __('Update') : __('Save') }}
                </flux:button>

                @if($isEditMode)
                    <flux:button type="button" variant="outline" wire:click="resetFields">
                        {{ __('Cancel') }}
                    </flux:button>
                @endif
            </div>
        </form>
    </flux:card>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700">
                    <th class="p-3 text-sm font-semibold">#</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Category Name') }}</th>
                    <th class="p-3 text-sm font-semibold text-right">{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($categories as $index => $category)
                    <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                        <td class="p-3">{{ $categories->firstItem() + $index }}</td>
                        <td class="p-3 font-medium">{{ $category->name }}</td>
                        <td class="p-3 text-right flex justify-end gap-2">
                            <flux:button size="sm" variant="outline" icon="pencil" wire:click="edit({{ $category->id }})" />

                            <flux:button size="sm" variant="danger" icon="trash" wire:click="delete({{ $category->id }})" wire:confirm="{{ __('Are you sure you want to delete this?') }}" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-4 text-center text-zinc-500">
                            {{ __('No categories found.') }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    </flux:card>
</div>
