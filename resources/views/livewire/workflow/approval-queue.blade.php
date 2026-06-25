<div class="max-w-7xl mx-auto space-y-6">
    <flux:heading size="xl" class="border-b pb-2">Approval Queue (অনুমোদনের অপেক্ষায়)</flux:heading>

    <flux:card class="bg-zinc-50 dark:bg-zinc-800/50">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" label="অনুসন্ধান করুন" placeholder="রিকুইজিশন নং, নাম বা PF No..." />
            </div>

            <div>
                <flux:input type="date" wire:model.live="start_date" label="শুরুর তারিখ" />
            </div>

            <div>
                <flux:input type="date" wire:model.live="end_date" label="শেষ তারিখ" />
            </div>

            <div>
                <flux:select wire:model.live="department" placeholder="দপ্তর নির্বাচন করুন" label="দপ্তর">
                    <flux:select.option value="">সকল দপ্তর</flux:select.option>
                    @foreach($departments as $dept)
                        <flux:select.option value="{{ $dept }}">{{ $dept }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>
    </flux:card>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700">
                    <th class="p-3 text-sm font-semibold">রিকুইজিশন নং</th>
                    <th class="p-3 text-sm font-semibold">আবেদনকারী</th>
                    <th class="p-3 text-sm font-semibold">দপ্তর</th>
                    <th class="p-3 text-sm font-semibold">তারিখ</th>
                    <th class="p-3 text-sm font-semibold text-right">অ্যাকশন</th>
                </tr>
                </thead>
                <tbody>
                @forelse($requisitions as $req)
                    <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                        <td class="p-3 font-medium">{{ $req->requisition_no }}</td>
                        <td class="p-3">{{ $req->user->name }} <br><span class="text-xs text-zinc-500">{{ $req->user->pf_no }}</span></td>
                        <td class="p-3">{{ $req->user->department }}</td>
                        <td class="p-3">{{ $req->created_at->format('d M, Y') }}</td>
                        <td class="p-3 text-right">
                            <flux:modal.trigger name="view-action-modal">
                                <flux:button size="sm" variant="primary" icon="document-magnifying-glass" wire:click="viewRequisition({{ $req->id }})">
                                    View & Approve
                                </flux:button>
                            </flux:modal.trigger>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-zinc-500">
                            <p class="text-lg font-medium">কোনো রিকুইজিশন খুঁজে পাওয়া যায়নি।</p>
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

    @if($selectedRequisition)
        <!-- Data Modal (সবসময় পেজে থাকবে) -->
        <flux:modal name="view-action-modal" class="md:w-3/4 lg:w-2/3">

            @if($selectedRequisition)
                <!-- যখন ডাটা লোড হয়ে যাবে, তখন এই অংশ দেখাবে -->
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">রিকুইজিশন নং: {{ $selectedRequisition->requisition_no }}</flux:heading>
                        <p class="text-sm text-zinc-500 mt-1">আবেদনকারী: {{ $selectedRequisition->user->name }} ({{ $selectedRequisition->user->department }})</p>
                    </div>

                    <flux:separator />

                    <!-- আইটেম টেবিল -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse border border-zinc-200 dark:border-zinc-700">
                            <thead>
                            <tr class="bg-zinc-50 dark:bg-zinc-800 border-b dark:border-zinc-700">
                                <th class="p-2 text-sm border-r dark:border-zinc-700">দ্রব্যের নাম</th>
                                <th class="p-2 text-sm border-r dark:border-zinc-700 text-center">বর্তমান স্টক</th>
                                <th class="p-2 text-sm border-r dark:border-zinc-700 text-center">চাহিদা</th>
                                <th class="p-2 text-sm text-center">সরবরাহ (নির্ধারণ করুন)</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($selectedRequisition->items as $item)
                                <tr class="border-b dark:border-zinc-700">
                                    <td class="p-2 border-r dark:border-zinc-700">{{ $item->product->name_bn }}</td>
                                    <td class="p-2 border-r dark:border-zinc-700 text-center font-bold text-blue-600">{{ $item->product->stock }}</td>
                                    <td class="p-2 border-r dark:border-zinc-700 text-center">{{ $item->demanded_qty }}</td>
                                    <td class="p-2 text-center w-32">
                                        <!-- ইনপুটের সাথে .defer বা শুধু wire:model ব্যবহার করুন -->
                                        <flux:input type="number" wire:model="suppliedQuantities.{{ $item->id }}" min="0" max="{{ $item->product->stock }}" />
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Comment Box -->
                    <div>
                        <flux:textarea wire:model="comment" label="নোট / কমেন্ট (অপশনাল)" placeholder="যেমন: অনুমোদিত..." rows="2" />
                    </div>

                    <div class="flex justify-between items-center mt-4">
                        <flux:button variant="danger" icon="arrow-uturn-left" wire:click="processAction('return')" wire:confirm="আপনি কি এটি ফেরত পাঠাতে চান?">
                            Send Back
                        </flux:button>

                        <div class="flex gap-2">
                            <flux:modal.close>
                                <flux:button variant="ghost">বাতিল</flux:button>
                            </flux:modal.close>

                            <flux:button variant="primary" icon="check-badge" wire:click="processAction('approve')" wire:confirm="আপনি কি এটি অনুমোদন করতে চান?">
                                Approve & Forward
                            </flux:button>
                        </div>
                    </div>
                </div>
            @else
                <!-- ডাটা লোড হওয়ার আগের মুহূর্তে এটি দেখাবে (Excellent UX) -->
                <div class="py-12 flex flex-col items-center justify-center text-zinc-500">
                    <svg class="animate-spin h-8 w-8 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="font-medium text-lg">ফাইল লোড হচ্ছে, অপেক্ষা করুন...</p>
                </div>
            @endif

        </flux:modal>
    @endif
</div>
