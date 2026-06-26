<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 print-container">
    {{-- Header & Action Buttons --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 dark:border-slate-700 pb-4 print:hidden">
        <div>
            <flux:heading size="xl">{{ __('Category-wise Product Summary') }}</flux:heading>
            <flux:subheading>{{ __('Generate summary reports of all product stocks and demands.') }}</flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button wire:click="exportToCSV" variant="outline" icon="document-arrow-down">
                {{ __('Export CSV') }}
            </flux:button>
            <flux:button onclick="window.print()" variant="primary" icon="printer">
                {{ __('Print Report') }}
            </flux:button>
        </div>
    </div>

    {{-- Filters Box --}}
    <flux:card class="print:hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <flux:input type="date" wire:model.live="startDate" label="{{ __('Start Date') }}" />
            <flux:input type="date" wire:model.live="endDate" label="{{ __('End Date') }}" />

            @if(count($categories) > 0)
                <flux:select wire:model.live="categoryId" label="{{ __('Category') }}" placeholder="All Categories">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </flux:select>
            @endif
        </div>
    </flux:card>

    {{-- Top Demand Cards (অ্যাডভান্সড ফিচার) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 print:hidden">
        <flux:card class="flex items-center gap-4">
            <div class="p-3 rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                <flux:icon.bolt class="w-6 h-6" />
            </div>
            <div>
                <flux:subheading>{{ __('Total Requisition Items') }}</flux:subheading>
                <flux:heading size="xl" class="font-bold">{{ $stats['total_demanded'] }}</flux:heading>
            </div>
        </flux:card>

        <flux:card class="flex items-center gap-4">
            <div class="p-3 rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400">
                <flux:icon.exclamation-triangle class="w-6 h-6" />
            </div>
            <div>
                <flux:subheading>{{ __('Items in Shortage') }}</flux:subheading>
                <flux:heading size="xl" class="font-bold text-rose-600 dark:text-rose-400">
                    {{ $stats['shortage_count'] }}
                </flux:heading>
            </div>
        </flux:card>

        <flux:card class="flex items-center gap-4">
            <div class="p-3 rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                <flux:icon.folder class="w-6 h-6" />
            </div>
            <div>
                <flux:subheading>{{ __('Active Categories') }}</flux:subheading>
                <flux:heading size="xl" class="font-bold">{{ $stats['active_categories'] }}</flux:heading>
            </div>
        </flux:card>
    </div>

    {{-- Printable Report Area --}}
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden printable-area">

        <div class="hidden print:block text-center mb-6 pt-4">
            <h2 class="text-2xl font-bold uppercase text-black">Product Inventory & Demand Summary</h2>
            <p class="text-slate-800 mt-1">
                Period: {{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}
            </p>
            <hr class="mt-4 border-slate-800">
        </div>

        <div class="overflow-x-auto mb-8"> {{-- ফুটারের জন্য নিচে একটু মার্জিন দেওয়া হলো --}}
            <table class="w-full text-left border-collapse print:border-2">
                <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase text-slate-500 font-semibold print:bg-transparent print:border-slate-800 print:text-black">
                    <th class="p-4 w-16">#</th>
                    <th class="p-4">{{ __('Product Name') }}</th>
                    <th class="p-4 text-center">{{ __('Current Stock') }}</th>
                    <th class="p-4 text-center">{{ __('Total Demanded') }}</th>
                    <th class="p-4 text-right">{{ __('Remarks') }}</th>
                </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200 dark:divide-slate-700 print:divide-slate-400">
                @forelse($summaryGroups as $categoryName => $products)

                    <tr class="bg-slate-100/50 dark:bg-slate-800/80 print:bg-slate-100">
                        <td colspan="5" class="p-3 px-4 font-bold text-slate-700 dark:text-slate-300 print:text-black">
                            <flux:icon.folder class="w-4 h-4 inline-block mr-1 text-slate-400" />
                            {{ $categoryName }}
                        </td>
                    </tr>

                    @foreach($products as $index => $product)
                        @php
                            // শর্টেজ আছে কি না চেক করা
                            $isShortage = ($product->total_qty ?? 0) > ($product->stock ?? 0);
                        @endphp

                        {{-- শর্টেজ থাকলে রো-এর কালার হালকা লাল হবে --}}
                        <tr class="{{ $isShortage ? 'bg-rose-50 dark:bg-rose-950/20' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50' }} transition-colors">
                            <td class="p-4 text-slate-500">{{ $loop->iteration }}</td>
                            <td class="p-4 font-medium text-slate-800 dark:text-slate-200 print:text-black">
                                {{ $product->name_bn ?? $product->name ?? 'Unknown Product' }}

                                {{-- শর্টেজ ট্যাগ --}}
                                @if($isShortage)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 print:border print:border-rose-500 print:bg-transparent">
                                        {{ __('Shortage: :qty', ['qty' => $product->total_qty - $product->stock]) }}
                                    </span>
                                @endif
                            </td>

                            <td class="p-4 text-center font-bold text-emerald-600 dark:text-emerald-400 print:text-black">
                                {{ $product->stock ?? 0 }}
                            </td>

                            <td class="p-4 text-center font-bold {{ ($product->total_qty ?? 0) > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }} print:text-black">
                                {{ $product->total_qty ?? 0 }}
                            </td>

                            <td class="p-4 border-l border-transparent print:border-slate-300 text-right text-xs text-slate-400 italic">
                                @if(($product->total_qty ?? 0) == 0)
                                    <span class="print:hidden">{{ __('No Demand') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-500">
                            {{ __('No product data available.') }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Print Footer (শুধুমাত্র প্রিন্টে দেখাবে) --}}
        <div class="hidden print:flex justify-between items-end px-4 pb-4 text-sm text-black border-t border-slate-800 pt-4">
            <div>
                <p><strong>Printed By:</strong> {{ auth()->user()->name ?? 'System Admin' }}</p>
                <p class="mt-1 font-medium text-xs">National University, Bangladesh</p>
            </div>
            <div class="text-right">
                <p><strong>Print Date & Time:</strong> {{ now()->format('d M, Y - h:i A') }}</p>
                <p class="mt-1 italic text-slate-600 text-xs">Generated via Automated System</p>
            </div>
        </div>

    </div>

    {{-- কাস্টম প্রিন্ট স্টাইল --}}
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .print\:hidden {
                display: none !important;
            }
            .print\:block, .print\:flex {
                display: block !important;
                visibility: visible;
            }
            .print\:flex {
                display: flex !important;
            }
            .printable-area, .printable-area * {
                visibility: visible;
            }
            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</div>
