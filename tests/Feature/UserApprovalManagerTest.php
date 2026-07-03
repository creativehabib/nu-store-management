<?php

use App\Livewire\Admin\UserApprovalManager;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('allows an admin to update user information including password, profile image, and signature', function () {
    Storage::fake('public');

    $department = Department::create([
        'name' => 'Procurement',
        'code' => 'PROC',
    ]);

    $designation = Designation::create([
        'title' => 'Assistant Director',
        'rank' => 10,
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'pf_no' => 'ADMIN-001',
        'mobile_no' => '01700000000',
        'department_id' => $department->id,
        'designation_id' => $designation->id,
    ]);

    Storage::disk('public')->put('profile-images/old-profile.png', 'old profile');
    Storage::disk('public')->put('signatures/old-signature.png', 'old signature');

    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
        'picture' => 'profile-images/old-profile.png',
        'digital_signature' => 'signatures/old-signature.png',
        'role' => 'requisitioner',
        'pf_no' => 'USER-001',
        'mobile_no' => '01800000000',
        'department_id' => $department->id,
        'designation_id' => $designation->id,
    ]);

    $this->actingAs($admin);

    Livewire::test(UserApprovalManager::class)
        ->call('edit', $user->id)
        ->set('name', 'Updated User')
        ->set('email', 'updated@example.com')
        ->set('pf_no', 'USER-002')
        ->set('mobile_no', '01900000000')
        ->set('designation_id', $designation->id)
        ->set('department_id', $department->id)
        ->set('role', 'super_admin')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->set('picture', UploadedFile::fake()->image('new-profile.png'))
        ->set('digital_signature', UploadedFile::fake()->image('new-signature.png'))
        ->call('update')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Updated User')
        ->and($user->email)->toBe('updated@example.com')
        ->and($user->pf_no)->toBe('USER-002')
        ->and($user->mobile_no)->toBe('01900000000')
        ->and($user->role)->toBe('super_admin')
        ->and($user->picture)->not->toBe('profile-images/old-profile.png')
        ->and($user->digital_signature)->not->toBe('signatures/old-signature.png')
        ->and(Hash::check('new-password', $user->password))->toBeTrue();

    Storage::disk('public')->assertMissing('profile-images/old-profile.png');
    Storage::disk('public')->assertMissing('signatures/old-signature.png');
    Storage::disk('public')->assertExists($user->picture);
    Storage::disk('public')->assertExists($user->digital_signature);
});

it('keeps the existing password, profile image, and signature when upload fields are blank', function () {
    Storage::fake('public');

    $department = Department::create([
        'name' => 'Store',
        'code' => 'STORE',
    ]);

    $designation = Designation::create([
        'title' => 'Director',
        'rank' => 1,
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
        'pf_no' => 'ADMIN-002',
        'mobile_no' => '01700000001',
        'department_id' => $department->id,
        'designation_id' => $designation->id,
    ]);

    Storage::disk('public')->put('profile-images/existing-profile.png', 'existing profile');
    Storage::disk('public')->put('signatures/existing-signature.png', 'existing signature');

    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
        'picture' => 'profile-images/existing-profile.png',
        'digital_signature' => 'signatures/existing-signature.png',
        'role' => 'requisitioner',
        'pf_no' => 'USER-003',
        'mobile_no' => '01800000001',
        'department_id' => $department->id,
        'designation_id' => $designation->id,
    ]);

    $this->actingAs($admin);

    Livewire::test(UserApprovalManager::class)
        ->call('edit', $user->id)
        ->set('name', 'Updated Without Password')
        ->set('email', 'blank-password@example.com')
        ->set('pf_no', 'USER-004')
        ->set('mobile_no', '01900000001')
        ->set('designation_id', $designation->id)
        ->set('department_id', $department->id)
        ->set('role', 'director')
        ->set('password', '')
        ->set('password_confirmation', '')
        ->call('update')
        ->assertHasNoErrors();

    $user->refresh();

    expect(Hash::check('old-password', $user->password))->toBeTrue()
        ->and($user->picture)->toBe('profile-images/existing-profile.png')
        ->and($user->digital_signature)->toBe('signatures/existing-signature.png');

    Storage::disk('public')->assertExists('profile-images/existing-profile.png');
    Storage::disk('public')->assertExists('signatures/existing-signature.png');
});
