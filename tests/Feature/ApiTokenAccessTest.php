<?php

use App\Models\ApiUserToken;
use App\Models\Category;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Product;
use App\Models\Requisition;
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

it('returns authenticated dashboard data through the api', function (): void {
    set_setting('api_token_hash', hash('sha256', 'app-token'), 'api');

    $department = Department::query()->create(['name' => 'Store', 'code' => 'STR']);
    $category = Category::query()->create(['name' => 'Stationery']);
    Product::query()->create([
        'category_id' => $category->id,
        'name_bn' => 'কলম',
        'name_en' => 'Pen',
        'stock' => 0,
    ]);

    $user = User::query()->create([
        'name' => 'Dashboard User',
        'email' => 'dashboard@example.com',
        'pf_no' => 'PF-4001',
        'mobile_no' => '01700000004',
        'password' => Hash::make('Password#123'),
        'role' => 'initiator',
        'department_id' => $department->id,
        'is_approved' => true,
    ]);

    Requisition::query()->create([
        'requisition_no' => 'REQ-DASH-001',
        'user_id' => $user->id,
        'status' => 'pending',
        'approval_history' => [],
    ]);

    ApiUserToken::query()->create([
        'user_id' => $user->id,
        'name' => 'android',
        'token_hash' => hash('sha256', 'user-token'),
    ]);

    getJson('/api/v1/dashboard', [
        'X-App-Token' => 'app-token',
        'Authorization' => 'Bearer user-token',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.role', 'initiator')
        ->assertJsonPath('data.stats.pending_action', 1)
        ->assertJsonPath('data.stats.stock_out_products', 1)
        ->assertJsonPath('data.recent_requisitions.0.requisition_no', 'REQ-DASH-001')
        ->assertJsonStructure([
            'data' => [
                'stats',
                'charts' => [
                    'requisition_trends' => ['labels', 'values'],
                    'category_inventory' => ['labels', 'values'],
                ],
                'recent_requisitions',
                'my_own_requisitions',
            ],
        ]);
});


it('returns only the authenticated users requisition history through the api', function (): void {
    set_setting('api_token_hash', hash('sha256', 'app-token'), 'api');

    $category = Category::query()->create(['name' => 'Stationery']);
    $pen = Product::query()->create([
        'category_id' => $category->id,
        'name_bn' => 'কলম',
        'name_en' => 'Pen',
        'stock' => 50,
    ]);
    $paper = Product::query()->create([
        'category_id' => $category->id,
        'name_bn' => 'কাগজ',
        'name_en' => 'Paper',
        'stock' => 30,
    ]);

    $user = User::query()->create([
        'name' => 'History User',
        'email' => 'history@example.com',
        'pf_no' => 'PF-5001',
        'mobile_no' => '01700000005',
        'password' => Hash::make('Password#123'),
        'role' => 'requisitioner',
        'is_approved' => true,
    ]);

    $otherUser = User::query()->create([
        'name' => 'Other User',
        'email' => 'other@example.com',
        'pf_no' => 'PF-5002',
        'mobile_no' => '01700000006',
        'password' => Hash::make('Password#123'),
        'role' => 'requisitioner',
        'is_approved' => true,
    ]);

    $matchingRequisition = Requisition::query()->create([
        'requisition_no' => 'REQ-MINE-001',
        'user_id' => $user->id,
        'status' => 'distributed',
        'approval_history' => [],
    ]);
    $matchingRequisition->items()->create([
        'product_id' => $pen->id,
        'demanded_qty' => 2,
        'supplied_qty' => 2,
        'purpose' => 'Official Use',
    ]);

    $ownNonMatchingRequisition = Requisition::query()->create([
        'requisition_no' => 'REQ-MINE-002',
        'user_id' => $user->id,
        'status' => 'pending',
        'approval_history' => [],
    ]);
    $ownNonMatchingRequisition->items()->create([
        'product_id' => $paper->id,
        'demanded_qty' => 1,
        'supplied_qty' => 0,
        'purpose' => 'Official Use',
    ]);

    Requisition::query()->create([
        'requisition_no' => 'REQ-OTHER-001',
        'user_id' => $otherUser->id,
        'status' => 'distributed',
        'approval_history' => [],
    ])->items()->create([
        'product_id' => $pen->id,
        'demanded_qty' => 4,
        'supplied_qty' => 4,
        'purpose' => 'Official Use',
    ]);

    ApiUserToken::query()->create([
        'user_id' => $user->id,
        'name' => 'android',
        'token_hash' => hash('sha256', 'user-token'),
    ]);

    getJson('/api/v1/my-requisitions?status=distributed&search=Pen&per_page=5', [
        'X-App-Token' => 'app-token',
        'Authorization' => 'Bearer user-token',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.data.0.requisition_no', 'REQ-MINE-001')
        ->assertJsonPath('data.data.0.items.0.product.name_en', 'Pen')
        ->assertJsonCount(1, 'data.data')
        ->assertJsonMissing(['requisition_no' => 'REQ-MINE-002'])
        ->assertJsonMissing(['requisition_no' => 'REQ-OTHER-001']);
});
