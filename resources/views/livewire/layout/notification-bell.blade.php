<div>
    <flux:dropdown position="bottom" align="end">

        <flux:button variant="ghost" icon="bell" class="relative hover:bg-zinc-100 dark:hover:bg-zinc-800">
            @if($notifications->count() > 0)
                <span class="absolute top-1 right-2 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 text-[10px] text-white flex items-center justify-center">
                        {{ $notifications->count() }}
                    </span>
                </span>
            @endif
        </flux:button>

        <flux:menu class="w-80 max-h-96 overflow-y-auto">
            <div class="px-4 py-2 border-b dark:border-zinc-700 font-bold flex justify-between items-center bg-zinc-50 dark:bg-zinc-800">
                <span>নোটিফিকেশন</span>
            </div>

            @forelse($notifications as $notification)
                <flux:menu.item wire:click="markAsRead('{{ $notification->id }}', '{{ $notification->data['url'] }}')" class="cursor-pointer border-b dark:border-zinc-700 last:border-b-0 hover:bg-zinc-50 dark:hover:bg-zinc-800">
                    <div class="text-sm py-1">
                        <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $notification->data['requisition_no'] }}</span><br>
                        <span class="text-zinc-700 dark:text-zinc-300">{{ $notification->data['message'] }}</span><br>
                        <span class="text-xs text-zinc-500 mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                </flux:menu.item>
            @empty
                <div class="px-4 py-6 text-center text-zinc-500 text-sm">
                    কোনো নতুন নোটিফিকেশন নেই
                </div>
            @endforelse
        </flux:menu>

    </flux:dropdown>
</div>
