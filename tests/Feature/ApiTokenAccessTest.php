<?php

use App\Models\ApiUserToken;
use App\Models\Category;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    file_put_contents(storage_path('installed'), 'installed');
});

afterEach(function (): void {
    if (file_exists(storage_path('installed'))) {
        unlink(storage_path('installed'));
    }
});

it('rejects api requests without an app token', function (): void {
    getJson('/api/v1/categories')
        ->assertUnauthorized()
        ->assertJson([
            'message' => 'Valid API app token is required.',
        ]);
});

it('rejects api requests with an invalid app token', function (): void {
    set_setting('api_token_hash', hash('sha256', 'correct-token'), 'api');

    getJson('/api/v1/categories', [
        'Authorization' => 'Bearer wrong-token',
    ])->assertUnauthorized();
});

it('allows api requests with a valid app token', function (): void {
    set_setting('api_token_hash', hash('sha256', 'correct-token'), 'api');

    Category::query()->create(['name' => 'Stationery']);

    getJson('/api/v1/categories', [
        'Authorization' => 'Bearer correct-token',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Stationery');
});

it('registers users through the api when the app token is valid', function (): void {
    set_setting('api_token_hash', hash('sha256', 'app-token'), 'api');

    $department = Department::query()->create(['name' => 'Store', 'code' => 'STR']);
    $designation = Designation::query()->create(['title' => 'Officer', 'rank' => 1]);

    postJson('/api/v1/auth/register', [
        'name' => 'API User',
        'email' => 'api@example.com',
        'pf_no' => 'PF-1001',
        'mobile_no' => '01700000000',
        'department_id' => $department->id,
        'designation_id' => $designation->id,
        'role' => 'requisitioner',
        'password' => 'Password#123',
        'password_confirmation' => 'Password#123',
    ], [
        'X-App-Token' => 'app-token',
    ])
        ->assertCreated()
        ->assertJsonPath('data.user.email', 'api@example.com')
        ->assertJsonPath('data.user.is_approved', false);
});

it('logs in approved users and returns the authenticated api profile', function (): void {
    set_setting('api_token_hash', hash('sha256', 'app-token'), 'api');

    $user = User::query()->create([
        'name' => 'Approved User',
        'email' => 'approved@example.com',
        'pf_no' => 'PF-2001',
        'mobile_no' => '01700000001',
        'password' => Hash::make('Password#123'),
        'role' => 'requisitioner',
        'is_approved' => true,
    ]);

    $loginResponse = postJson('/api/v1/auth/login', [
        'login' => $user->email,
        'password' => 'Password#123',
        'device_name' => 'android',
    ], [
        'X-App-Token' => 'app-token',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.user.email', 'approved@example.com');

    $userToken = $loginResponse->json('data.token');

    expect($userToken)->toBeString()
        ->and(ApiUserToken::query()->where('user_id', $user->id)->count())->toBe(1);

    getJson('/api/v1/auth/me', [
        'X-App-Token' => 'app-token',
        'Authorization' => 'Bearer '.$userToken,
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.user.email', 'approved@example.com');
});

it('does not log in users awaiting admin approval', function (): void {
    set_setting('api_token_hash', hash('sha256', 'app-token'), 'api');

    User::query()->create([
        'name' => 'Pending User',
        'email' => 'pending@example.com',
        'pf_no' => 'PF-3001',
        'mobile_no' => '01700000002',
        'password' => Hash::make('Password#123'),
        'role' => 'requisitioner',
        'is_approved' => false,
    ]);

    postJson('/api/v1/auth/login', [
        'login' => 'pending@example.com',
        'password' => 'Password#123',
    ], [
        'X-App-Token' => 'app-token',
    ])->assertForbidden();
});

it('returns application settings through the api without exposing secret settings', function (): void {
    set_setting('api_token_hash', hash('sha256', 'app-token'), 'api');
    set_setting('site_name', 'NU Store');
    set_setting('site_email', 'store@example.com');
    set_setting('site_logo', 'settings/logo.png');
    set_setting('mail_password', 'secret-mail-password', 'mail');
    set_setting('store_mode', 'centralized');
    set_setting('central_store_dept_id', 7);
    set_setting('approval_flow_roles', ['deputy_director', 'director']);

    getJson('/api/v1/settings', [
        'X-App-Token' => 'app-token',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.site.name', 'NU Store')
        ->assertJsonPath('data.site.email', 'store@example.com')
        ->assertJsonPath('data.site.logo', 'settings/logo.png')
        ->assertJsonPath('data.requisition.store_mode', 'centralized')
        ->assertJsonPath('data.requisition.central_store_dept_id', 7)
        ->assertJsonPath('data.requisition.approval_flow_roles.0', 'deputy_director')
        ->assertJsonMissing(['api_token_hash' => hash('sha256', 'app-token')])
        ->assertJsonMissing(['mail_password' => 'secret-mail-password']);
});
