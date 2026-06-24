<div class="max-w-5xl mx-auto space-y-6">

    <div id="print-area" class="bg-white text-black p-10 shadow-lg rounded-lg border border-zinc-200 print:shadow-none print:border-none print:p-0">

        <div class="text-center border-b-2 border-black pb-4 mb-6">
            <h1 class="text-2xl font-bold">জাতীয় বিশ্ববিদ্যালয়</h1>
            <p class="text-lg">গাজীপুর-১৭০৪</p>
            <p class="text-lg font-semibold">শিক্ষক প্রশিক্ষণ দপ্তর</p>
            <h2 class="text-xl font-bold mt-4 underline inline-block">স্টোর রিকুইজিশন ফরম</h2>
        </div>

        <div class="flex justify-between items-end mb-6 text-sm">
            <div class="space-y-1">
                <p><strong>নাম:</strong> {{ $requisition->user->name }}</p>
                <p><strong>পদবি:</strong> {{ $requisition->user->post }}</p>
                <p><strong>দপ্তর:</strong> {{ $requisition->user->department }}</p>
            </div>
            <div class="space-y-1 text-right">
                <p><strong>ক্রমিক নং:</strong> {{ $requisition->requisition_no }}</p>
                <p><strong>তারিখ:</strong> {{ $requisition->created_at->format('d M, Y') }}</p>
            </div>
        </div>

        <table class="w-full border-collapse border border-black mb-16 text-sm">
            <thead>
            <tr>
                <th class="border border-black p-2 w-16 text-center">ক্রমিক নং</th>
                <th class="border border-black p-2">দ্রব্যের নাম</th>
                <th class="border border-black p-2 text-center w-32">চাহিদার পরিমাণ</th>
                <th class="border border-black p-2 text-center w-32">সরবরাহের পরিমাণ</th>
                <th class="border border-black p-2 text-center w-32">এন্ট্রির বিবরণ</th>
            </tr>
            </thead>
            <tbody>
            @foreach($requisition->items as $index => $item)
                <tr>
                    <td class="border border-black p-2 text-center">{{ $index + 1 }}</td>
                    <td class="border border-black p-2">{{ $item->product->name_bn }} <br><span class="text-xs text-gray-600">{{ $item->product->name_en }}</span></td>
                    <td class="border border-black p-2 text-center">{{ $item->demanded_qty }}</td>
                    <td class="border border-black p-2 text-center font-bold">{{ $item->supplied_qty }}</td>
                    <td class="border border-black p-2 text-center">{{ $item->purpose }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="grid grid-cols-5 gap-4 mt-24 text-center text-xs font-semibold">
            <div class="flex flex-col justify-end">
                <p class="border-t border-black pt-2 mx-2">গ্রহণকারীর স্বাক্ষর ও তারিখ</p>
            </div>

            <div class="flex flex-col justify-end items-center">
                @if($this->getSignature('initiator'))
                    <img src="{{ $this->getSignature('initiator') }}" class="h-10 mb-1" alt="signature" />
                @else
                    <div class="h-10 mb-1"></div>
                @endif
                <p class="border-t border-black pt-2 w-full mx-2">প্রস্তুতকারী</p>
            </div>

            <div class="flex flex-col justify-end items-center">
                @if($this->getSignature('assistant_director'))
                    <img src="{{ $this->getSignature('assistant_director') }}" class="h-10 mb-1" alt="signature" />
                @else
                    <div class="h-10 mb-1"></div>
                @endif
                <p class="border-t border-black pt-2 w-full mx-2">সহকারী পরিচালক</p>
            </div>

            <div class="flex flex-col justify-end items-center">
                @if($this->getSignature('deputy_director'))
                    <img src="{{ $this->getSignature('deputy_director') }}" class="h-10 mb-1" alt="signature" />
                @else
                    <div class="h-10 mb-1"></div>
                @endif
                <p class="border-t border-black pt-2 w-full mx-2">উপ-পরিচালক</p>
            </div>

            <div class="flex flex-col justify-end items-center">
                @if($this->getSignature('director'))
                    <img src="{{ $this->getSignature('director') }}" class="h-10 mb-1" alt="signature" />
                @else
                    <div class="h-10 mb-1"></div>
                @endif
                <p class="border-t border-black pt-2 w-full mx-2">পরিচালক</p>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-4 mt-4 print:hidden">
        <flux:button variant="outline" icon="printer" onclick="window.print()">
            Print Layout
        </flux:button>

        @if($requisition->status === 'director_approved')
            <flux:button variant="primary" icon="archive-box-arrow-down" wire:click="distributeStock" wire:confirm="আপনি কি স্টক মাইনাস করতে নিশ্চিত? একবার স্টক মাইনাস করলে এটি আর পরিবর্তন করা যাবে না।">
                Distribute Product (Stock Minus)
            </flux:button>
        @elseif($requisition->status === 'distributed')
            <flux:badge color="green" size="lg" icon="check-badge">পণ্য বিতরণ ও স্টক মাইনাস সম্পন্ন হয়েছে</flux:badge>
        @endif
    </div>
</div>

<style>
    @media print {
        body { visibility: hidden; background: white; }
        #print-area, #print-area * { visibility: visible; }
        #print-area { position: absolute; left: 0; top: 0; width: 100%; border: none; box-shadow: none; }
        header, aside, .flux-sidebar, .print\:hidden { display: none !important; }
    }
</style>
