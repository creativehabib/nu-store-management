@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="setting('site_name') ?? __('Store Management')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md text-accent-foreground overflow-hidden">
            @if(setting('site_logo'))
                <img src="{{ asset('storage/' . setting('site_logo')) }}" alt="{{ setting('site_name') ?? __('Store Management') }}" class="size-full object-cover">
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="setting('site_name') ?? __('Store Management')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            @if(setting('site_logo'))
                <img src="{{ asset('storage/' . setting('site_logo')) }}" alt="{{ setting('site_name') }}" class="size-full object-cover">
            @endif
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
