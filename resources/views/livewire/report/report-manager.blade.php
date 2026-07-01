<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <flux:heading size="xl" class="border-b pb-2 print:hidden">{{ __('Reports and Export') }}</flux:heading>

    @php
        // ইউজার সব ডিপার্টমেন্ট ফিল্টার করতে পারবে কি না তার লজিক
        $user = auth()->user();
        $isGlobalAdmin = in_array($user->role, ['admin', 'super_admin']);

        $storeRoles = ['initiator', 'assistant_director', 'deputy_director', 'director'];
        $isCentralStoreOfficer = setting('store_mode', 'departmental') === 'centralized'
                              && $user->department_id == setting('central_store_dept_id', 1)
                              && in_array($user->role, $storeRoles);

        $canFilterAllDepartments = $isGlobalAdmin || $isCentralStoreOfficer;
    @endphp

    <flux:card class="print:hidden mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <flux:input type="date" wire:model.live="start_date" :label="__('Start Date')" />
            </div>
            <div>
                <flux:input type="date" wire:model.live="end_date" :label="__('End Date')" />
            </div>

            <div>
                @if($canFilterAllDepartments)
                    <flux:select wire:model.live="department_id" :label="__('Filter by Department')">
                        <flux:select.option value="">{{ __('All Departments') }}</flux:select.option>
                        @foreach($departments as $dept)
                            <flux:select.option value="{{ $dept->id }}">{{ $dept->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @else
                    <flux:input disabled :label="__('Department')" :value="$user->department->name ?? 'N/A'" />
                @endif
            </div>

            <div>
                <flux:select wire:model.live="status" :label="__('Filter by Status')">
                    <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                    <flux:select.option value="pending">Pending</flux:select.option>
                    <flux:select.option value="director_approved">Approved (Ready)</flux:select.option>
                    <flux:select.option value="distributed">Distributed</flux:select.option>
                    <flux:select.option value="returned">Returned</flux:select.option>
                </flux:select>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <flux:button variant="ghost" icon="arrow-path" wire:click="resetFilters">
                {{ __('Reset Filters') }}
            </flux:button>

            <flux:button variant="outline" icon="printer" onclick="window.print()">
                {{ __('Print (PDF)') }}
            </flux:button>

            <flux:button variant="primary" icon="document-arrow-down" wire:click="exportCSV">
                {{ __('Export (CSV)') }}
            </flux:button>
        </div>
    </flux:card>

    <div id="printable-area">
        <div class="hidden print:block text-center mb-6">
            <h1 class="text-2xl font-bold text-black">{{ __('National University, Bangladesh') }}</h1>
            <h2 class="text-lg font-semibold text-black mt-1">{{ __('Store Requisition Summary Report') }}</h2>
            <p class="text-sm mt-1 text-black">{{ __('Report Period:') }} {{ \Carbon\Carbon::parse($start_date)->format('d M, Y') }} {{ __('to') }} {{ \Carbon\Carbon::parse($end_date)->format('d M, Y') }}</p>

            @if(!$canFilterAllDepartments)
                <p class="text-sm text-black font-semibold">{{ __('Department:') }} {{ $user->department->name ?? 'N/A' }}</p>
            @elseif($department_id)
                <p class="text-sm text-black">{{ __('Department:') }} {{ $departments->firstWhere('id', $department_id)?->name }}</p>
            @else
                <p class="text-sm text-black">{{ __('Department: All Departments') }}</p>
            @endif
            <hr class="border-black my-4">
        </div>
        <div class="grid grid-cols-2 gap-6 mb-6 print:gap-4">
            <flux:card class="text-center bg-amber-50 dark:bg-amber-900/20 print:border-black print:border print:shadow-none print:bg-transparent">
                <flux:heading size="lg" class="print:text-black">{{ __('Total Demanded Quantity') }}</flux:heading>
                <p class="text-3xl font-bold text-amber-600 print:text-black mt-2">
                    {{ $reportData->sum(fn($req) => $req->items->sum('demanded_qty')) }} {{ __('pcs') }}
                </p>
            </flux:card>

            <flux:card class="text-center bg-green-50 dark:bg-green-900/20 print:border-black print:border print:shadow-none print:bg-transparent">
                <flux:heading size="lg" class="print:text-black">{{ __('Total Supplied (Stock Out)') }}</flux:heading>
                <p class="text-3xl font-bold text-green-600 print:text-black mt-2">
                    {{-- শুধুমাত্র distributed হওয়া আইটেমগুলোকেই আমরা প্রকৃত Stock Out হিসেবে ধরব --}}
                    {{ $reportData->where('status', 'distributed')->sum(fn($req) => $req->items->sum('supplied_qty')) }} {{ __('pcs') }}
                </p>
            </flux:card>
        </div>
        <flux:card class="print:border-none print:shadow-none print:p-0 print:bg-transparent">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse print:border-black print:border text-sm">
                    <thead>
                    <tr class="bg-zinc-100 dark:bg-zinc-800 border-b dark:border-zinc-700 print:bg-white print:border-black">
                        <th class="p-3 print:p-2 print:border-black print:border text-sm font-semibold">{{ __('Date') }}</th>
                        <th class="p-3 print:p-2 print:border-black print:border text-sm font-semibold">{{ __('Requisition No') }}</th>
                        <th class="p-3 print:p-2 print:border-black print:border text-sm font-semibold">{{ __('Applicant & Department') }}</th>
                        <th class="p-3 print:p-2 print:border-black print:border text-sm font-semibold text-center">{{ __('Demand') }}</th>
                        <th class="p-3 print:p-2 print:border-black print:border text-sm font-semibold text-center">{{ __('Supply') }}</th>
                        <th class="p-3 print:p-2 print:border-black print:border text-sm font-semibold">{{ __('Status') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($reportData as $req)
                        <tr class="border-b dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition print:border-black">
                            <td class="p-3 print:p-2 print:border-black print:border text-sm">{{ $req->created_at->format('d M, Y') }}</td>
                            <td class="p-3 print:p-2 print:border-black print:border text-sm font-medium">{{ $req->requisition_no }}</td>
                            <td class="p-3 print:p-2 print:border-black print:border text-sm">
                                {{ $req->user->name }} <br>
                                <span class="text-xs text-zinc-500 print:text-black">{{ $req->user->department->name ?? 'N/A' }}</span>
                            </td>
                            <td class="p-3 print:p-2 print:border-black print:border text-sm text-center">{{ $req->items->sum('demanded_qty') }} {{ __('pcs') }}</td>
                            <td class="p-3 print:p-2 print:border-black print:border text-sm text-center font-bold text-green-600 print:text-black">{{ $req->items->sum('supplied_qty') }} {{ __('pcs') }}</td>
                            <td class="p-3 print:p-2 print:border-black print:border text-sm">
                                <flux:badge size="sm" color="{{ $req->status === 'distributed' ? 'green' : ($req->status === 'returned' ? 'red' : 'amber') }} print:border-none print:text-black print:bg-transparent print:p-0">
                                    {{ strtoupper($req->status) }}
                                </flux:badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-zinc-500 print:text-black print:border-black print:border">
                                {{ __('No data found for this filter.') }}
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
        /* পুরো ওয়েবসাইটের সব কিছু লুকিয়ে ফেলা হলো */
        body * {
            visibility: hidden;
        }

        /* শুধুমাত্র আমাদের টার্গেট করা এরিয়াটি দৃশ্যমান করা হলো */
        #printable-area, #printable-area * {
            visibility: visible;
        }

        /* টার্গেট করা এরিয়াটিকে পেজের একদম টপ-লেফটে নিয়ে আসা হলো */
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

        /* ফ্লাক্স কার্ডের বর্ডার এবং শ্যাডো প্রিন্টের সময় রিমুভ করা হলো */
        .flux-card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
