<?php

use App\Livewire\Purpose\PurposeManager;
use App\Livewire\Requisition\CreateRequisition;
use App\Models\Category;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Product;
use App\Models\Purpose;
use App\Models\RequisitionItem;
use App\Models\User;
use Livewire\Livewire;

it('allows an admin to manage requisition purposes', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'pf_no' => 'ADMIN-PURPOSE-001',
        'mobile_no' => '01700001001',
        'is_approved' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(PurposeManager::class)
        ->set('name', 'Workshop Use')
        ->call('save')
        ->assertHasNoErrors();

    $purpose = Purpose::where('name', 'Workshop Use')->firstOrFail();

    Livewire::test(PurposeManager::class)
        ->call('edit', $purpose->id)
        ->set('name', 'Workshop & Seminar Use')
        ->set('is_active', false)
        ->call('save')
        ->assertHasNoErrors();

    expect($purpose->refresh()->name)->toBe('Workshop & Seminar Use')
        ->and($purpose->is_active)->toBeFalse();
});

it('submits a demand with an active dynamic purpose', function () {
    $department = Department::create([
        'name' => 'Procurement',
        'code' => 'PROC',
    ]);

    $designation = Designation::create([
        'title' => 'Officer',
        'rank' => 10,
    ]);

    $user = User::factory()->create([
        'role' => 'requisitioner',
        'pf_no' => 'REQ-PURPOSE-001',
        'mobile_no' => '01700001002',
        'is_approved' => true,
        'department_id' => $department->id,
        'designation_id' => $designation->id,
    ]);

    $category = Category::create(['name' => 'Stationery']);
    $product = Product::create([
        'category_id' => $category->id,
        'name_bn' => 'কলম',
        'name_en' => 'Pen',
        'stock' => 100,
    ]);
    $purpose = Purpose::create(['name' => 'Seminar Use', 'is_active' => true]);

    $this->actingAs($user);

    Livewire::test(CreateRequisition::class)
        ->set('selectedCategories.0', $category->id)
        ->set('requisitionItems.0.product_id', $product->id)
        ->set('requisitionItems.0.demanded_qty', 5)
        ->set('requisitionItems.0.purpose', $purpose->name)
        ->call('submitDemand')
        ->assertHasNoErrors();

    expect(RequisitionItem::where('purpose', 'Seminar Use')->where('demanded_qty', 5)->exists())->toBeTrue();
});

it('rejects inactive dynamic purposes when submitting demand', function () {
    $user = User::factory()->create([
        'role' => 'requisitioner',
        'pf_no' => 'REQ-PURPOSE-002',
        'mobile_no' => '01700001003',
        'is_approved' => true,
    ]);
    $category = Category::create(['name' => 'IT Supplies']);
    $product = Product::create([
        'category_id' => $category->id,
        'name_bn' => 'মাউস',
        'name_en' => 'Mouse',
        'stock' => 20,
    ]);
    $purpose = Purpose::create(['name' => 'Archived Purpose', 'is_active' => false]);

    $this->actingAs($user);

    Livewire::test(CreateRequisition::class)
        ->set('selectedCategories.0', $category->id)
        ->set('requisitionItems.0.product_id', $product->id)
        ->set('requisitionItems.0.demanded_qty', 2)
        ->set('requisitionItems.0.purpose', $purpose->name)
        ->call('submitDemand')
        ->assertHasErrors(['requisitionItems.0.purpose' => 'exists']);
});
