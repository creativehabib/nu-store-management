<?php

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Support\Facades\URL;

function auditTrailUser(array $attributes = []): User
{
    $department = Department::create([
        'name' => 'Audit Department',
    ]);

    return User::factory()->create(array_merge([
        'role' => 'admin',
        'is_approved' => true,
        'department_id' => $department->id,
        'pf_no' => fake()->unique()->numerify('AUD-####'),
        'mobile_no' => fake()->unique()->numerify('017########'),
    ], $attributes));
}

test('successful logins are written to the audit trail', function () {
    $user = auditTrailUser();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $auditLog = AuditLog::query()->where('event', 'auth.login')->first();

    expect($auditLog)->not->toBeNull()
        ->and($auditLog->user_id)->toBe($user->id)
        ->and($auditLog->auditable_type)->toBe($user->getMorphClass())
        ->and($auditLog->auditable_id)->toBe($user->id);
});

test('requisition status and product stock changes are written to the audit trail', function () {
    $user = auditTrailUser();
    $this->actingAs($user);

    $category = Category::create(['name' => 'Stationery']);

    $product = Product::create([
        'category_id' => $category->id,
        'name_bn' => 'কলম',
        'name_en' => 'Pen',
        'stock' => 10,
    ]);

    $product->increment('stock', 5);

    $requisition = Requisition::create([
        'requisition_no' => 'REQ-AUD-001',
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $requisition->update(['status' => 'director_approved']);

    expect(AuditLog::query()->where('event', 'inventory.stock_changed')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('event', 'requisition.status_changed')->exists())->toBeTrue();
});

test('signed requisition verification link shows live status', function () {
    $user = auditTrailUser();
    $requisition = Requisition::create([
        'requisition_no' => 'REQ-VERIFY-001',
        'user_id' => $user->id,
        'status' => 'distributed',
    ]);

    $url = URL::signedRoute('requisition.verify', ['requisition' => $requisition]);

    $this->get($url)
        ->assertOk()
        ->assertSee('REQ-VERIFY-001')
        ->assertSee('Distributed');
});
