<div class="grid grid-cols-1">
    <div class="col-span-1">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                <h4 class="flex items-center text-lg font-semibold text-slate-800 dark:text-slate-100">
                    <flux:icon.arrow-path class="size-5 mr-2" />
                    Cache Management
                </h4>
            </div>

            <div class="px-4 py-4">
                <p class="text-sm mb-3 text-slate-700 dark:text-slate-300">
                    Clear cache to make your site up to date. ডাটাবেস ক্যাশিং, স্ট্যাটিক ব্লকসহ সকল ক্যাশ পরিষ্কার করুন। ডেটা আপডেট করার পরও পরিবর্তন দৃশ্যমান না হলে এই কমান্ডটি চালান।
                </p>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="text-xs font-semibold uppercase tracking-wide text-slate-500 bg-slate-50 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th scope="col" class="px-3 py-2 w-16">Type</th>
                            <th scope="col" class="px-3 py-2">Description</th>
                            <th scope="col" class="px-3 py-2 text-center w-52">Action</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">

                        <tr class="bg-white dark:bg-slate-900">
                            <td class="px-3 py-3 align-middle">
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-md bg-sky-500 text-white">
                                    <flux:icon.circle-stack class="size-5" />
                                </span>
                            </td>
                            <td class="px-3 py-3 align-middle">
                                <span class="block font-semibold text-slate-800 dark:text-slate-100">Clear all CMS cache</span>
                                <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    সমস্ত অপ্টিমাইজড ক্যাশ (config, route, view, events) এবং ডিফল্ট অ্যাপ্লিকেশন ক্যাশ একসাথে মুছে ফেলে।
                                </div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 text-sky-700 border border-sky-100 px-2.5 py-0.5 text-xs dark:bg-sky-950/30 dark:text-sky-300 dark:border-sky-500/40">
                                        <span class="inline-flex h-2 w-2 rounded-full bg-sky-500 animate-ping"></span>
                                        <strong>Current Size:</strong>
                                        <span>{{ $cacheSize }}</span>
                                    </span>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center align-middle">
                                <button wire:click="clearAllCache" wire:loading.attr="disabled" wire:target="clearAllCache"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1 focus:ring-offset-slate-50 dark:focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed mt-auto cursor-pointer">
                                    <span wire:loading.remove wire:target="clearAllCache" class="inline-flex items-center">
                                        <flux:icon.trash class="size-4 mr-1" /> Clear
                                    </span>
                                    <span wire:loading.inline-flex wire:target="clearAllCache" class="items-center">
                                        <flux:icon.arrow-path class="size-4 mr-2 animate-spin" /> Clearing...
                                    </span>
                                </button>
                            </td>
                        </tr>

                        <tr class="align-middle bg-white dark:bg-slate-900">
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-md bg-amber-400 text-white">
                                    <flux:icon.code-bracket-square class="size-5" />
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <strong class="block text-slate-800 dark:text-slate-100">Refresh compiled views</strong>
                                <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    ক্যাশ হওয়া ব্লেড ভিউগুলো পরিষ্কার করে সর্বশেষ টেমপ্লেট পরিবর্তনগুলো তাৎক্ষণিকভাবে প্রতিফলিত করবে।
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <button wire:click="clearCompiledViews" wire:loading.attr="disabled" wire:target="clearCompiledViews"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1 focus:ring-offset-slate-50 dark:focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed mt-auto cursor-pointer">
                                    <span wire:loading.remove wire:target="clearCompiledViews" class="inline-flex items-center">
                                        <flux:icon.arrow-path class="size-4 mr-1" /> Refresh
                                    </span>
                                    <span wire:loading.inline-flex wire:target="clearCompiledViews" class="items-center">
                                        <flux:icon.arrow-path class="size-4 mr-2 animate-spin" /> Refreshing...
                                    </span>
                                </button>
                            </td>
                        </tr>

                        <tr class="align-middle bg-white dark:bg-slate-900">
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-md bg-emerald-500 text-white">
                                    <flux:icon.cog-8-tooth class="size-5" />
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <strong class="block text-slate-800 dark:text-slate-100">Clear config cache</strong>
                                <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    প্রোডাকশন পরিবেশে কনফিগ ফাইল পরিবর্তনের পর কনফিগ ক্যাশ রিফ্রেশ করতে এই অপশনটি ব্যবহার করুন।
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <button wire:click="clearConfigCache" wire:loading.attr="disabled" wire:target="clearConfigCache"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 focus:ring-offset-slate-50 dark:focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed mt-auto cursor-pointer">
                                    <span wire:loading.remove wire:target="clearConfigCache" class="inline-flex items-center">
                                        <flux:icon.trash class="size-4 mr-1" /> Clear
                                    </span>
                                    <span wire:loading.inline-flex wire:target="clearConfigCache" class="items-center">
                                        <flux:icon.arrow-path class="size-4 mr-2 animate-spin" /> Clearing...
                                    </span>
                                </button>
                            </td>
                        </tr>

                        <tr class="align-middle bg-white dark:bg-slate-900">
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-md bg-rose-500 text-white">
                                    <flux:icon.map class="size-5" />
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <strong class="block text-slate-800 dark:text-slate-100">Clear route cache</strong>
                                <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    রাউটিং সংক্রান্ত পরিবর্তন কার্যকর করতে রুট ক্যাশ পরিষ্কার করুন।
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <button wire:click="clearRouteCache" wire:loading.attr="disabled" wire:target="clearRouteCache"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1 focus:ring-offset-slate-50 dark:focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed mt-auto cursor-pointer">
                                    <span wire:loading.remove wire:target="clearRouteCache" class="inline-flex items-center">
                                        <flux:icon.trash class="size-4 mr-1" /> Clear
                                    </span>
                                    <span wire:loading.inline-flex wire:target="clearRouteCache" class="items-center">
                                        <flux:icon.arrow-path class="size-4 mr-2 animate-spin" /> Clearing...
                                    </span>
                                </button>
                            </td>
                        </tr>

                        <tr class="align-middle bg-white dark:bg-slate-900">
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-md bg-sky-600 text-white">
                                    <flux:icon.document-text class="size-5" />
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <strong class="block text-slate-800 dark:text-slate-100">Clear log</strong>
                                <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    storage/logs ডিরেক্টরির সকল লগ ফাইল মুছে ফেলে ডিস্ক স্পেস খালি করুন এবং নতুন লগ সংগ্রহ করুন।
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <button wire:click="clearLogFiles" wire:loading.attr="disabled" wire:target="clearLogFiles"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1 focus:ring-offset-slate-50 dark:focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed mt-auto cursor-pointer">
                                    <span wire:loading.remove wire:target="clearLogFiles" class="inline-flex items-center">
                                        <flux:icon.trash class="size-4 mr-1" /> Clear
                                    </span>
                                    <span wire:loading.inline-flex wire:target="clearLogFiles" class="items-center">
                                        <flux:icon.arrow-path class="size-4 mr-2 animate-spin" /> Clearing...
                                    </span>
                                </button>
                            </td>
                        </tr>

                        <tr class="align-middle bg-white dark:bg-slate-900">
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-md bg-slate-800 text-white">
                                    <flux:icon.sparkles class="size-5" />
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <strong class="block text-slate-800 dark:text-slate-100">Clear optimization cache</strong>
                                <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    Remove optimized cache files so new configuration or route changes take effect. কনফিগ বা রুট পরিবর্তনের পর দ্রুত আপডেট দেখতে অপ্টিমাইজ ক্যাশ পরিষ্কার করুন।
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <button wire:click="clearOptimizationCaches" wire:loading.attr="disabled" wire:target="clearOptimizationCaches"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium border border-slate-800 text-slate-800 hover:bg-slate-800 hover:text-white dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-200 dark:hover:text-slate-900 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-700 focus:ring-offset-1 focus:ring-offset-slate-50 dark:focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed mt-auto cursor-pointer">
                                    <span wire:loading.remove wire:target="clearOptimizationCaches" class="inline-flex items-center">
                                        <flux:icon.trash class="size-4 mr-1" /> Clear
                                    </span>
                                    <span wire:loading.inline-flex wire:target="clearOptimizationCaches" class="items-center">
                                        <flux:icon.arrow-path class="size-4 mr-2 animate-spin" /> Clearing...
                                    </span>
                                </button>
                            </td>
                        </tr>

                        <tr class="bg-white dark:bg-slate-900">
                            <td class="px-3 py-3 align-middle">
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-md bg-slate-500 text-white">
                                    <flux:icon.square-3-stack-3d class="size-5" />
                                </span>
                            </td>
                            <td class="px-3 py-3 align-middle">
                                <span class="block font-semibold text-slate-800 dark:text-slate-100">Cache views</span>
                                <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    Precompile Blade templates into PHP for quicker rendering. ব্লেড ভিউগুলো আগে থেকেই কম্পাইল করে রেন্ডারিং গতি বাড়ায়।
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center align-middle">
                                <button wire:click="cacheViews" wire:loading.attr="disabled" wire:target="cacheViews"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium border border-sky-500 text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-950/30 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1 focus:ring-offset-slate-50 dark:focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed mt-auto cursor-pointer">
                                    <span wire:loading.remove wire:target="cacheViews" class="inline-flex items-center">
                                        <flux:icon.eye class="size-4 mr-1" /> Cache
                                    </span>
                                    <span wire:loading.inline-flex wire:target="cacheViews" class="items-center">
                                        <flux:icon.arrow-path class="size-4 mr-2 animate-spin" /> Caching...
                                    </span>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                <div class="flex items-start gap-2 text-xs text-slate-700 dark:text-slate-300">
                    <flux:icon.information-circle class="size-4 mt-0.5 text-slate-400" />
                    <small>Clear cache after making changes to your site to ensure they appear correctly.</small>
                </div>
            </div>
        </div>
    </div>
</div>
