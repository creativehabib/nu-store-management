<div class="max-w-6xl mx-auto space-y-6">
    <flux:heading size="xl" class="border-b pb-2">Initiator Queue (অপেক্ষমান রিকুইজিশন)</flux:heading>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700">
                    <th class="p-3 text-sm font-semibold">রিকুইজিশন নং</th>
                    <th class="p-3 text-sm font-semibold">আবেদনকারী</th>
                    <th class="p-3 text-sm font-semibold">দপ্তর</th>
                    <th class="p-3 text-sm font-semibold">তারিখ</th>
                    <th class="p-3 text-sm font-semibold">স্ট্যাটাস</th>
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

                        <td class="p-3">
                            @if($req->status === 'returned')
                                <flux:badge color="red">Returned</flux:badge>
                            @elseif($req->status === 'director_approved')
                                <flux:badge color="green">Approved (Ready)</flux:badge>
                            @elseif($req->status === 'distributed')
                                <flux:badge color="indigo">Distributed</flux:badge>
                            @else
                                <flux:badge color="amber">Pending</flux:badge>
                            @endif
                        </td>

                        <td class="p-3 text-right">
                            @if($req->status === 'pending' || $req->status === 'returned')
                                <flux:button size="sm" variant="primary" icon="eye" wire:click="viewRequisition({{ $req->id }})">
                                    View & Action
                                </flux:button>
                            @elseif($req->status === 'director_approved' || $req->status === 'distributed')
                                <flux:button size="sm" variant="outline" icon="printer" href="{{ route('workflow.print', $req->id) }}" wire:navigate>
                                    Print & Distribute
                                </flux:button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-10 text-center text-zinc-500">
                            <p class="text-lg font-medium">আপনার কিউতে কোনো রিকুইজিশন নেই।</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>

    @if($selectedRequisition)
        <flux:modal name="approve-modal-{{ $selectedRequisition->id }}" class="md:w-3/4 lg:w-2/3">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">রিকুইজিশন ডিটেইলস: {{ $selectedRequisition->requisition_no }}</flux:heading>
                    <p class="text-sm text-zinc-500 mt-1">আবেদনকারী: {{ $selectedRequisition->user->name }} ({{ $selectedRequisition->user->department }})</p>
                </div>

                <flux:separator />
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-zinc-200 dark:border-zinc-700">
                        <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-800 border-b dark:border-zinc-700">
                            <th class="p-2 text-sm border-r dark:border-zinc-700">দ্রব্যের নাম</th>
                            <th class="p-2 text-sm border-r dark:border-zinc-700 text-center">বর্তমান স্টক</th>
                            <th class="p-2 text-sm border-r dark:border-zinc-700 text-center">চাহিদার পরিমাণ</th>
                            <th class="p-2 text-sm text-center">সরবরাহের পরিমাণ নির্ধারণ</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($selectedRequisition->items as $item)
                            <tr class="border-b dark:border-zinc-700">
                                <td class="p-2 border-r dark:border-zinc-700">{{ $item->product->name_bn }} <br><span class="text-xs text-zinc-500">{{ $item->purpose }}</span></td>
                                <td class="p-2 border-r dark:border-zinc-700 text-center font-bold text-blue-600">{{ $item->product->stock }}</td>
                                <td class="p-2 border-r dark:border-zinc-700 text-center">{{ $item->demanded_qty }}</td>
                                <td class="p-2 text-center w-32">
                                    <flux:input type="number" wire:model="suppliedQuantities.{{ $item->id }}" min="0" max="{{ $item->product->stock }}" />
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div>
                    <flux:textarea wire:model="comment" label="নোট / কমেন্ট (অপশনাল)" placeholder="যেমন: স্টকে পর্যাপ্ত আছে..." rows="2" />
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">বাতিল</flux:button>
                    </flux:modal.close>

                    <flux:button variant="primary" icon="check-circle" wire:click="forwardRequisition" wire:confirm="আপনি কি এটি পরবর্তী ধাপে পাঠাতে চান?">
                        Forward to AD
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
