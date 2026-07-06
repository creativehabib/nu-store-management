<?php

use App\Livewire\Layout\WorkflowQueueBadge;
use App\Livewire\Workflow\ApprovalQueue;
use App\Livewire\Workflow\InitiatorQueue;
use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\Setting;
use App\Models\User;
use App\Support\WorkflowQueueCounter;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

function workflowUser(string $role, Department $department): User
{
    return User::factory()->create([
        'pf_no' => fake()->unique()->numerify('PF#####'),
        'mobile_no' => fake()->unique()->numerify('01#########'),
        'role' => $role,
        'department_id' => $department->id,
        'is_approved' => true,
    ]);
}

function workflowSetting(string $key, mixed $value): void
{
    Setting::updateOrCreate(['key' => $key], [
        'value' => $value,
        'group' => 'general',
        'autoload' => true,
    ]);
}

it('routes centralized requisitions to the applicant department director before central store initiator', function () {
    Notification::fake();
    touch(storage_path('installed'));

    $applicantDepartment = Department::create(['name' => 'Applicant Department', 'code' => 'APP']);
    $centralStoreDepartment = Department::create(['name' => 'Central Store', 'code' => 'CEN']);

    workflowSetting('store_mode', 'centralized');
    workflowSetting('central_store_dept_id', $centralStoreDepartment->id);

    $requisitioner = workflowUser('requisitioner', $applicantDepartment);
    $departmentDirector = workflowUser('director', $applicantDepartment);
    $departmentInitiator = workflowUser('initiator', $applicantDepartment);
    $centralInitiator = workflowUser('initiator', $centralStoreDepartment);

    $requisition = Requisition::create([
        'requisition_no' => 'REQ-CENTRAL-001',
        'user_id' => $requisitioner->id,
        'status' => Requisition::initialStatus($requisitioner->department_id),
        'approval_history' => [],
    ]);

    expect($requisition->status)->toBe('department_director_review');

    $this->actingAs($departmentDirector);

    Livewire::test(ApprovalQueue::class)
        ->set('selectedRequisition', $requisition)
        ->call('processAction', 'approve');

    expect($requisition->refresh()->status)->toBe('pending');

    $this->actingAs($departmentInitiator);
    Livewire::test(InitiatorQueue::class)
        ->assertSet('requisitions', fn ($requisitions) => $requisitions->pluck('id')->doesntContain($requisition->id));

    $this->actingAs($centralInitiator);
    Livewire::test(InitiatorQueue::class)
        ->assertSet('requisitions', fn ($requisitions) => $requisitions->pluck('id')->contains($requisition->id));
});

it('keeps departmental requisitions on the department initiator first', function () {
    touch(storage_path('installed'));

    $department = Department::create(['name' => 'Own Department', 'code' => 'OWN']);

    workflowSetting('store_mode', 'departmental');

    $requisitioner = workflowUser('requisitioner', $department);
    $initiator = workflowUser('initiator', $department);

    $requisition = Requisition::create([
        'requisition_no' => 'REQ-DEPT-001',
        'user_id' => $requisitioner->id,
        'status' => Requisition::initialStatus($requisitioner->department_id),
        'approval_history' => [],
    ]);

    expect($requisition->status)->toBe('pending');

    $this->actingAs($initiator);
    Livewire::test(InitiatorQueue::class)
        ->assertSet('requisitions', fn ($requisitions) => $requisitions->pluck('id')->contains($requisition->id));
});

it('sends centralized requisitions from central store requisitioners directly to the store initiator', function () {
    touch(storage_path('installed'));

    $centralStoreDepartment = Department::create(['name' => 'Requester Central Store', 'code' => 'RCS']);

    workflowSetting('store_mode', 'centralized');
    workflowSetting('central_store_dept_id', $centralStoreDepartment->id);

    $requisitioner = workflowUser('requisitioner', $centralStoreDepartment);
    $centralInitiator = workflowUser('initiator', $centralStoreDepartment);

    $requisition = Requisition::create([
        'requisition_no' => 'REQ-CENTRAL-STORE-001',
        'user_id' => $requisitioner->id,
        'status' => Requisition::initialStatus($requisitioner->department_id),
        'approval_history' => [],
    ]);

    expect($requisition->status)->toBe('pending');

    $this->actingAs($centralInitiator);
    Livewire::test(InitiatorQueue::class)
        ->assertSet('requisitions', fn ($requisitions) => $requisitions->pluck('id')->contains($requisition->id));
});

it('shows enhanced initiator queue columns and filters by status and product search', function () {
    touch(storage_path('installed'));

    $department = Department::create(['name' => 'Enhanced Queue Department', 'code' => 'EQD']);
    $category = Category::create(['name' => 'Office Supplies']);
    $product = Product::create([
        'category_id' => $category->id,
        'name_bn' => 'স্ট্যাপলার',
        'name_en' => 'Stapler',
        'stock' => 25,
    ]);

    workflowSetting('store_mode', 'departmental');

    $requisitioner = workflowUser('requisitioner', $department);
    $initiator = workflowUser('initiator', $department);

    $pendingRequisition = Requisition::create([
        'requisition_no' => 'REQ-ENHANCED-001',
        'user_id' => $requisitioner->id,
        'status' => 'pending',
        'approval_history' => [],
    ]);
    $pendingRequisition->items()->create([
        'product_id' => $product->id,
        'demanded_qty' => 7,
        'supplied_qty' => 0,
        'purpose' => 'Official Use',
    ]);

    Requisition::create([
        'requisition_no' => 'REQ-ENHANCED-002',
        'user_id' => $requisitioner->id,
        'status' => 'distributed',
        'approval_history' => [],
    ]);

    $this->actingAs($initiator);

    Livewire::test(InitiatorQueue::class)
        ->assertSee('Items Summary')
        ->assertSee('Demand')
        ->assertSee('Age')
        ->assertSee('Stapler')
        ->assertSee('7')
        ->set('statusFilter', 'pending')
        ->assertSee('REQ-ENHANCED-001')
        ->assertDontSee('REQ-ENHANCED-002')
        ->set('search', 'Stapler')
        ->assertSee('REQ-ENHANCED-001');
});

it('counts sidebar workflow queue notifications for the current user', function () {
    touch(storage_path('installed'));

    $applicantDepartment = Department::create(['name' => 'Sidebar Applicant Department', 'code' => 'SAD']);
    $centralStoreDepartment = Department::create(['name' => 'Sidebar Central Store', 'code' => 'SCS']);

    workflowSetting('store_mode', 'centralized');
    workflowSetting('central_store_dept_id', $centralStoreDepartment->id);

    $requisitioner = workflowUser('requisitioner', $applicantDepartment);
    $departmentDirector = workflowUser('director', $applicantDepartment);
    $departmentInitiator = workflowUser('initiator', $applicantDepartment);
    $centralInitiator = workflowUser('initiator', $centralStoreDepartment);

    Requisition::create([
        'requisition_no' => 'REQ-SIDEBAR-001',
        'user_id' => $requisitioner->id,
        'status' => 'department_director_review',
        'approval_history' => [],
    ]);

    Requisition::create([
        'requisition_no' => 'REQ-SIDEBAR-002',
        'user_id' => $requisitioner->id,
        'status' => 'pending',
        'approval_history' => [],
    ]);

    Requisition::create([
        'requisition_no' => 'REQ-SIDEBAR-003',
        'user_id' => $requisitioner->id,
        'status' => 'distributed',
        'approval_history' => [],
    ]);

    $counter = app(WorkflowQueueCounter::class);

    $this->actingAs($departmentDirector);
    expect($counter->countsFor($departmentDirector))->toBe([
        'initiator' => 0,
        'approval' => 1,
    ]);
    Livewire::test(WorkflowQueueBadge::class, ['type' => 'approval'])
        ->assertSet('count', 1);

    $this->actingAs($departmentInitiator);
    expect($counter->countsFor($departmentInitiator))->toBe([
        'initiator' => 0,
        'approval' => 0,
    ]);

    $this->actingAs($centralInitiator);
    expect($counter->countsFor($centralInitiator))->toBe([
        'initiator' => 1,
        'approval' => 0,
    ]);
    Livewire::test(WorkflowQueueBadge::class, ['type' => 'initiator'])
        ->assertSet('count', 1);
});

it('allows settings to skip assistant director in the approval flow', function () {
    Notification::fake();
    touch(storage_path('installed'));

    $department = Department::create(['name' => 'Configurable Flow Department', 'code' => 'CFD']);

    workflowSetting('store_mode', 'departmental');
    workflowSetting('approval_flow_roles', json_encode(['deputy_director', 'director']));

    $requisitioner = workflowUser('requisitioner', $department);
    $initiator = workflowUser('initiator', $department);
    $assistantDirector = workflowUser('assistant_director', $department);
    $deputyDirector = workflowUser('deputy_director', $department);
    $director = workflowUser('director', $department);

    $requisition = Requisition::create([
        'requisition_no' => 'REQ-FLOW-001',
        'user_id' => $requisitioner->id,
        'status' => 'pending',
        'approval_history' => [],
    ]);

    $this->actingAs($initiator);
    Livewire::test(InitiatorQueue::class)
        ->set('selectedRequisition', $requisition)
        ->call('forwardRequisition');

    expect($requisition->refresh()->status)->toBe('ad_approved');

    $this->actingAs($assistantDirector);
    Livewire::test(ApprovalQueue::class)
        ->assertDontSee('REQ-FLOW-001');

    $this->actingAs($deputyDirector);
    Livewire::test(ApprovalQueue::class)
        ->assertSee('REQ-FLOW-001')
        ->set('selectedRequisition', $requisition->refresh())
        ->call('processAction', 'approve');

    expect($requisition->refresh()->status)->toBe('dd_approved');

    $this->actingAs($director);
    Livewire::test(ApprovalQueue::class)
        ->assertSee('REQ-FLOW-001');
});

it('allows settings to send requisitions from initiator directly to director', function () {
    Notification::fake();
    touch(storage_path('installed'));

    $department = Department::create(['name' => 'Direct Director Flow', 'code' => 'DDF']);

    workflowSetting('store_mode', 'departmental');
    workflowSetting('approval_flow_roles', json_encode(['director']));

    $requisitioner = workflowUser('requisitioner', $department);
    $initiator = workflowUser('initiator', $department);
    $deputyDirector = workflowUser('deputy_director', $department);
    $director = workflowUser('director', $department);

    $requisition = Requisition::create([
        'requisition_no' => 'REQ-FLOW-002',
        'user_id' => $requisitioner->id,
        'status' => 'pending',
        'approval_history' => [],
    ]);

    $this->actingAs($initiator);
    Livewire::test(InitiatorQueue::class)
        ->set('selectedRequisition', $requisition)
        ->call('forwardRequisition');

    expect($requisition->refresh()->status)->toBe('dd_approved');

    $this->actingAs($deputyDirector);
    Livewire::test(ApprovalQueue::class)
        ->assertDontSee('REQ-FLOW-002');

    $this->actingAs($director);
    Livewire::test(ApprovalQueue::class)
        ->assertSee('REQ-FLOW-002')
        ->set('selectedRequisition', $requisition->refresh())
        ->call('processAction', 'approve');

    expect($requisition->refresh()->status)->toBe('director_approved');
});

it('allows settings to skip deputy director while keeping assistant director before director', function () {
    Notification::fake();
    touch(storage_path('installed'));

    $department = Department::create(['name' => 'Assistant Direct Flow', 'code' => 'ADF']);

    workflowSetting('store_mode', 'departmental');
    workflowSetting('approval_flow_roles', json_encode(['assistant_director', 'director']));

    $requisitioner = workflowUser('requisitioner', $department);
    $initiator = workflowUser('initiator', $department);
    $assistantDirector = workflowUser('assistant_director', $department);
    $deputyDirector = workflowUser('deputy_director', $department);
    $director = workflowUser('director', $department);

    $requisition = Requisition::create([
        'requisition_no' => 'REQ-FLOW-003',
        'user_id' => $requisitioner->id,
        'status' => 'pending',
        'approval_history' => [],
    ]);

    $this->actingAs($initiator);
    Livewire::test(InitiatorQueue::class)
        ->set('selectedRequisition', $requisition)
        ->call('forwardRequisition');

    expect($requisition->refresh()->status)->toBe('initiator_checked');

    $this->actingAs($assistantDirector);
    Livewire::test(ApprovalQueue::class)
        ->assertSee('REQ-FLOW-003')
        ->set('selectedRequisition', $requisition->refresh())
        ->call('processAction', 'approve');

    expect($requisition->refresh()->status)->toBe('dd_approved');

    $this->actingAs($deputyDirector);
    Livewire::test(ApprovalQueue::class)
        ->assertDontSee('REQ-FLOW-003');

    $this->actingAs($director);
    Livewire::test(ApprovalQueue::class)
        ->assertSee('REQ-FLOW-003');
});
