<?php

namespace App\Livewire\Workflow;

use App\Models\Department; // Department মডেলটি যুক্ত করা হলো
use App\Models\Requisition;
use App\Models\User;
use App\Notifications\RequisitionNotification;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class InitiatorQueue extends Component
{
    public $requisitions;

    public ?Requisition $selectedRequisition = null;

    public $suppliedQuantities = [];

    public $comment = '';

    public string $search = '';

    public function mount()
    {
        $this->loadRequisitions();
    }

    public function updatedSearch()
    {
        $this->loadRequisitions();
    }

    public function loadRequisitions(): void
    {
        $query = Requisition::with(['user.department', 'items.product'])
            ->whereIn('status', ['pending', 'returned', 'director_approved', 'distributed'])
            ->forUserDepartment();

        if (setting('store_mode', 'departmental') === 'centralized'
            && Auth::user()->role === 'initiator'
            && (int) Auth::user()->department_id !== (int) setting('central_store_dept_id', 1)) {
            $query->whereRaw('1 = 0');
        }

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('requisition_no', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        $this->requisitions = $query->latest()->get();
    }

    public function viewRequisition($id): void
    {
        $this->selectedRequisition = Requisition::with(['user.department', 'items.product'])
            ->forUserDepartment()
            ->findOrFail($id);

        $this->suppliedQuantities = [];
        foreach ($this->selectedRequisition->items as $item) {
            $this->suppliedQuantities[$item->id] = $item->demanded_qty;
        }
        $this->comment = '';
        Flux::modal('view-action-modal')->show();
    }

    public function forwardRequisition(): void
    {
        if (! $this->selectedRequisition) {
            Flux::toast(__('Requisition data not found!'), variant: 'danger');
            return;
        }

        DB::transaction(function () {
            foreach ($this->selectedRequisition->items as $item) {
                if (isset($this->suppliedQuantities[$item->id])) {
                    $item->update(['supplied_qty' => $this->suppliedQuantities[$item->id]]);
                }
            }

            $history = $this->selectedRequisition->approval_history ?? [];
            $history[] = [
                'role' => 'initiator',
                'name' => Auth::user()->name,
                'action' => 'forwarded',
                'comment' => $this->comment,
                'date' => now()->toDateTimeString(),
                'signature' => Auth::user()->digital_signature,
            ];

            $this->selectedRequisition->update([
                'status' => 'initiator_checked',
                'approval_history' => $history,
            ]);
        });

        // নোটিফিকেশন লজিক
        $message = __('New requisition (:req_no) is waiting for your approval.', ['req_no' => $this->selectedRequisition->requisition_no]);
        $url = route('workflow.approval');

        $approvingDeptId = Department::getApprovingDepartmentId($this->selectedRequisition->user->department_id);

        // সঠিক দপ্তরের Assistant Director (AD) কে নোটিফিকেশন পাঠানো
        $targetUsers = User::where('role', 'assistant_director')
            ->where('department_id', $approvingDeptId)
            ->whereNotNull('email')
            ->get();

        if ($targetUsers->isNotEmpty()) {
            Notification::send($targetUsers, new RequisitionNotification($this->selectedRequisition, $message, $url));
        }

        Flux::toast(__('Requisition successfully forwarded!'));
        $this->dispatch('workflow-queue-updated');
        Flux::modal('view-action-modal')->close();

        $this->selectedRequisition = null;
        $this->loadRequisitions();
    }

    public function render()
    {
        return view('livewire.workflow.initiator-queue')->layout('layouts.app', ['title' => 'Initiator Queue']);
    }
}
