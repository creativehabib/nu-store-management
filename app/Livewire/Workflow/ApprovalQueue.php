<?php

namespace App\Livewire\Workflow;

use App\Models\Department;
use App\Models\Requisition;
use App\Models\User;
use App\Notifications\RequisitionNotification;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class ApprovalQueue extends Component
{
    use WithPagination;

    public $search = '';
    public $start_date = '';
    public $end_date = '';
    public $department_id = '';

    public $selectedRequisition;
    public $suppliedQuantities = [];
    public $comment = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
    }

    public function updatingDepartmentId(): void
    {
        $this->resetPage();
    }

    public function getRoleStatus(): ?string
    {
        $role = Auth::user()->role;

        if ($role === 'assistant_director') {
            return 'initiator_checked';
        }

        if ($role === 'deputy_director') {
            return 'ad_approved';
        }

        if ($role === 'director') {
            if (setting('store_mode', 'departmental') === 'centralized'
                && (int) Auth::user()->department_id !== (int) setting('central_store_dept_id', 1)) {
                return 'department_director_review';
            }

            return 'dd_approved';
        }

        return null;
    }

    public function viewRequisition($id)
    {
        $this->selectedRequisition = Requisition::with(['user.department', 'items.product'])
            ->forUserDepartment()
            ->findOrFail($id);

        $this->suppliedQuantities = [];
        foreach ($this->selectedRequisition->items as $item) {
            $this->suppliedQuantities[$item->id] = $item->supplied_qty;
        }
        $this->comment = '';
        Flux::modal('view-action-modal')->show();
    }

    public function processAction($actionType): void
    {
        $role = Auth::user()->role;
        $nextStatus = '';

        if ($actionType === 'return') {
            $nextStatus = 'returned';
            $msg = 'রিকুইজিশনটি Initiator-এর কাছে ফেরত পাঠানো হয়েছে!';
        } else {
            if ($role === 'assistant_director') {
                $nextStatus = 'ad_approved';
            }

            if ($role === 'deputy_director') {
                $nextStatus = 'dd_approved';
            }

            if ($role === 'director') {
                $nextStatus = $this->selectedRequisition->status === 'department_director_review'
                    ? 'pending'
                    : 'director_approved';
            }

            $msg = 'রিকুইজিশনটি সফলভাবে অনুমোদিত হয়েছে!';

            foreach ($this->selectedRequisition->items as $item) {
                if (isset($this->suppliedQuantities[$item->id])) {
                    $item->update(['supplied_qty' => $this->suppliedQuantities[$item->id]]);
                }
            }

            $targetRole = '';
            $message = "নতুন রিকুইজিশন ({$this->selectedRequisition->requisition_no}) আপনার অনুমোদনের অপেক্ষায় আছে।";
            $url = route('workflow.approval');

            if ($nextStatus === 'ad_approved') {
                $targetRole = 'deputy_director';
            } elseif ($nextStatus === 'dd_approved') {
                $targetRole = 'director';
            } elseif ($nextStatus === 'pending') {
                $targetRole = 'initiator';
                $message = "রিকুইজিশন ({$this->selectedRequisition->requisition_no}) সেন্ট্রাল স্টোরে যাচাইয়ের অপেক্ষায় আছে।";
                $url = route('workflow.initiator');
            } elseif ($nextStatus === 'director_approved') {
                $targetRole = 'initiator';
                $message = "রিকুইজিশন ({$this->selectedRequisition->requisition_no}) প্রিন্ট ও বিতরণের জন্য প্রস্তুত।";
                $url = route('workflow.initiator');
            } elseif ($nextStatus === 'returned') {
                $targetRole = 'initiator';
                $message = "রিকুইজিশন ({$this->selectedRequisition->requisition_no}) আপনার কাছে ফেরত এসেছে।";
                $url = route('workflow.initiator');
            }

            if ($targetRole) {
                $approvingDeptId = $nextStatus === 'pending'
                    ? (int) setting('central_store_dept_id', 1)
                    : Department::getApprovingDepartmentId($this->selectedRequisition->user->department_id);

                $targetUsers = User::where('role', $targetRole)
                    ->where('department_id', $approvingDeptId)
                    ->get();

                if ($targetUsers->isNotEmpty()) {
                    Notification::send($targetUsers, new RequisitionNotification($this->selectedRequisition, $message, $url));
                }
            }
        }

        $history = $this->selectedRequisition->approval_history ?? [];
        $history[] = [
            'role' => $role,
            'name' => Auth::user()->name,
            'action' => $actionType,
            'comment' => $this->comment,
            'date' => now()->toDateTimeString(),
            'signature' => Auth::user()->digital_signature,
        ];

        $this->selectedRequisition->update([
            'status' => $nextStatus,
            'approval_history' => $history,
        ]);

        Flux::toast($msg);
        Flux::modal('view-action-modal')->close();
        $this->selectedRequisition = null;
    }

    public function render()
    {
        $status = $this->getRoleStatus();

        $query = Requisition::with(['user.department', 'items.product'])
            ->forUserDepartment();

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->whereRaw('1 = 0');
        }

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('requisition_no', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('pf_no', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // whereBetween এর পরিবর্তে whereDate ব্যবহার করা হলো তারিখজনিত এরর এড়াতে
        if (! empty($this->start_date) && ! empty($this->end_date)) {
            $query->whereDate('created_at', '>=', $this->start_date)
                ->whereDate('created_at', '<=', $this->end_date);
        }

        if (! empty($this->department_id)) {
            $query->whereHas('user', function ($q) {
                $q->where('department_id', $this->department_id);
            });
        }

        $departments = Department::orderBy('name')->get();

        return view('livewire.workflow.approval-queue', [
            'requisitions' => $query->latest()->paginate(10),
            'departments' => $departments,
        ])->layout('layouts.app', ['title' => 'My Requisitions']);
    }
}
