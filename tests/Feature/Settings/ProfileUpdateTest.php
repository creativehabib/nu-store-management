<?php

use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    $this->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('Delete account');
});

test('profile information can be updated', function () {
    $user = profileTestUser();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('mobile_no', '01999999999')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->mobile_no)->toEqual('01999999999');
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
        ->set('mobile_no', $user->mobile_no)
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
        ->set('mobile_no', $user->mobile_no)
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
        ->set('mobile_no', $user->mobile_no)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user cannot delete their own account from the self-service modal', function () {
    $user = profileTestUser();

    $this->actingAs($user);

    expect(fn () => Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser'))
        ->toThrow(HttpException::class);

    expect($user->fresh())->not->toBeNull();
    expect(auth()->check())->toBeTrue();
});

test('self-service account deletion stays blocked even with an incorrect password', function () {
    $user = profileTestUser();

    $this->actingAs($user);

    expect(fn () => Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser'))
        ->toThrow(HttpException::class);

    expect($user->fresh())->not->toBeNull();
});
