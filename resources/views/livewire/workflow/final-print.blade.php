<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    {{-- print:flex print:flex-col print:min-h-screen যুক্ত করা হয়েছে --}}
    <div id="print-area" class="bg-white text-black p-10 shadow-lg rounded-lg border border-zinc-200 print:shadow-none print:border-none print:p-0 print:flex print:flex-col print:min-h-screen">

        {{-- উপরের সমস্ত কন্টেন্টকে flex-1 এর ভেতরে রাখা হলো যাতে এটি ফাঁকা জায়গা দখল করে ফুটারকে নিচে ঠেলে দেয় --}}
        <div class="flex-1">
            <div class="text-center border-b-2 border-black pb-4 mb-6">
                <h1 class="text-3xl font-bold">{{ __('National University') }}</h1>
                <p class="text-lg">{{ __('Bangladesh') }}</p>
                <p class="text-lg font-semibold">{{ __('Office of the Teacher Training') }}</p>
                <h2 class="text-xl font-bold mt-4 underline inline-block">{{ __('Store Requisition Form') }}</h2>
            </div>

            <div class="flex justify-between items-end mb-6 text-sm">
                <div class="space-y-1">
                    <p><strong>{{ __('Name:') }}</strong> {{ $requisition->user->name }}</p>
                    <p><strong>{{ __('Designation:') }}</strong> {{ $requisition->user->post }}</p>
                    <p><strong>{{ __('Department:') }}</strong> {{ $requisition->user->department }}</p>
                </div>
                <div class="space-y-1 text-right">
                    <p><strong>{{ __('Serial No:') }}</strong> {{ $requisition->requisition_no }}</p>
                    <p><strong>{{ __('Date:') }}</strong> {{ $requisition->created_at->format('d M, Y') }}</p>
                </div>
            </div>

            <table class="w-full border-collapse border border-black mb-16 text-sm">
                <thead>
                <tr>
                    <th class="border border-black p-2 w-16 text-center">{{ __('Sl No.') }}</th>
                    <th class="border border-black p-2">{{ __('Item Name') }}</th>
                    <th class="border border-black p-2 text-center w-32">{{ __('Demanded Quantity') }}</th>
                    <th class="border border-black p-2 text-center w-32">{{ __('Supplied Quantity') }}</th>
                    <th class="border border-black p-2 text-center w-32">{{ __('Purpose/Description') }}</th>
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
                    <p class="border-t border-black pt-2 mx-2">{{ __('Receiver\'s Signature & Date') }}</p>
                </div>

                <div class="flex flex-col justify-end items-center">
                    @if($this->getSignature('initiator'))
                        <img src="{{ $this->getSignature('initiator') }}" class="h-10 mb-1" alt="signature" />
                    @else
                        <div class="h-10 mb-1"></div>
                    @endif
                    <p class="border-t border-black pt-2 w-full mx-2">{{ __('Prepared By') }}</p>
                </div>

                <div class="flex flex-col justify-end items-center">
                    @if($this->getSignature('assistant_director'))
                        <img src="{{ $this->getSignature('assistant_director') }}" class="h-10 mb-1" alt="signature" />
                    @else
                        <div class="h-10 mb-1"></div>
                    @endif
                    <p class="border-t border-black pt-2 w-full mx-2">{{ __('Assistant Director') }}</p>
                </div>

                <div class="flex flex-col justify-end items-center">
                    @if($this->getSignature('deputy_director'))
                        <img src="{{ $this->getSignature('deputy_director') }}" class="h-10 mb-1" alt="signature" />
                    @else
                        <div class="h-10 mb-1"></div>
                    @endif
                    <p class="border-t border-black pt-2 w-full mx-2">{{ __('Deputy Director') }}</p>
                </div>

                <div class="flex flex-col justify-end items-center">
                    @if($this->getSignature('director'))
                        <img src="{{ $this->getSignature('director') }}" class="h-10 mb-1" alt="signature" />
                    @else
                        <div class="h-10 mb-1"></div>
                    @endif
                    <p class="border-t border-black pt-2 w-full mx-2">{{ __('Director') }}</p>
                </div>
            </div>
        </div> {{-- flex-1 div এর শেষ --}}

        <!-- Print Footer (শুধুমাত্র প্রিন্টে দেখাবে) -->
        <div class="hidden print:flex justify-between items-end mt-auto pt-2  text-xs text-black border-t border-black">
            <div class="w-1/3">
                <p><strong>{{ __('Printed By:') }}</strong> {{ auth()->user()->name ?? 'System Admin' }}</p>
            </div>

            <div class="w-1/3 text-center">
                <p class="italic text-gray-600">{{ __('Generated via Automated System') }}</p>
            </div>

            <div class="w-1/3 text-right">
                <p><strong>{{ __('Date & Time:') }}</strong> {{ now()->format('d M, Y - h:i A') }}</p>
            </div>
        </div>

    </div>

    <div class="flex justify-end gap-4 mt-4 print:hidden">
        <flux:button variant="outline" icon="printer" onclick="window.print()">
            {{ __('Print Layout') }}
        </flux:button>

        @if($requisition->status === 'director_approved')
            <flux:button variant="primary" icon="archive-box-arrow-down" wire:click="distributeStock" wire:confirm="{{ __('Are you sure you want to deduct the stock? Once deducted, this cannot be undone.') }}">
                {{ __('Distribute Product (Stock Minus)') }}
            </flux:button>
        @elseif($requisition->status === 'distributed')
            <flux:badge color="green" size="lg" icon="check-badge">{{ __('Product distribution and stock deduction completed') }}</flux:badge>
        @endif
    </div>
</div>

<style>
    @media print {
        body { visibility: hidden; background: white; }
        #print-area, #print-area * { visibility: visible; }

        /* Flexbox সাপোর্টের জন্য min-height: 100vh যোগ করা হয়েছে */
        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            min-height: 100vh;
            border: none;
            box-shadow: none;
        }

        header, aside, .flux-sidebar, .print\:hidden { display: none !important; }
    }
</style>
