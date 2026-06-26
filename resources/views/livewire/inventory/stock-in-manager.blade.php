<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <flux:heading size="xl" class="border-b pb-2">{{ __('Stock In / Purchase Entry') }}</flux:heading>

    <flux:card class="{{ $isEditMode ? 'border-indigo-500 shadow-md' : '' }}">
        @if($isEditMode)
            <div class="mb-4 text-indigo-600 font-bold flex justify-between items-center border-b pb-2">
                <span><flux:icon.pencil-square class="w-5 h-5 inline mr-1" /> {{ __('Updating Entry') }}</span>
            </div>
        @endif

        <form wire:submit="saveStock" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                <div>
                    <flux:select wire:model="product_id" :label="__('Product Name')" searchable :placeholder="__('Select Product')" required>
                        @foreach($products as $product)
                            <flux:select.option value="{{ $product->id }}">
                                {{ $product->name_bn }} ({{ __('Current Stock:') }} {{ $product->stock }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:input type="number" wire:model="quantity" :label="__('Stock In Quantity')" min="1" required :placeholder="__('e.g. 50')" />
                </div>
                <div>
                    <flux:input wire:model="voucher_no" :label="__('Voucher / Memo No. (Optional)')" :placeholder="__('e.g. V-402')" />
                </div>
                <div>
                    <flux:input wire:model="supplier" :label="__('Supplier / Source (Optional)')" :placeholder="__('e.g. Government Store')" />
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                @if($isEditMode)
                    <flux:button type="button" variant="outline" wire:click="resetFields">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">{{ __('Update') }}</flux:button>
                @else
                    <flux:button type="submit" variant="primary" icon="plus">{{ __('Add Stock (Stock In)') }}</flux:button>
                @endif
            </div>
        </form>
    </flux:card>

    <flux:card>
        <div class="mb-4">
            <flux:heading size="lg">{{ __('Recent Entries') }}</flux:heading>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700">
                    <th class="p-3 text-sm font-semibold">{{ __('Date & Time') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Product Name') }}</th>
                    <th class="p-3 text-sm font-semibold text-center">{{ __('Quantity') }}</th>
                    <th class="p-3 text-sm font-semibold">I{{ __('Voucher No.') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Supplier') }}</th>
                    <th class="p-3 text-sm font-semibold text-right">{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($stockEntries as $entry)
                    <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                        <td class="p-3 text-zinc-600 dark:text-zinc-400">
                            {{ $entry->created_at->format('d M, Y (h:i A)') }}
                        </td>
                        <td class="p-3">
                            <span class="font-medium">{{ $entry->product->name_bn }}</span><br>
                            <span class="text-xs text-zinc-500">{{ $entry->product->category->name ?? '' }}</span>
                        </td>
                        <td class="p-3 text-center text-green-600 font-bold">+ {{ $entry->quantity }}</td>
                        <td class="p-3 text-zinc-500">{{ $entry->voucher_no ?? '-' }}</td>
                        <td class="p-3 text-zinc-500">{{ $entry->supplier ?? '-' }}</td>
                        <td class="p-3 text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" variant="outline" icon="pencil" wire:click="edit({{ $entry->id }})" :title="__('Edit')" />
                                <flux:button size="sm" variant="outline" icon="trash" class="text-red-500 hover:text-red-700 hover:bg-red-50" wire:click="deleteEntry({{ $entry->id }})" wire:confirm="{{ __('Are you sure you want to delete this entry? This will subtract the quantity from the main stock!') }}" :title="__('Delete')" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-10 text-center text-zinc-500">
                            {{ __('No stock entries have been made yet.') }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $stockEntries->links() }}
        </div>
    </flux:card>
</div>
