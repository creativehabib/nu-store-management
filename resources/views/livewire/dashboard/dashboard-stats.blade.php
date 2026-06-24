<div class="space-y-6">
    <div class="border-b pb-4 mb-4">
        <flux:heading size="xl">স্বাগতম, {{ auth()->user()->name }}!</flux:heading>
        <p class="text-zinc-500 text-sm mt-1">আপনার ড্যাশবোর্ডের সামারি নিচে দেওয়া হলো:</p>
    </div>

    <!-- Admin Dashboard -->
    @if($role === 'admin')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <flux:card class="bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">মোট ইউজার</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['total_users'] }}</p>
                    </div>
                    <flux:icon.users class="w-10 h-10 text-blue-500 opacity-50" />
                </div>
            </flux:card>

            <flux:card class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-600 dark:text-amber-400">অপেক্ষমান ইউজার রিকোয়েস্ট</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['pending_users'] }}</p>
                    </div>
                    <flux:icon.user-plus class="w-10 h-10 text-amber-500 opacity-50" />
                </div>
            </flux:card>

            <flux:card class="bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">মোট ইনভেন্টরি প্রোডাক্ট</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['total_products'] }}</p>
                    </div>
                    <flux:icon.cube class="w-10 h-10 text-indigo-500 opacity-50" />
                </div>
            </flux:card>

            <flux:card class="bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">Low Stock (প্রোডাক্ট)</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['low_stock'] }}</p>
                    </div>
                    <flux:icon.exclamation-triangle class="w-10 h-10 text-red-500 opacity-50" />
                </div>
            </flux:card>
        </div>
    @endif

    <!-- Requisitioner Dashboard -->
    @if($role === 'requisitioner')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <flux:card class="bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">মোট জমা দেওয়া চাহিদা</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['total_submitted'] }}</p>
                    </div>
                    <flux:icon.document-text class="w-10 h-10 text-indigo-500 opacity-50" />
                </div>
            </flux:card>

            <flux:card class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-600 dark:text-amber-400">প্রক্রিয়াধীন (Pending)</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['pending'] }}</p>
                    </div>
                    <flux:icon.clock class="w-10 h-10 text-amber-500 opacity-50" />
                </div>
            </flux:card>

            <flux:card class="bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 dark:text-green-400">প্রাপ্তি সম্পন্ন (Distributed)</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['distributed'] }}</p>
                    </div>
                    <flux:icon.check-circle class="w-10 h-10 text-green-500 opacity-50" />
                </div>
            </flux:card>

            <flux:card class="bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">ফেরত এসেছে (Returned)</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['returned'] }}</p>
                    </div>
                    <flux:icon.arrow-uturn-left class="w-10 h-10 text-red-500 opacity-50" />
                </div>
            </flux:card>
        </div>
    @endif

    <!-- Workflow Approvers Dashboard -->
    @if(in_array($role, ['initiator', 'assistant_director', 'deputy_director', 'director']))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            @if($role === 'initiator')
                <flux:card class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-amber-600 dark:text-amber-400">নতুন রিকুইজিশন (আপনার কিউতে)</p>
                            <p class="text-3xl font-bold mt-2">{{ $stats['pending_action'] }}</p>
                        </div>
                        <flux:icon.clipboard-document-list class="w-10 h-10 text-amber-500 opacity-50" />
                    </div>
                </flux:card>

                <flux:card class="bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">প্রিন্ট ও বিতরণের জন্য প্রস্তুত</p>
                            <p class="text-3xl font-bold mt-2">{{ $stats['ready_to_print'] }}</p>
                        </div>
                        <flux:icon.printer class="w-10 h-10 text-green-500 opacity-50" />
                    </div>
                </flux:card>
            @else
                <flux:card class="bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-amber-600 dark:text-amber-400">আপনার অনুমোদনের অপেক্ষায়</p>
                            <p class="text-3xl font-bold mt-2">{{ $stats['pending_approval'] }}</p>
                        </div>
                        <flux:icon.clipboard-document-check class="w-10 h-10 text-amber-500 opacity-50" />
                    </div>
                </flux:card>
            @endif

            <flux:card class="bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">সিস্টেমের মোট রিকুইজিশন</p>
                        <p class="text-3xl font-bold mt-2">{{ $stats['total_requisitions'] }}</p>
                    </div>
                    <flux:icon.document-duplicate class="w-10 h-10 text-blue-500 opacity-50" />
                </div>
            </flux:card>
        </div>
    @endif
</div>
