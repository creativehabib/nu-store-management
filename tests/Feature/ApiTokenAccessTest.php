<?php

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    file_put_contents(storage_path('installed'), 'installed');
});

afterEach(function (): void {
    if (file_exists(storage_path('installed'))) {
        unlink(storage_path('installed'));
    }
});

it('rejects api requests without a bearer token', function (): void {
    getJson('/api/v1/categories')
        ->assertUnauthorized()
        ->assertJson([
            'message' => 'Valid API bearer token is required.',
        ]);
});

it('rejects api requests with an invalid bearer token', function (): void {
    Setting::query()->create([
        'key' => 'api_token_hash',
        'value' => hash('sha256', 'correct-token'),
        'group' => 'api',
    ]);

    getJson('/api/v1/categories', [
        'Authorization' => 'Bearer wrong-token',
    ])->assertUnauthorized();
});

it('allows api requests with a valid bearer token', function (): void {
    Setting::query()->create([
        'key' => 'api_token_hash',
        'value' => hash('sha256', 'correct-token'),
        'group' => 'api',
    ]);

    Category::query()->create(['name' => 'Stationery']);

    getJson('/api/v1/categories', [
        'Authorization' => 'Bearer correct-token',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Stationery');
});
