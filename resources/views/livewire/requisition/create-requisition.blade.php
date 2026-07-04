<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <flux:heading size="xl" class="border-b pb-2">{{ __('Submit New Demand') }}</flux:heading>

    <flux:card>
        <form wire:submit.prevent="submitDemand" class="space-y-6">
            <div class="space-y-4">
                @foreach($requisitionItems as $index => $item)
                    <div wire:key="row-{{ $index }}" class="flex flex-col md:flex-row gap-4 items-end bg-zinc-50 dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">

                        <div class="w-full md:w-1/4">
                            <flux:select
                                wire:model.live="selectedCategories.{{ $index }}"
                                label="{{ __('Category') }}"
                            >
                                <flux:select.option value="">
                                    Select Category
                                </flux:select.option>

                                @foreach($categories as $category)
                                    <flux:select.option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="flex-1">
                            <flux:select wire:model="requisitionItems.{{ $index }}.product_id" label="{{ __('Item Name') }}" searchable placeholder="Select Product">
                                @foreach($getProducts($index) as $product)
                                    <flux:select.option value="{{ $product->id }}">
                                        {{ $product->name_bn }} ({{ $product->name_en }})
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="w-full md:w-24">
                            <flux:input type="number" wire:model="requisitionItems.{{ $index }}.demanded_qty" label="{{ __('Qty') }}" min="1" />
                        </div>

                        <div class="w-full md:w-36">
                            <flux:select wire:model="requisitionItems.{{ $index }}.purpose" label="{{ __('Purpose') }}">
                                @foreach($purposes as $purpose)
                                    <flux:select.option value="{{ $purpose->name }}" wire:key="purpose-option-{{ $index }}-{{ $purpose->id }}">{{ $purpose->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="pb-1">
                            @if(count($requisitionItems) > 1)
                                <flux:button variant="danger" icon="trash" wire:click.prevent="removeRow({{ $index }})" />
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <flux:button variant="outline" icon="plus" wire:click.prevent="addRow">
                {{ __('Add New Item') }}
            </flux:button>

            <flux:separator />

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" icon="paper-airplane" wire:loading.attr="disabled">
                    {{ __('Submit Demand') }}
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
