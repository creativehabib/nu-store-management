<div class="max-w-6xl mx-auto space-y-6">
    <flux:heading size="xl" class="border-b pb-2">আমার রিকুইজিশন সমূহ (Tracking & History)</flux:heading>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700">
                    <th class="p-3 text-sm font-semibold">রিকুইজিশন নং</th>
                    <th class="p-3 text-sm font-semibold">আবেদনের তারিখ</th>
                    <th class="p-3 text-sm font-semibold">আইটেম সংখ্যা</th>
                    <th class="p-3 text-sm font-semibold">বর্তমান স্ট্যাটাস</th>
                    <th class="p-3 text-sm font-semibold text-right">ট্র্যাকিং</th>
                </tr>
                </thead>
                <tbody>
                @forelse($requisitions as $req)
                    <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                        <td class="p-3 font-medium">{{ $req->requisition_no }}</td>
                        <td class="p-3">{{ $req->created_at->format('d M, Y (h:i A)') }}</td>
                        <td class="p-3">{{ $req->items()->count() }} টি</td>
                        <td class="p-3">
                            @if($req->status === 'pending')
                                <flux:badge color="amber">Pending (Initiator-এর কাছে)</flux:badge>
                            @elseif($req->status === 'initiator_checked')
                                <flux:badge color="blue">Checked (AD-এর কাছে)</flux:badge>
                            @elseif($req->status === 'ad_approved')
                                <flux:badge color="indigo">AD Approved (DD-এর কাছে)</flux:badge>
                            @elseif($req->status === 'dd_approved')
                                <flux:badge color="purple">DD Approved (Director-এর কাছে)</flux:badge>
                            @elseif($req->status === 'director_approved')
                                <flux:badge color="green">Director Approved (প্রস্তুত)</flux:badge>
                            @elseif($req->status === 'distributed')
                                <flux:badge color="zinc">Distributed (বিতরণ সম্পন্ন)</flux:badge>
                            @elseif($req->status === 'returned')
                                <flux:badge color="red">Returned (ফেরত এসেছে)</flux:badge>
                            @endif
                        </td>
                        <td class="p-3 text-right">
                            <flux:button size="sm" variant="outline" icon="clock" wire:click="viewHistory({{ $req->id }})">
                                হিস্ট্রি দেখুন
                            </flux:button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-zinc-500">
                            <p class="text-lg font-medium">আপনি এখনও কোনো রিকুইজিশন জমা দেননি।</p>
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

    <!-- History & Tracking Modal -->
    @if($selectedRequisition)
        <flux:modal name="history-modal" class="md:w-3/4 lg:w-2/3">
            @if($selectedRequisition)
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">ট্র্যাকিং ডিটেইলস: {{ $selectedRequisition->requisition_no }}</flux:heading>
                    </div>

                    <flux:separator />

                    <div>
                        <h3 class="font-semibold mb-2">দ্রব্যের বিবরণ ও অনুমোদন:</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse border border-zinc-200 dark:border-zinc-700 text-sm">
                                <thead>
                                <tr class="bg-zinc-50 dark:bg-zinc-800 border-b dark:border-zinc-700">
                                    <th class="p-2 border-r dark:border-zinc-700">দ্রব্যের নাম</th>
                                    <th class="p-2 border-r dark:border-zinc-700 text-center">আপনি চেয়েছেন</th>
                                    <th class="p-2 text-center text-green-600">অনুমোদন দেওয়া হয়েছে</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($selectedRequisition->items as $item)
                                    <tr class="border-b dark:border-zinc-700">
                                        <td class="p-2 border-r dark:border-zinc-700">{{ $item->product->name_bn }}</td>
                                        <td class="p-2 border-r dark:border-zinc-700 text-center">{{ $item->demanded_qty }}</td>
                                        <td class="p-2 text-center font-bold text-green-600">{{ $item->supplied_qty }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold mb-4">অ্যাপ্রুভাল হিস্ট্রি (Timeline):</h3>

                        @if(empty($selectedRequisition->approval_history))
                            <div class="p-4 bg-amber-50 dark:bg-amber-900/20 text-amber-600 rounded-lg text-sm border border-amber-200 dark:border-amber-800">
                                এখনো কোনো অফিসার এটি দেখেননি। এটি স্টোর কিউতে অপেক্ষমান রয়েছে।
                            </div>
                        @else
                            <div class="space-y-4 border-l-2 border-zinc-200 dark:border-zinc-700 ml-3 pl-4">
                                @foreach($selectedRequisition->approval_history as $history)
                                    <div class="relative">
                                        <div class="absolute -left-6 mt-1.5 h-3 w-3 rounded-full {{ $history['action'] === 'returned' ? 'bg-red-500' : 'bg-green-500' }}"></div>

                                        <div class="bg-zinc-50 dark:bg-zinc-800 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-semibold text-sm">{{ $history['name'] }} <span class="text-xs font-normal text-zinc-500">({{ ucwords(str_replace('_', ' ', $history['role'])) }})</span></p>
                                                    <p class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($history['date'])->format('d M, Y - h:i A') }}</p>
                                                </div>
                                                <div>
                                                    @if($history['action'] === 'returned')
                                                        <flux:badge color="red" size="sm">Returned</flux:badge>
                                                    @else
                                                        <flux:badge color="green" size="sm">Approved / Forwarded</flux:badge>
                                                    @endif
                                                </div>
                                            </div>

                                            @if(!empty($history['comment']))
                                                <div class="mt-2 text-sm bg-white dark:bg-zinc-900 p-2 rounded border border-zinc-100 dark:border-zinc-700">
                                                    <strong>কমেন্ট:</strong> {{ $history['comment'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-end mt-4">
                        <flux:modal.close>
                            <flux:button variant="ghost">বন্ধ করুন</flux:button>
                        </flux:modal.close>
                    </div>
                </div>
            @else
                <div class="py-12 flex flex-col items-center justify-center text-zinc-500">
                    <svg class="animate-spin h-8 w-8 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="font-medium text-lg">হিস্ট্রি লোড হচ্ছে...</p>
                </div>
            @endif
        </flux:modal>
    @endif
</div>
