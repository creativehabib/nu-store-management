<?php

use App\Livewire\Requisition\MyRequisitions;
use App\Models\Category;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\User;
use Livewire\Livewire;

function requisitionHistoryUser(): User
{
    $department = Department::create([
        'name' => 'Requester Department',
        'code' => 'REQD',
    ]);

    $designation = Designation::create([
        'title' => 'Requester Officer',
        'rank' => 30,
    ]);

    return User::factory()->create([
        'role' => 'requisitioner',
        'pf_no' => 'REQ-HISTORY-001',
        'mobile_no' => '01700006001',
        'department_id' => $department->id,
        'designation_id' => $designation->id,
        'is_approved' => true,
    ]);
}

it('shows requisition history stats and filters by status and product search', function () {
    $user = requisitionHistoryUser();
    $category = Category::create(['name' => 'Stationery']);
    $product = Product::create([
        'category_id' => $category->id,
        'name_bn' => 'কলম',
        'name_en' => 'Pen',
        'stock' => 50,
    ]);

    $pendingRequisition = Requisition::create([
        'requisition_no' => 'REQ-HISTORY-001',
        'user_id' => $user->id,
        'status' => 'pending',
        'approval_history' => [],
    ]);
    $pendingRequisition->items()->create([
        'product_id' => $product->id,
        'demanded_qty' => 4,
        'supplied_qty' => 0,
        'purpose' => 'Official Use',
    ]);

    Requisition::create([
        'requisition_no' => 'REQ-HISTORY-002',
        'user_id' => $user->id,
        'status' => 'distributed',
        'approval_history' => [],
    ]);

    $this->actingAs($user);

    Livewire::test(MyRequisitions::class)
        ->assertSee('Total Submitted')
        ->assertSee('In Progress')
        ->assertSee('Items Summary')
        ->assertSee('Pen')
        ->assertSee('4')
        ->set('statusFilter', 'pending')
        ->assertSee('REQ-HISTORY-001')
        ->assertDontSee('REQ-HISTORY-002')
        ->set('search', 'Pen')
        ->assertSee('REQ-HISTORY-001')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('statusFilter', '');
});
