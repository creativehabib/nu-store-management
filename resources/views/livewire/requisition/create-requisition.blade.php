<div class="max-w-5xl mx-auto space-y-6">
    <flux:heading size="xl" class="border-b pb-2">নতুন চাহিদা জমা দিন (Submit Demand)</flux:heading>

    <flux:card>
        <form wire:submit="submitDemand" class="space-y-6">

            <div class="space-y-4">
                @foreach($requisitionItems as $index => $item)
                    <div class="flex flex-col md:flex-row gap-4 items-end bg-zinc-50 dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">

                        <div class="flex-1">
                            <flux:select wire:model="requisitionItems.{{ $index }}.product_id" label="দ্রব্যের নাম" searchable placeholder="প্রোডাক্ট নির্বাচন করুন">
                                @foreach($products as $product)
                                    <flux:select.option value="{{ $product->id }}">
                                        {{ $product->name_bn }} ({{ $product->name_en }})
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="w-full md:w-32">
                            <flux:input type="number" wire:model="requisitionItems.{{ $index }}.demanded_qty" label="পরিমাণ" min="1" />
                        </div>

                        <div class="w-full md:w-48">
                            <flux:select wire:model="requisitionItems.{{ $index }}.purpose" label="এন্ট্রির বিবরণ">
                                <flux:select.option value="Official Use">Official Use</flux:select.option>
                                <flux:select.option value="Training Purpose">Training Purpose</flux:select.option>
                            </flux:select>
                        </div>

                        <div class="pb-1">
                            @if(count($requisitionItems) > 1)
                                <flux:button variant="danger" icon="trash" wire:click="removeRow({{ $index }})" />
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div>
                <flux:button variant="outline" icon="plus" wire:click="addRow">
                    নতুন দ্রব্য যোগ করুন
                </flux:button>
            </div>

            <flux:separator />

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" icon="paper-airplane">
                    চাহিদা জমা দিন
                </flux:button>
            </div>

        </form>
    </flux:card>
</div>
