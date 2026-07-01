<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
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
                                {{ __('Initiator Queue') }}
                            </flux:sidebar.item>
                        @endif
                        @if(in_array(auth()->user()->role, ['assistant_director', 'deputy_director', 'director']))
                            <flux:sidebar.item icon="clipboard-document-check" :href="route('workflow.approval')" :current="request()->routeIs('workflow.approval')" wire:navigate>
                                {{ __('Approval Queue') }}
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
                        <flux:sidebar.item icon="building-office" :href="route('departments.index')" :current="request()->routeIs('departments.*')" wire:navigate>
                            {{ __('Departments') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="briefcase" :href="route('designations.index')" :current="request()->routeIs('designations.*')" wire:navigate>
                            {{ __('Designations') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="chart-pie" :href="route('admin.product_summary')" :current="request()->routeIs('admin.product_summary')" wire:navigate>
                            {{ __('Products Summary') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('admin.user_approvals')" :current="request()->routeIs('admin.user_approvals')" wire:navigate>
                            {{ __('User Manage') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="language" :href="route('admin.language_settings')" :current="request()->routeIs('admin.language_settings')" wire:navigate>
                            {{ __('Language Settings') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="cog-8-tooth" :href="route('admin.mail_settings')" :current="request()->routeIs('admin.mail_settings')" wire:navigate>
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

                        <flux:navlist.item icon="circle-stack" href="{{ route('admin.backup') }}" :current="request()->routeIs('admin.backup')">
                            {{ __('Database Backup') }}
                        </flux:navlist.item>

                    </flux:sidebar.group>
                @endif


                {{--<livewire:layout.notification-bell />--}}
                <livewire:layout.language-switcher />
            </flux:sidebar.nav>

            <flux:spacer />

            {{-- SECONDARY LINKS --}}
            <flux:sidebar.nav>
                <flux:sidebar.item
                    icon="globe-alt"
                    :href="route('home')"
                    :current="request()->routeIs('home')"
                    target="_blank"
                    tooltip="{{ __('Visit Website') }}"
                >
                    {{ __('Visit Website') }}
                </flux:sidebar.item>

                <div
                    x-data="{
                    mode: localStorage.getItem('flux.appearance') || localStorage.getItem('theme') || 'system',
                    apply(selected) {
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
                            document.documentElement.classList.toggle(
                                'dark',
                                window.matchMedia('(prefers-color-scheme: dark)').matches
                            );
                        }

                        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: selected } }));
                    }
                }"
                    class="mt-2 in-data-flux-sidebar-collapsed-desktop:hidden"
                >

                    <div class="grid grid-cols-3 gap-1">
                        <button type="button" @click="apply('light')"
                                :class="mode === 'light' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600'"
                                class="flex items-center justify-center rounded-md border px-2 py-1.5 text-xs font-medium transition">
                            <flux:icon.sun class="size-4" />
                        </button>

                        <button type="button" @click="apply('dark')"
                                :class="mode === 'dark' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600'"
                                class="flex items-center justify-center rounded-md border px-2 py-1.5 text-xs font-medium transition">
                            <flux:icon.moon class="size-4" />
                        </button>

                        <button type="button" @click="apply('system')"
                                :class="mode === 'system' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600'"
                                class="flex items-center justify-center rounded-md border px-2 py-1.5 text-xs font-medium transition">
                            <flux:icon.computer-desktop class="size-4" />
                        </button>
                    </div>
                </div>

                <div
                    x-data="{
                    mode: localStorage.getItem('flux.appearance') || localStorage.getItem('theme') || 'system',
                    apply(selected) {
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
                            document.documentElement.classList.toggle(
                                'dark',
                                window.matchMedia('(prefers-color-scheme: dark)').matches
                            );
                        }

                        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: selected } }));
                    }
                }"
                    class="mt-2 hidden flex-col gap-1 in-data-flux-sidebar-collapsed-desktop:flex"
                >
                    <button type="button" @click="apply('light')" title="{{ __('Light') }}"
                            :class="mode === 'light' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600'"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border transition">
                        <flux:icon.sun class="size-4" />
                    </button>

                    <button type="button" @click="apply('dark')" title="{{ __('Dark') }}"
                            :class="mode === 'dark' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600'"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border transition">
                        <flux:icon.moon class="size-4" />
                    </button>

                    <button type="button" @click="apply('system')" title="{{ __('System') }}"
                            :class="mode === 'system' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600'"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border transition">
                        <flux:icon.computer-desktop class="size-4" />
                    </button>
                </div>
            </flux:sidebar.nav>
            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

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
