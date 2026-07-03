<?php

use App\Livewire\Workflow\ApprovalQueue;
use App\Livewire\Workflow\InitiatorQueue;
use App\Models\Department;
use App\Models\Requisition;
use App\Models\Setting;
use App\Models\User;
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
        'status' => Requisition::initialStatus(),
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
        'status' => Requisition::initialStatus(),
        'approval_history' => [],
    ]);

    expect($requisition->status)->toBe('pending');

    $this->actingAs($initiator);
    Livewire::test(InitiatorQueue::class)
        ->assertSet('requisitions', fn ($requisitions) => $requisitions->pluck('id')->contains($requisition->id));
});
