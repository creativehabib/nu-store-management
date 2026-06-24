<div class="max-w-7xl mx-auto space-y-6">
    <flux:heading size="xl" class="border-b pb-2">স্টক ইন / পারচেজ এন্ট্রি</flux:heading>

    <flux:card class="{{ $isEditMode ? 'border-indigo-500 shadow-md' : '' }}">
        @if($isEditMode)
            <div class="mb-4 text-indigo-600 font-bold flex justify-between items-center border-b pb-2">
                <span><flux:icon.pencil-square class="w-5 h-5 inline mr-1" /> এন্ট্রি আপডেট করছেন</span>
            </div>
        @endif

        <form wire:submit="saveStock" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                <div>
                    <flux:select wire:model="product_id" label="দ্রব্যের নাম" searchable placeholder="প্রোডাক্ট নির্বাচন করুন" required>
                        @foreach($products as $product)
                            <flux:select.option value="{{ $product->id }}">
                                {{ $product->name_bn }} (বর্তমান স্টক: {{ $product->stock }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:input type="number" wire:model="quantity" label="আমদানির পরিমাণ" min="1" required placeholder="যেমন: ৫০" />
                </div>
                <div>
                    <flux:input wire:model="voucher_no" label="ভাউচার / মেমো নং (ঐচ্ছিক)" placeholder="যেমন: V-402" />
                </div>
                <div>
                    <flux:input wire:model="supplier" label="সরবরাহকারী/উৎস (ঐচ্ছিক)" placeholder="যেমন: সরকারি স্টোর" />
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                @if($isEditMode)
                    <flux:button type="button" variant="outline" wire:click="resetFields">বাতিল</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">আপডেট করুন</flux:button>
                @else
                    <flux:button type="submit" variant="primary" icon="plus">স্টক যুক্ত করুন (Stock In)</flux:button>
                @endif
            </div>
        </form>
    </flux:card>

    <flux:card>
        <div class="mb-4">
            <flux:heading size="lg">সাম্প্রতিক এন্ট্রি সমূহ</flux:heading>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700">
                    <th class="p-3 text-sm font-semibold">তারিখ ও সময়</th>
                    <th class="p-3 text-sm font-semibold">দ্রব্যের নাম</th>
                    <th class="p-3 text-sm font-semibold text-center">পরিমাণ</th>
                    <th class="p-3 text-sm font-semibold">ভাউচার নং</th>
                    <th class="p-3 text-sm font-semibold">সরবরাহকারী</th>
                    <th class="p-3 text-sm font-semibold text-right">অ্যাকশন</th>
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
                                <flux:button size="sm" variant="outline" icon="pencil" wire:click="edit({{ $entry->id }})" title="এডিট করুন" />
                                <flux:button size="sm" variant="outline" icon="trash" class="text-red-500 hover:text-red-700 hover:bg-red-50" wire:click="deleteEntry({{ $entry->id }})" wire:confirm="আপনি কি এই এন্ট্রিটি মুছে ফেলতে চান? এটি মুছলে মূল স্টক থেকেও এই পরিমাণ মাইনাস হয়ে যাবে!" title="ডিলিট করুন" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-10 text-center text-zinc-500">
                            এখন পর্যন্ত কোনো স্টক এন্ট্রি করা হয়নি।
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
