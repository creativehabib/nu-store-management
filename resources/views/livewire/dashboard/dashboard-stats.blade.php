<div class="space-y-6">
    {{-- স্ট্যাটাস কার্ডসমূহ (আপনার আগের কোড) --}}
    @if($role === 'admin')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <flux:card class="bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ __('Total Users') }}</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['total_users'] }}</p>
                    </div>
                    <flux:icon.users class="w-10 h-10 text-blue-500 opacity-50" />
                </div>
            </flux:card>

            <flux:card class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-600 dark:text-amber-400">{{ __('Pending User Requests') }}</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['pending_users'] }}</p>
                    </div>
                    <flux:icon.user-plus class="w-10 h-10 text-amber-500 opacity-50" />
                </div>
            </flux:card>

            <flux:card class="bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ __('Total Inventory Products') }}</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['total_products'] }}</p>
                    </div>
                    <flux:icon.cube class="w-10 h-10 text-indigo-500 opacity-50" />
                </div>
            </flux:card>

            @if($stats['low_stock'] > 0)
                <a href="{{ route('admin.products', ['low_stock' => 1]) }}" wire:navigate class="block">
                    <flux:card class="h-full cursor-pointer bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 transition hover:border-red-400 hover:bg-red-100 dark:hover:bg-red-900/30">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ __('Low Stock (Products)') }}</p>
                                <p class="text-3xl font-bold mt-2">{{ $stats['low_stock'] }}</p>
                                <p class="mt-2 text-xs font-medium text-red-500">{{ __('Click to view all low stock products') }}</p>
                            </div>
                            <flux:icon.exclamation-triangle class="w-10 h-10 text-red-500 opacity-50" />
                        </div>
                    </flux:card>
                </a>
            @else
                <flux:card class="bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ __('Low Stock (Products)') }}</p>
                            <p class="text-3xl font-bold mt-2">{{ $stats['low_stock'] }}</p>
                        </div>
                        <flux:icon.exclamation-triangle class="w-10 h-10 text-red-500 opacity-50" />
                    </div>
                </flux:card>
            @endif
        </div>
    @endif
    @if($role === 'requisitioner')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <flux:card class="bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ __('Total Submitted Requests') }}</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['total_submitted'] }}</p>
                    </div>
                    <flux:icon.document-text class="w-10 h-10 text-indigo-500 opacity-50" />
                </div>
            </flux:card>

            <flux:card class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-600 dark:text-amber-400">{{ __('Processing (Pending)') }}</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['pending'] }}</p>
                    </div>
                    <flux:icon.clock class="w-10 h-10 text-amber-500 opacity-50" />
                </div>
            </flux:card>

            <flux:card class="bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 dark:text-green-400">{{ __('Completed (Distributed)') }}</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['distributed'] }}</p>
                    </div>
                    <flux:icon.check-circle class="w-10 h-10 text-green-500 opacity-50" />
                </div>
            </flux:card>

            <flux:card class="bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ __('Returned') }}</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['returned'] }}</p>
                    </div>
                    <flux:icon.arrow-uturn-left class="w-10 h-10 text-red-500 opacity-50" />
                </div>
            </flux:card>
        </div>
    @endif

    @if(in_array($role, ['initiator', 'assistant_director', 'deputy_director', 'director']))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @if($role === 'initiator')
                <flux:card class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-amber-600 dark:text-amber-400">{{ __('New Requisitions (In Your Queue)') }}</p>
                            <p class="text-3xl font-bold mt-2">{{ $stats['pending_action'] }}</p>
                        </div>
                        <flux:icon.clipboard-document-list class="w-10 h-10 text-amber-500 opacity-50" />
                    </div>
                </flux:card>

                <flux:card class="bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">{{ __('Ready for Print & Distribute') }}</p>
                            <p class="text-3xl font-bold mt-2">{{ $stats['ready_to_print'] }}</p>
                        </div>
                        <flux:icon.printer class="w-10 h-10 text-green-500 opacity-50" />
                    </div>
                </flux:card>
            @else
                <flux:card class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-amber-600 dark:text-amber-400">{{ __('Pending Your Approval') }}</p>
                            <p class="text-3xl font-bold mt-2">{{ $stats['pending_approval'] }}</p>
                        </div>
                        <flux:icon.clipboard-document-check class="w-10 h-10 text-amber-500 opacity-50" />
                    </div>
                </flux:card>
            @endif

            <flux:card class="bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ __('Total System Requisitions') }}</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['total_requisitions'] }}</p>
                    </div>
                    <flux:icon.document-duplicate class="w-10 h-10 text-blue-500 opacity-50" />
                </div>
            </flux:card>
        </div>
    @endif

    @if(in_array($role, ['admin', 'director']))
        {{-- চার্ট সেকশন --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
            <flux:card>
                <div class="flex items-center justify-between mb-4">
                    <flux:heading>{{ __('Requisition Trends') }}</flux:heading>

                    <div class="w-36">
                        <flux:select wire:model.live="trendFilter" size="sm">
                            <!-- 🟢 All Time অপশনটি একদম উপরে যোগ করা হলো -->
                            <flux:select.option value="all">{{ __('All Time') }}</flux:select.option>
                            <flux:select.option value="7">{{ __('7 Days') }}</flux:select.option>
                            <flux:select.option value="15">{{ __('15 Days') }}</flux:select.option>
                            <flux:select.option value="30">{{ __('30 Days') }}</flux:select.option>
                        </flux:select>
                    </div>
                </div>

                {{-- wire:ignore যুক্ত করা হয়েছে যাতে লাইভওয়্যার চার্টের ক্যানভাস মুছে না ফেলে --}}
                <div class="h-64" wire:ignore>
                    <canvas id="monthlyChart"></canvas>
                </div>
            </flux:card>

            <flux:card>
                <flux:heading>{{ __('Category-wise Inventory') }}</flux:heading>
                <div class="mt-4 h-64 flex justify-center" wire:ignore>
                    <canvas id="categoryChart"></canvas>
                </div>
            </flux:card>
        </div>
    @endif


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let monthlyChart;
        let categoryChart;

        document.addEventListener('livewire:navigated', () => { initCharts(); });

        function initCharts() {
            const monthlyCtx = document.getElementById('monthlyChart');
            if(monthlyCtx) {
                if(monthlyChart) { monthlyChart.destroy(); }
                monthlyChart = new Chart(monthlyCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($trendLabels ?? []) !!},
                        datasets: [{
                            label: '{{ __("Requisitions") }}',
                            data: {!! json_encode($trendValues ?? []) !!},
                            backgroundColor: '#6366f1',
                            borderRadius: 6
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            const categoryCtx = document.getElementById('categoryChart');
            if(categoryCtx) {
                if(categoryChart) { categoryChart.destroy(); }
                categoryChart = new Chart(categoryCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($categoryLabels ?? []) !!},
                        datasets: [{
                            data: {!! json_encode($categoryValues ?? []) !!},
                            backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
        }

        // প্রথমবার পেজ লোড হলে চার্ট ইনিশিয়ালাইজ করার জন্য
        initCharts();

        // লাইভওয়্যার থেকে ফিল্টার চেঞ্জ হওয়ার ইভেন্ট শুনবে
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('update-trend-chart', (event) => {
                let data = event[0] || event;
                if(monthlyChart) {
                    monthlyChart.data.labels = data.labels;
                    monthlyChart.data.datasets[0].data = data.values;
                    monthlyChart.update();
                }
            });
        });
    </script>
</div>
