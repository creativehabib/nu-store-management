<?php

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

test('super admins can bulk delete selected audit logs and delete all records', function () {
    $admin = auditTrailUser(['role' => 'super_admin']);
    $this->actingAs($admin);

    $firstLog = AuditLog::create([
        'user_id' => $admin->id,
        'event' => 'auth.login',
        'description' => 'First audit log',
    ]);
    $secondLog = AuditLog::create([
        'user_id' => $admin->id,
        'event' => 'inventory.stock_changed',
        'description' => 'Second audit log',
    ]);
    $thirdLog = AuditLog::create([
        'user_id' => $admin->id,
        'event' => 'requisition.status_changed',
        'description' => 'Third audit log',
    ]);

    Livewire::test(\App\Livewire\Admin\AuditLogManager::class)
        ->set('selectedAuditLogs', [$firstLog->id, $secondLog->id])
        ->call('deleteSelected')
        ->assertSet('selectedAuditLogs', []);

    expect(AuditLog::query()->whereKey($firstLog->id)->exists())->toBeFalse()
        ->and(AuditLog::query()->whereKey($secondLog->id)->exists())->toBeFalse()
        ->and(AuditLog::query()->whereKey($thirdLog->id)->exists())->toBeTrue();

    Livewire::test(\App\Livewire\Admin\AuditLogManager::class)
        ->call('deleteAllRecords');

    expect(AuditLog::query()->count())->toBe(0);
});

test('admins can view but cannot delete audit logs', function () {
    $admin = auditTrailUser(['role' => 'admin']);
    $this->actingAs($admin);

    $auditLog = AuditLog::create([
        'user_id' => $admin->id,
        'event' => 'auth.login',
        'description' => 'Protected audit log',
    ]);

    Livewire::test(\App\Livewire\Admin\AuditLogManager::class)
        ->assertSee('Only super admin can delete logs')
        ->assertDontSee('Delete all records');

    expect(fn () => Livewire::test(\App\Livewire\Admin\AuditLogManager::class)
        ->set('selectedAuditLogs', [$auditLog->id])
        ->call('deleteSelected'))
        ->toThrow(HttpException::class);

    expect(AuditLog::query()->whereKey($auditLog->id)->exists())->toBeTrue();
});

test('audit logs can be filtered by date range while keeping backend records intact', function () {
    $superAdmin = auditTrailUser(['role' => 'super_admin']);
    $this->actingAs($superAdmin);

    AuditLog::create([
        'user_id' => $superAdmin->id,
        'event' => 'auth.login',
        'description' => 'Outside range audit log',
        'created_at' => now()->subDays(10),
        'updated_at' => now()->subDays(10),
    ]);
    AuditLog::create([
        'user_id' => $superAdmin->id,
        'event' => 'auth.login',
        'description' => 'Inside range audit log',
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    Livewire::test(\App\Livewire\Admin\AuditLogManager::class)
        ->set('startDate', now()->subDays(2)->toDateString())
        ->set('endDate', now()->toDateString())
        ->assertSee('Inside range audit log')
        ->assertDontSee('Outside range audit log');

    expect(AuditLog::query()->count())->toBe(2);
});
