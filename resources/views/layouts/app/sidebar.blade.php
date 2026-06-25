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
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    @if(auth()->user()->role === 'admin')
                        <flux:sidebar.item icon="users" :href="route('admin.user_approvals')" :current="request()->routeIs('admin.user_approvals')" wire:navigate>
                            {{ __('User Manage') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="rectangle-group" :href="route('admin.categories')" :current="request()->routeIs('admin.categories')" wire:navigate>
                            {{ __('Categories') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="cube" :href="route('admin.products')" :current="request()->routeIs('admin.products')" wire:navigate>
                            {{ __('Products') }}
                        </flux:sidebar.item>
                    @endif

                    <!-- Requisitioner Menu -->
                    @if(auth()->user()->role === 'requisitioner')
                        <flux:sidebar.item icon="document-plus" :href="route('requisition.create')" :current="request()->routeIs('requisition.create')" wire:navigate>
                            {{ __('Submit Demand') }}
                        </flux:sidebar.item>

                        <!-- নতুন ট্র্যাকিং মেনু -->
                        <flux:sidebar.item icon="clock" :href="route('requisition.my_history')" :current="request()->routeIs('requisition.my_history')" wire:navigate>
                            {{ __('My Requisitions') }}
                        </flux:sidebar.item>
                    @endif

                    @if(in_array(auth()->user()->role, ['initiator', 'assistant_director', 'deputy_director', 'director']))

                        <!-- Initiator এর মেনু -->
                        @if(auth()->user()->role === 'initiator')
                            <flux:sidebar.item icon="clipboard-document-list" :href="route('workflow.initiator')" :current="request()->routeIs('workflow.initiator')" wire:navigate>
                                {{ __('Initiator Queue') }}
                            </flux:sidebar.item>
                        @endif

                        <!-- AD, DD এবং Director এর মেনু -->
                        @if(in_array(auth()->user()->role, ['assistant_director', 'deputy_director', 'director']))
                            <flux:sidebar.item icon="clipboard-document-check" :href="route('workflow.approval')" :current="request()->routeIs('workflow.approval')" wire:navigate>
                                {{ __('Approval Queue') }}
                                <flux:badge size="sm" color="amber">New</flux:badge>
                            </flux:sidebar.item>
                        @endif
                    @endif

                    @if(auth()->user()->role === 'initiator')
                        <flux:sidebar.item icon="plus-circle" :href="route('inventory.stock_in')" :current="request()->routeIs('inventory.stock_in')" wire:navigate>
                            {{ __('Stock In Entry') }}
                        </flux:sidebar.item>
                    @endif

                    @if(in_array(auth()->user()->role, ['admin', 'director', 'deputy_director', 'initiator']))
                        <flux:sidebar.item icon="chart-pie" :href="route('report.summary')" :current="request()->routeIs('report.summary')" wire:navigate>
                            {{ __('Reports & Export') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                @if(auth()->user()->role === 'admin')
                    <flux:sidebar.item icon="language" :href="route('admin.language_settings')" :current="request()->routeIs('admin.language_settings')" wire:navigate>
                        {{ __('Language Settings') }}
                    </flux:sidebar.item>

                    <livewire:layout.notification-bell />
                @endif
                <livewire:layout.language-switcher />
            </flux:sidebar.nav>

            <flux:spacer />

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
