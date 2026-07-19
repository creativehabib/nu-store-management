<?php

use App\Livewire\Admin\GeneralSettings;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function generalSettingsAdmin(): User
{
    return User::factory()->create([
        'role' => 'admin',
        'pf_no' => 'ADMIN-GENERAL-001',
        'mobile_no' => '01700004001',
        'is_approved' => true,
    ]);
}

it('shows branding preview controls on the general settings page', function () {
    $this->actingAs(generalSettingsAdmin());

    Livewire::test(GeneralSettings::class)
        ->assertSee('Preview updates immediately after selecting a new image.')
        ->assertSee('Preview updates immediately after selecting a new favicon.')
        ->assertSee('Upload Site Logo')
        ->assertSee('Upload Favicon');
});

it('shows the settings api endpoint in available endpoints', function () {
    $this->actingAs(generalSettingsAdmin());

    Livewire::test(GeneralSettings::class)
        ->assertSee('Available endpoints:')
        ->assertSee('GET /api/v1/dashboard')
        ->assertSee('GET /api/v1/settings');
});

it('stores logo and favicon uploads and refreshes current preview paths', function () {
    Storage::fake('public');

    $this->actingAs(generalSettingsAdmin());

    Livewire::test(GeneralSettings::class)
        ->set('site_name', 'NU Store')
        ->set('site_email', 'store@example.com')
        ->set('show_print_footer', true)
        ->set('store_mode', 'departmental')
        ->set('central_store_dept_id', null)
        ->set('logo', UploadedFile::fake()->image('logo.png'))
        ->set('favicon', UploadedFile::fake()->image('favicon.png'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('current_logo', fn ($path) => filled($path))
        ->assertSet('current_favicon', fn ($path) => filled($path));

    Storage::disk('public')->assertExists(setting('site_logo'));
    Storage::disk('public')->assertExists(setting('site_favicon'));
});

it('stores configurable approval flow roles from general settings', function () {
    $this->actingAs(generalSettingsAdmin());

    Livewire::test(GeneralSettings::class)
        ->set('site_name', 'NU Store')
        ->set('site_email', 'store@example.com')
        ->set('show_print_footer', true)
        ->set('store_mode', 'departmental')
        ->set('central_store_dept_id', null)
        ->set('approval_flow_roles', ['deputy_director', 'director'])
        ->call('save')
        ->assertHasNoErrors();

    expect(setting('approval_flow_roles'))->toBe(['deputy_director', 'director']);
});

it('normalizes approval flow builder steps with director as the final approver', function () {
    $this->actingAs(generalSettingsAdmin());

    Livewire::test(GeneralSettings::class)
        ->set('approval_flow_roles', ['director'])
        ->call('addApprovalStep')
        ->assertSet('approval_flow_roles', ['assistant_director', 'director'])
        ->call('addApprovalStep')
        ->assertSet('approval_flow_roles', ['assistant_director', 'deputy_director', 'director'])
        ->call('removeApprovalStep', 1)
        ->assertSet('approval_flow_roles', ['assistant_director', 'director']);
});
