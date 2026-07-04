@php($workflowQueueCounts ??= ['initiator' => 0, 'approval' => 0])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body
        x-data="{
            settingsOpen: false,
            mode: localStorage.getItem('flux.appearance') || localStorage.getItem('theme') || 'system',
            applyAppearance(selected) {
                this.mode = selected;

                if (this.$flux) {
                    this.$flux.appearance = selected;
                }

                if (selected === 'dark') {
                    localStorage.setItem('theme', 'dark');
                    localStorage.setItem('flux.appearance', 'dark');
                    document.documentElement.classList.add('dark');
                } else if (selected === 'light') {
                    localStorage.setItem('theme', 'light');
                    localStorage.setItem('flux.appearance', 'light');
                    document.documentElement.classList.remove('dark');
                } else {
                    localStorage.removeItem('theme');
                    localStorage.setItem('flux.appearance', 'system');
                    document.documentElement.classList.toggle('dark', window.matchMedia('(prefers-color-scheme: dark)').matches);
                }

                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: selected } }));
            }
        }"
        x-on:keydown.escape.window="settingsOpen = false"
        class="min-h-screen bg-white dark:bg-zinc-800 flex"
    >

        <flux:header sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="inbox" badge="12" href="#">Inbox</flux:navbar.item>
                <flux:separator vertical variant="subtle" class="my-2"/>
                <flux:dropdown class="max-lg:hidden">
                    <flux:navbar.item icon:trailing="chevron-down">Favorites</flux:navbar.item>
                    <flux:navmenu>
                        <flux:navmenu.item href="#">Marketing site</flux:navmenu.item>
                        <flux:navmenu.item href="#">Android app</flux:navmenu.item>
                        <flux:navmenu.item href="#">Brand guidelines</flux:navmenu.item>
                    </flux:navmenu>
                </flux:dropdown>
            </flux:navbar>
            <flux:spacer />
            <flux:navbar class="me-4">
                <flux:navbar.item icon="magnifying-glass" href="#" label="Search" />
                <livewire:layout.notification-bell wire:key="header-notification-bell" />
                <livewire:layout.language-switcher wire:key="header-language-switcher" />
                <flux:navbar.item icon="globe-alt" :href="route('home')" target="_blank" label="{{ __('Visit Website') }}" />
                <flux:button type="button" variant="ghost" icon="cog-6-tooth" class="max-lg:hidden" x-on:click="settingsOpen = true" aria-label="{{ __('Open settings') }}" />
            </flux:navbar>

            <flux:dropdown align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    :avatar="filled(auth()->user()->picture) ? asset('storage/' . auth()->user()->picture) : null"
                />

                <flux:menu class="min-w-72">
                    <div class="px-3 py-3">
                        <div class="flex items-start gap-3">
                            <div class="min-w-0 flex-1">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text size="sm" class="truncate">{{ auth()->user()->email }}</flux:text>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <flux:badge color="blue" size="sm">{{ __('Role:') }} {{ __(ucwords(str_replace('_', ' ', auth()->user()->role))) }}</flux:badge>
                                    <flux:badge color="zinc" size="sm">{{ __('PF No:') }} {{ auth()->user()->pf_no ?? 'N/A' }}</flux:badge>
                                </div>
                            </div>
                        </div>
                    </div>

                    <flux:menu.separator />

                    <flux:menu.item :href="route('profile.edit')" icon="user" wire:navigate>
                        {{ __('Profile Settings') }}
                    </flux:menu.item>
                    <flux:menu.item :href="route('security.edit')" icon="shield-check" wire:navigate>
                        {{ __('Security') }}
                    </flux:menu.item>
                    <flux:menu.item :href="route('appearance.edit')" icon="paint-brush" wire:navigate>
                        {{ __('Appearance') }}
                    </flux:menu.item>
                    <flux:menu.item icon="cog-6-tooth" x-on:click="settingsOpen = true">
                        {{ __('Quick Settings') }}
                    </flux:menu.item>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button
                            type="submit"
                            class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30"
                            data-test="logout-button"
                        >
                            <flux:icon.arrow-right-start-on-rectangle class="size-4" />
                            <span>{{ __('Log out') }}</span>
                        </button>
                    </form>
                </flux:menu>
            </flux:dropdown>

        </flux:header>

        <div x-cloak x-show="settingsOpen" class="fixed inset-0 z-50" aria-modal="true" role="dialog">
            <div class="absolute inset-0 bg-black/40" x-on:click="settingsOpen = false"></div>

            <aside
                x-show="settingsOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-full opacity-0"
                class="absolute right-0 top-0 flex h-full w-full max-w-md flex-col border-l border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div class="flex items-start justify-between border-b border-zinc-200 p-5 dark:border-zinc-700">
                    <div>
                        <flux:heading size="lg">{{ __('Quick Settings') }}</flux:heading>
                        <flux:subheading>{{ __('Important account and system shortcuts') }}</flux:subheading>
                    </div>
                    <flux:button type="button" variant="ghost" icon="x-mark" x-on:click="settingsOpen = false" aria-label="{{ __('Close settings') }}" />
                </div>

                <div class="flex-1 space-y-6 overflow-y-auto p-5">
                    <flux:card>
                        <div class="flex items-start gap-3">
                            <div class="min-w-0 flex-1">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text size="sm" class="truncate">{{ auth()->user()->email }}</flux:text>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <flux:badge color="blue">{{ __('Role:') }} {{ __(ucwords(str_replace('_', ' ', auth()->user()->role))) }}</flux:badge>
                                    <flux:badge color="zinc">{{ __('PF No:') }} {{ auth()->user()->pf_no ?? 'N/A' }}</flux:badge>
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    <div class="space-y-2">
                        <flux:heading size="sm">{{ __('Account Settings') }}</flux:heading>
                        <div class="grid gap-2">
                            <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 text-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                <flux:icon.user class="size-5 text-zinc-500" />
                                <span>{{ __('Profile Settings') }}</span>
                            </a>
                            <a href="{{ route('security.edit') }}" wire:navigate class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 text-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                <flux:icon.shield-check class="size-5 text-zinc-500" />
                                <span>{{ __('Security Settings') }}</span>
                            </a>
                            <a href="{{ route('appearance.edit') }}" wire:navigate class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 text-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                <flux:icon.paint-brush class="size-5 text-zinc-500" />
                                <span>{{ __('Appearance Settings') }}</span>
                            </a>
                        </div>
                    </div>

                    @if(auth()->user()->role === 'admin')
                        <div class="space-y-2">
                            <flux:heading size="sm">{{ __('System Settings') }}</flux:heading>
                            <div class="grid gap-2">
                                <a href="{{ route('admin.general_settings') }}" wire:navigate class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 text-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                    <flux:icon.cog-6-tooth class="size-5 text-zinc-500" />
                                    <span>{{ __('General Settings') }}</span>
                                </a>
                                <a href="{{ route('admin.mail_settings') }}" wire:navigate class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 text-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                    <flux:icon.envelope class="size-5 text-zinc-500" />
                                    <span>{{ __('Mail Settings') }}</span>
                                </a>
                                <a href="{{ route('admin.language_settings') }}" wire:navigate class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 text-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                    <flux:icon.language class="size-5 text-zinc-500" />
                                    <span>{{ __('Language Settings') }}</span>
                                </a>
                                <a href="{{ route('admin.cache_management') }}" wire:navigate class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 text-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                    <flux:icon.archive-box class="size-5 text-zinc-500" />
                                    <span>{{ __('Cache Management') }}</span>
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-2">
                        <flux:heading size="sm">{{ __('Theme Mode') }}</flux:heading>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" x-on:click="applyAppearance('light')" :class="mode === 'light' ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-zinc-200 bg-white text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200'" class="flex flex-col items-center gap-2 rounded-lg border p-3 text-sm transition">
                                <flux:icon.sun class="size-5" />
                                <span>{{ __('Light') }}</span>
                            </button>
                            <button type="button" x-on:click="applyAppearance('dark')" :class="mode === 'dark' ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-zinc-200 bg-white text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200'" class="flex flex-col items-center gap-2 rounded-lg border p-3 text-sm transition">
                                <flux:icon.moon class="size-5" />
                                <span>{{ __('Dark') }}</span>
                            </button>
                            <button type="button" x-on:click="applyAppearance('system')" :class="mode === 'system' ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-zinc-200 bg-white text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200'" class="flex flex-col items-center gap-2 rounded-lg border p-3 text-sm transition">
                                <flux:icon.computer-desktop class="size-5" />
                                <span>{{ __('System') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>

                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                {{-- রিকুইজিশন ম্যানেজমেন্ট (গ্রুপ করা) --}}
                <flux:sidebar.group heading="{{ __('Requisition Management') }}" class="grid">
                    <flux:sidebar.item icon="document-plus" :href="route('requisition.create')" :current="request()->routeIs('requisition.create')" wire:navigate>
                        {{ __('Submit Demand') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="clock" :href="route('requisition.my_history')" :current="request()->routeIs('requisition.my_history')" wire:navigate>
                        {{ __('My Requisitions') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- ওয়ার্কফ্লো / অ্যাপ্রুভাল কিউ --}}
                @if(in_array(auth()->user()->role, ['initiator', 'assistant_director', 'deputy_director', 'director']))
                    <flux:sidebar.group heading="{{ __('Workflow') }}" class="grid">
                        @if(auth()->user()->role === 'initiator')
                            <flux:sidebar.item icon="clipboard-document-list" :href="route('workflow.initiator')" :current="request()->routeIs('workflow.initiator')" wire:navigate>
                                <span class="flex w-full items-center justify-between gap-2">
                                    <span>{{ __('Initiator Queue') }}</span>
                                    <livewire:layout.workflow-queue-badge type="initiator" wire:key="sidebar-initiator-queue-badge" />
                                </span>
                            </flux:sidebar.item>
                        @endif
                        @if(in_array(auth()->user()->role, ['assistant_director', 'deputy_director', 'director']))
                            <flux:sidebar.item icon="clipboard-document-check" :href="route('workflow.approval')" :current="request()->routeIs('workflow.approval')" wire:navigate>
                                <span class="flex w-full items-center justify-between gap-2">
                                    <span>{{ __('Approval Queue') }}</span>
                                    <livewire:layout.workflow-queue-badge type="approval" wire:key="sidebar-approval-queue-badge" />
                                </span>
                            </flux:sidebar.item>
                        @endif
                        @if(auth()->user()->role === 'initiator')
                            <flux:sidebar.item icon="plus-circle" :href="route('inventory.stock_in')" :current="request()->routeIs('inventory.stock_in')" wire:navigate>
                                {{ __('Stock In Entry') }}
                            </flux:sidebar.item>
                        @endif
                    </flux:sidebar.group>
                @endif

                {{-- রিপোর্টস --}}
                @if(in_array(auth()->user()->role, ['admin','super_admin', 'director', 'assistant_director', 'deputy_director', 'initiator']))
                    <flux:sidebar.group heading="{{ __('Reports') }}" class="grid">
                        <flux:sidebar.item icon="chart-pie" :href="route('report.summary')" :current="request()->routeIs('report.summary')" wire:navigate>
                            {{ __('Reports & Export') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif

                @if(auth()->user()->role === 'admin')
                    <flux:sidebar.group heading="{{ __('System Administration') }}">
                        <flux:sidebar.item icon="rectangle-group" :href="route('admin.categories')" :current="request()->routeIs('admin.categories')" wire:navigate>
                            {{ __('Categories') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="cube" :href="route('admin.products')" :current="request()->routeIs('admin.products')" wire:navigate>
                            {{ __('Products') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.purposes')" :current="request()->routeIs('admin.purposes')" wire:navigate>
                            {{ __('Purposes') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-office" :href="route('departments.index')" :current="request()->routeIs('departments.*')" wire:navigate>
                            {{ __('Departments') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="briefcase" :href="route('designations.index')" :current="request()->routeIs('designations.*')" wire:navigate>
                            {{ __('Designations') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="plus-circle" :href="route('inventory.stock_in')" :current="request()->routeIs('inventory.stock_in')" wire:navigate>
                            {{ __('Stock In Entry') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="chart-pie" :href="route('admin.product_summary')" :current="request()->routeIs('admin.product_summary')" wire:navigate>
                            {{ __('Products Summary') }}
                        </flux:sidebar.item>

                        {{-- Settings & Manage সাব-মেনু --}}
                        <flux:sidebar.group
                            expandable
                            icon="cog-8-tooth"
                            :heading="__('Settings & Manage')"
                            class="grid"
                            :expanded="request()->routeIs('admin.user_approvals', 'admin.audit_logs', 'admin.language_settings', 'admin.mail_settings', 'admin.general_settings', 'admin.system_info', 'admin.cache_management', 'admin.backup')"
                        >

                            <flux:sidebar.item icon="users" :href="route('admin.user_approvals')" :current="request()->routeIs('admin.user_approvals')" wire:navigate>
                                {{ __('User Manage') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="shield-check" :href="route('admin.audit_logs')" :current="request()->routeIs('admin.audit_logs')" wire:navigate>
                                {{ __('Audit Trail') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="language" :href="route('admin.language_settings')" :current="request()->routeIs('admin.language_settings')" wire:navigate>
                                {{ __('Language Settings') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="envelope" :href="route('admin.mail_settings')" :current="request()->routeIs('admin.mail_settings')" wire:navigate>
                                {{ __('Mail Settings') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="cog-6-tooth" :href="route('admin.general_settings')" :current="request()->routeIs('admin.general_settings')" wire:navigate>
                                {{ __('General Settings') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="server-stack" :href="route('admin.system_info')" :current="request()->routeIs('admin.system_info')" wire:navigate>
                                {{ __('System Info') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="archive-box" :href="route('admin.cache_management')" :current="request()->routeIs('admin.cache_management')" wire:navigate>
                                {{ __('Cache Management') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="circle-stack" :href="route('admin.backup')" :current="request()->routeIs('admin.backup')" wire:navigate>
                                {{ __('Database Backup') }}
                            </flux:sidebar.item>

                        </flux:sidebar.group>
                        {{-- সাব-মেনু শেষ --}}

                    </flux:sidebar.group>
                @endif


            </flux:sidebar.nav>

            <flux:spacer />
            <flux:sidebar.nav>
                <flux:text class="text-center" size="sm">Developed By <br></flux:text>
                <flux:badge color="green" size="sm" class="text-center">Habibur Rahaman, PF No-2125</flux:badge>
            </flux:sidebar.nav>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="hidden">
            <flux:sidebar.toggle class="hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
