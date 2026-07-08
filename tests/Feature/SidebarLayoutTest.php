<?php

use Illuminate\Support\Facades\File;

it('allows the application sidebar to collapse on desktop and mobile', function () {
    $sidebarLayout = File::get(resource_path('views/layouts/app/sidebar.blade.php'));

    expect($sidebarLayout)
        ->toContain('<flux:sidebar sticky collapsible class=')
        ->toContain('<flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />')
        ->toContain('in-data-flux-sidebar-collapsed-desktop:hidden');
});
