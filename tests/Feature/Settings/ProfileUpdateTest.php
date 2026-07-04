<?php

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function profileTestUser(array $attributes = []): User
{
    $department = Department::create([
        'name' => fake()->unique()->word(),
        'code' => fake()->unique()->lexify('???'),
    ]);

    $designation = Designation::create([
        'title' => fake()->unique()->jobTitle(),
        'rank' => fake()->numberBetween(1, 10),
    ]);

    return User::factory()->create([
        'pf_no' => fake()->unique()->numerify('PF#####'),
        'mobile_no' => fake()->unique()->numerify('01#########'),
        'role' => 'requisitioner',
        'department_id' => $department->id,
        'designation_id' => $designation->id,
        ...$attributes,
    ]);
}

test('profile page is displayed', function () {
    $this->actingAs($user = profileTestUser());

    $this->get(route('profile.edit'))->assertOk();
});

test('profile information can be updated', function () {
    $user = profileTestUser();

    $this->actingAs($user);

    $department = Department::create([
        'name' => 'Updated Department',
        'code' => 'UPD',
    ]);
    $designation = Designation::create([
        'title' => 'Updated Designation',
        'rank' => 50,
    ]);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('pf_no', 'PF99999')
        ->set('mobile_no', '01999999999')
        ->set('designation_id', $designation->id)
        ->set('department_id', $department->id)
        ->set('role', 'initiator')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->pf_no)->toEqual('PF99999');
    expect($user->mobile_no)->toEqual('01999999999');
    expect($user->designation_id)->toEqual($designation->id);
    expect($user->department_id)->toEqual($department->id);
    expect($user->role)->toEqual('initiator');
    expect($user->email_verified_at)->toBeNull();
});

test('profile image can be updated', function () {
    Storage::fake('public');

    $user = profileTestUser([
        'picture' => 'profile-images/old-profile.png',
    ]);

    Storage::disk('public')->put($user->picture, 'old profile');

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('pf_no', $user->pf_no)
        ->set('mobile_no', $user->mobile_no)
        ->set('designation_id', $user->designation_id)
        ->set('department_id', $user->department_id)
        ->set('role', $user->role)
        ->set('picture', UploadedFile::fake()->image('new-profile.png'))
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->picture)->not->toBe('profile-images/old-profile.png');

    Storage::disk('public')->assertMissing('profile-images/old-profile.png');
    Storage::disk('public')->assertExists($user->picture);
});

test('profile signature can be updated', function () {
    Storage::fake('public');

    $user = profileTestUser([
        'digital_signature' => 'signatures/old-signature.png',
    ]);

    Storage::disk('public')->put($user->digital_signature, 'old signature');

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('pf_no', $user->pf_no)
        ->set('mobile_no', $user->mobile_no)
        ->set('designation_id', $user->designation_id)
        ->set('department_id', $user->department_id)
        ->set('role', $user->role)
        ->set('digital_signature', UploadedFile::fake()->image('new-signature.png'))
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->digital_signature)->not->toBe('signatures/old-signature.png');

    Storage::disk('public')->assertMissing('signatures/old-signature.png');
    Storage::disk('public')->assertExists($user->digital_signature);
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = profileTestUser();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->set('pf_no', $user->pf_no)
        ->set('mobile_no', $user->mobile_no)
        ->set('designation_id', $user->designation_id)
        ->set('department_id', $user->department_id)
        ->set('role', $user->role)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = profileTestUser();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = profileTestUser();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});