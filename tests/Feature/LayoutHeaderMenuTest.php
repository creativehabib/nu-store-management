<?php

use App\Models\User;

it('renders profile details, settings shortcuts, theme modes, and logout in the dashboard header', function () {
    $user = User::factory()->create([
        'name' => 'Header User',
        'email' => 'header@example.com',
        'pf_no' => 'PF-HEADER-001',
        'mobile_no' => '01700003001',
        'role' => 'admin',
        'is_approved' => true,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Header User')
        ->assertSee('header@example.com')
        ->assertSee('PF-HEADER-001')
        ->assertSee('Profile Settings')
        ->assertSee('Security Settings')
        ->assertSee('Quick Settings')
        ->assertSee('Visit Website')
        ->assertSee('Light')
        ->assertSee('Dark')
        ->assertSee('System')
        ->assertSee('data-test="logout-button"', false);
});
