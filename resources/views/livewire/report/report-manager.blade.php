<div class="max-w-7xl mx-auto space-y-6">
    <flux:heading size="xl" class="border-b pb-2 print:hidden">রিপোর্টিং এবং এক্সপোর্ট</flux:heading>

    <flux:card class="print:hidden mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <flux:input type="date" wire:model.live="start_date" label="শুরুর তারিখ" />
            </div>
            <div>
                <flux:input type="date" wire:model.live="end_date" label="শেষ তারিখ" />
            </div>
            <div>
                <flux:select wire:model.live="department" label="দপ্তর অনুযায়ী ফিল্টার">
                    <flux:select.option value="">সকল দপ্তর</flux:select.option>
                    @foreach($departments as $dept)
                        <flux:select.option value="{{ $dept }}">{{ $dept }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div>
                <flux:select wire:model.live="status" label="স্ট্যাটাস অনুযায়ী ফিল্টার">
                    <flux:select.option value="">সকল স্ট্যাটাস</flux:select.option>
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="director_approved">Approved (Ready)</flux:select.option>
                    <flux:select.option value="distributed">Distributed</flux:select.option>
                    <flux:select.option value="returned">Returned</flux:select.option>
                </flux:select>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <flux:button variant="outline" icon="printer" onclick="window.print()">
                প্রিন্ট (PDF)
            </flux:button>

            <flux:button variant="primary" icon="document-arrow-down" wire:click="exportCSV">
                এক্সপোর্ট (Excel/CSV)
            </flux:button>
        </div>
    </flux:card>

    <div id="printable-area">

        <div class="hidden print:block text-center mb-6">
            <h1 class="text-2xl font-bold text-black">জাতীয় বিশ্ববিদ্যালয়, বাংলাদেশ</h1>
            <h2 class="text-lg font-semibold text-black mt-1">স্টোর রিকুইজিশন সামারি রিপোর্ট</h2>
            <p class="text-sm mt-1 text-black">রিপোর্টের সময়কাল: {{ \Carbon\Carbon::parse($start_date)->format('d M, Y') }} থেকে {{ \Carbon\Carbon::parse($end_date)->format('d M, Y') }}</p>
            @if($department) <p class="text-sm text-black">দপ্তর: {{ $department }}</p> @endif
            <hr class="border-black my-4">
        </div>

        <flux:card class="print:border-none print:shadow-none print:p-0 print:bg-transparent">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse print:border-black print:border">
                    <thead>
                    <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700 print:bg-white print:border-black">
                        <th class="p-3 print:p-2 print:border-black print:border text-sm font-semibold">তারিখ</th>
                        <th class="p-3 print:p-2 print:border-black print:border text-sm font-semibold">রিকুইজিশন নং</th>
                        <th class="p-3 print:p-2 print:border-black print:border text-sm font-semibold">আবেদনকারী ও দপ্তর</th>
                        <th class="p-3 print:p-2 print:border-black print:border text-sm font-semibold text-center">চাহিদা</th>
                        <th class="p-3 print:p-2 print:border-black print:border text-sm font-semibold text-center">সরবরাহ</th>
                        <th class="p-3 print:p-2 print:border-black print:border text-sm font-semibold">স্ট্যাটাস</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($reportData as $req)
                        <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition print:border-black">
                            <td class="p-3 print:p-2 print:border-black print:border text-sm">{{ $req->created_at->format('d M, Y') }}</td>
                            <td class="p-3 print:p-2 print:border-black print:border text-sm font-medium">{{ $req->requisition_no }}</td>
                            <td class="p-3 print:p-2 print:border-black print:border text-sm">
                                {{ $req->user->name }} <br><span class="text-xs text-zinc-500 print:text-black">{{ $req->user->department }}</span>
                            </td>
                            <td class="p-3 print:p-2 print:border-black print:border text-sm text-center">{{ $req->items->sum('demanded_qty') }} টি</td>
                            <td class="p-3 print:p-2 print:border-black print:border text-sm text-center font-bold text-green-600 print:text-black">{{ $req->items->sum('supplied_qty') }} টি</td>
                            <td class="p-3 print:p-2 print:border-black print:border text-sm">
                                <flux:badge size="sm" color="{{ $req->status === 'distributed' ? 'green' : ($req->status === 'returned' ? 'red' : 'amber') }} print:border-none print:text-black print:bg-transparent print:p-0">
                                    {{ strtoupper($req->status) }}
                                </flux:badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-zinc-500 print:text-black print:border-black print:border">
                                এই ফিল্টার অনুযায়ী কোনো ডাটা পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </flux:card>
    </div>
</div>

<style>
    @media print {
        /* পুরো ওয়েবসাইটের সব কিছু লুকিয়ে ফেলা হলো */
        body * {
            visibility: hidden;
        }

        /* শুধুমাত্র আমাদের টার্গেট করা এরিয়াটি দৃশ্যমান করা হলো */
        #printable-area, #printable-area * {
            visibility: visible;
        }

        /* টার্গেট করা এরিয়াটিকে পেজের একদম টপ-লেফটে নিয়ে আসা হলো */
        #printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* ব্যাকগ্রাউন্ড সাদা এবং মার্জিন রিসেট করা হলো */
        html, body {
            background-color: white !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        /* ফ্লাক্স কার্ডের বর্ডার এবং শ্যাডো প্রিন্টের সময় রিমুভ করা হলো */
        .flux-card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
