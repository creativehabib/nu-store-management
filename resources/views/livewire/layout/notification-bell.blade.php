<div>
    <flux:dropdown position="bottom" align="end">
        <flux:button
            variant="ghost"
            icon="bell"
            class="relative hover:bg-zinc-100 dark:hover:bg-zinc-800"
            aria-label="{{ __('Notifications') }}"
        >
            @if($unreadCount > 0)
                <span class="absolute -right-0.5 top-0 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white ring-2 ring-white dark:ring-zinc-900">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </flux:button>

        <flux:menu class="w-96 max-h-[32rem] overflow-hidden p-0">
            <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/80">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Notifications') }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ trans_choice(':count unread notification|:count unread notifications', $unreadCount, ['count' => $unreadCount]) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($readCount > 0)
                            <button
                                type="button"
                                wire:click="clearRead"
                                class="text-xs font-medium text-zinc-500 transition hover:text-red-600 dark:text-zinc-400 dark:hover:text-red-400"
                            >
                                {{ __('Clear read') }}
                            </button>
                        @endif

                        @if($unreadCount > 0)
                            <button
                                type="button"
                                wire:click="markAllAsRead"
                                class="text-xs font-medium text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                            >
                                {{ __('Mark all read') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="max-h-96 overflow-y-auto">
                @forelse($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $title = $data['requisition_no'] ?? $data['title'] ?? __('System Notification');
                        $message = $data['message'] ?? __('You have a new notification.');
                        $isUnread = is_null($notification->read_at);
                    @endphp

                    <div class="group border-b border-zinc-100 last:border-b-0 dark:border-zinc-800 {{ $isUnread ? 'bg-indigo-50/70 dark:bg-indigo-950/20' : 'bg-white dark:bg-zinc-900' }}">
                        <button
                            type="button"
                            wire:click="openNotification('{{ $notification->id }}')"
                            class="flex w-full gap-3 px-4 py-3 text-left transition hover:bg-zinc-50 dark:hover:bg-zinc-800/80"
                        >
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $isUnread ? 'bg-indigo-500' : 'bg-zinc-300 dark:bg-zinc-600' }}"></span>

                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-3">
                                    <span class="truncate text-sm font-semibold {{ $isUnread ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-700 dark:text-zinc-200' }}">
                                        {{ $title }}
                                    </span>
                                    <span class="shrink-0 text-[11px] text-zinc-500 dark:text-zinc-400">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </span>
                                <span class="mt-1 line-clamp-2 text-xs leading-5 text-zinc-600 dark:text-zinc-300">
                                    {{ $message }}
                                </span>
                            </span>
                        </button>

                        <div class="flex items-center justify-end gap-3 px-4 pb-3 text-xs">
                            @if($isUnread)
                                <button
                                    type="button"
                                    wire:click="markAsRead('{{ $notification->id }}')"
                                    class="font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                                >
                                    {{ __('Mark read') }}
                                </button>
                            @endif
                            <button
                                type="button"
                                wire:click="deleteNotification('{{ $notification->id }}')"
                                class="font-medium text-zinc-500 hover:text-red-600 dark:text-zinc-400 dark:hover:text-red-400"
                            >
                                {{ __('Delete') }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            <flux:icon.bell class="size-6" />
                        </div>
                        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('No notifications yet') }}</p>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Important requisition and system updates will appear here.') }}</p>
                    </div>
                @endforelse
            </div>
        </flux:menu>
    </flux:dropdown>
</div>
