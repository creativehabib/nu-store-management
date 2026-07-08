<?php

use Illuminate\Support\Facades\File;

it('allows the application sidebar to collapse on desktop and mobile', function () {
    $sidebarLayout = File::get(resource_path('views/layouts/app/sidebar.blade.php'));

    expect($sidebarLayout)
        ->toContain('<flux:sidebar sticky collapsible class=')
        ->toContain('<flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />')
        ->toContain('<flux:sidebar.group icon="document-plus" heading="{{ __(\'Requisition Management\') }}" class="grid in-data-flux-sidebar-collapsed-desktop:hidden">')
        ->toContain('<flux:sidebar.group icon="clipboard-document-list" heading="{{ __(\'Workflow\') }}" class="grid in-data-flux-sidebar-collapsed-desktop:hidden">')
        ->toContain('<flux:sidebar.group icon="chart-pie" heading="{{ __(\'Reports\') }}" class="grid in-data-flux-sidebar-collapsed-desktop:hidden">')
        ->toContain('<flux:sidebar.group icon="cog-8-tooth" heading="{{ __(\'System Administration\') }}" class="in-data-flux-sidebar-collapsed-desktop:hidden">')
        ->toContain('<flux:sidebar.nav class="hidden in-data-flux-sidebar-collapsed-desktop:grid">')
        ->toContain('x-on:mouseenter="open = true"')
        ->toContain('<flux:button type="button" variant="ghost" icon="document-plus" class="w-full justify-center"')
        ->toContain('<flux:button type="button" variant="ghost" icon="cog-8-tooth" class="w-full justify-center"')
        ->toContain('absolute left-full top-0 z-50 ms-2')
        ->toContain('in-data-flux-sidebar-collapsed-desktop:hidden');
});
