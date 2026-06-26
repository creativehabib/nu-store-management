<div class="p-6">
    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center text-sm text-slate-500 uppercase font-semibold tracking-wider">
        <span class="text-blue-600">Dashboard</span>
        <span class="mx-2">/</span>
        <span class="text-slate-500">Settings</span>
        <span class="mx-2">/</span>
        <span class="text-slate-800">Backups</span>
    </div>

    <div class="bg-amber-400 text-white rounded-lg p-4 mb-6 shadow-sm">
        <ul class="space-y-2 text-sm">
            <li class="flex items-start gap-2">
                <span class="mt-0.5">•</span>
                <span>This simple backup feature is ideal for websites with less than 1GB of data. A quick and easy way to create backups.</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="mt-0.5">•</span>
                <span>For larger websites with over 1GB of images or files, consider using the backup features provided by your hosting or VPS provider.</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="mt-0.5">•</span>
                <span>To back up your database, the server commands <span class="bg-white/80 text-amber-700 px-1 rounded">mysqldump</span>, <span class="bg-white/80 text-amber-700 px-1 rounded">pg_dump</span>, or <span class="bg-white/80 text-amber-700 px-1 rounded">sqlite3</span> must be available.</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="mt-0.5">•</span>
                <span>This is not a full backup. Only the database dump is included.</span>
            </li>
        </ul>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="p-4 flex items-center justify-end border-b border-slate-200 dark:border-slate-700">
            <button wire:click="generateBackup" wire:loading.attr="disabled" class="px-4 py-2 bg-blue-600 text-white rounded text-sm font-medium hover:bg-blue-500 flex items-center gap-2">
                <i class="fas fa-database" wire:loading.remove wire:target="generateBackup"></i>
                <i class="fas fa-spinner fa-spin" wire:loading wire:target="generateBackup"></i>
                <span>Generate backup</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase text-slate-500 font-semibold">
                    <th class="p-4">Name</th>
                    <th class="p-4">Description</th>
                    <th class="p-4">Size</th>
                    <th class="p-4">Created At</th>
                    <th class="p-4 text-right">Operations</th>
                </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($backups as $backup)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="p-4 text-slate-800 dark:text-slate-200 font-medium">{{ $backup['name'] }}</td>
                        <td class="p-4 text-slate-500">{{ $backup['description'] }}</td>
                        <td class="p-4 text-slate-500">{{ $backup['size'] }}</td>
                        <td class="p-4 text-slate-500">{{ $backup['created_at'] }}</td>
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="restoreBackup('{{ $backup['name'] }}')" wire:confirm="Are you sure you want to restore this backup? Current data will be overwritten." title="Restore" class="w-8 h-8 rounded bg-emerald-500 text-white hover:bg-emerald-600 transition-colors flex items-center justify-center shadow-sm">
                                    <i class="fas fa-database"></i>
                                </button>
                                <button wire:click="downloadBackup('{{ $backup['name'] }}')" title="Download" class="w-8 h-8 rounded bg-blue-600 text-white hover:bg-blue-500 transition-colors flex items-center justify-center shadow-sm">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button wire:click="$refresh" title="Refresh" class="w-8 h-8 rounded bg-slate-500 text-white hover:bg-slate-400 transition-colors flex items-center justify-center shadow-sm">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button wire:click="deleteBackup('{{ $backup['name'] }}')" wire:confirm="Delete this backup?" title="Delete" class="w-8 h-8 rounded bg-rose-500 text-white hover:bg-rose-600 transition-colors flex items-center justify-center shadow-sm">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-500">
                            No backups available.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
