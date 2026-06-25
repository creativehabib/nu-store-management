<div class="max-w-6xl mx-auto space-y-6">
    <flux:heading size="xl" class="border-b pb-2">{{ __('Product / Inventory Management') }}</flux:heading>

    <flux:card>
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                <div>
                    <flux:select wire:model="category_id" :label="__('Category')" :placeholder="__('Select Category')" required>
                        @foreach($categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:input wire:model="name_bn" :label="__('Item Name (Bangla)')" :placeholder="__('e.g. Offset Paper A4')" required />
                </div>
                <div>
                    <flux:input wire:model="name_en" :label="__('Item Name (English)')" :placeholder="__('e.g. Offset Paper A4 (EN)')" required />
                </div>
                <div>
                    <flux:input type="number" wire:model="stock" :label="__('Initial Stock')" min="0" required />
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                @if($isEditMode)
                    <flux:button type="button" variant="outline" wire:click="resetFields">{{ __('Cancel') }}</flux:button>
                @endif
                <flux:button type="submit" variant="primary">
                    {{ $isEditMode ? __('Update') : __('Save') }}
                </flux:button>
            </div>
        </form>
    </flux:card>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700">
                    <th class="p-3 text-sm font-semibold">#</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Category') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Name (Bangla)') }}</th>
                    <th class="p-3 text-sm font-semibold">{{ __('Name (English)') }}</th>
                    <th class="p-3 text-sm font-semibold text-center">{{ __('Stock Quantity') }}</th>
                    <th class="p-3 text-sm font-semibold text-right">{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($products as $index => $product)
                    <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                        <td class="p-3">{{ $products->firstItem() + $index }}</td>
                        <td class="p-3">{{ $product->category->name ?? 'N/A' }}</td>
                        <td class="p-3">{{ $product->name_bn }}</td>
                        <td class="p-3">{{ $product->name_en }}</td>
                        <td class="p-3 text-center font-bold {{ $product->stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $product->stock }}
                        </td>
                        <td class="p-3 text-right flex justify-end gap-2">
                            <flux:button size="sm" variant="outline" icon="pencil" wire:click="edit({{ $product->id }})" />
                            <flux:button size="sm" variant="danger" icon="trash" wire:click="delete({{ $product->id }})" wire:confirm="{{ __('Are you sure you want to delete this?') }}" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-zinc-500">
                            {{ __('No products found.') }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </flux:card>
</div>
