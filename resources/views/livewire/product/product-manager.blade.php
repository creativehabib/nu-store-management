<div class="max-w-6xl mx-auto space-y-6">
    <flux:heading size="xl" class="border-b pb-2">প্রোডাক্ট / ইনভেন্টরি ম্যানেজমেন্ট</flux:heading>

    <flux:card>
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                <div>
                    <flux:select wire:model="category_id" label="ক্যাটাগরি" placeholder="ক্যাটাগরি নির্বাচন করুন" required>
                        @foreach($categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div>
                    <flux:input wire:model="name_bn" label="দ্রব্যের নাম (বাংলা)" placeholder="যেমন: অফসেট কাগজ A4" required />
                </div>
                <div>
                    <flux:input wire:model="name_en" label="দ্রব্যের নাম (English)" placeholder="যেমন: Offset Paper A4" required />
                </div>
                <div>
                    <flux:input type="number" wire:model="stock" label="প্রাথমিক স্টক" min="0" required />
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                @if($isEditMode)
                    <flux:button type="button" variant="outline" wire:click="resetFields">বাতিল</flux:button>
                @endif
                <flux:button type="submit" variant="primary">
                    {{ $isEditMode ? 'আপডেট করুন' : 'সেভ করুন' }}
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
                    <th class="p-3 text-sm font-semibold">ক্যাটাগরি</th>
                    <th class="p-3 text-sm font-semibold">নাম (বাংলা)</th>
                    <th class="p-3 text-sm font-semibold">নাম (English)</th>
                    <th class="p-3 text-sm font-semibold text-center">স্টক পরিমাণ</th>
                    <th class="p-3 text-sm font-semibold text-right">অ্যাকশন</th>
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
                            <flux:button size="sm" variant="danger" icon="trash" wire:click="delete({{ $product->id }})" wire:confirm="মুছে ফেলতে চান?" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-4 text-center text-zinc-500">
                            কোনো প্রোডাক্ট পাওয়া যায়নি।
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
