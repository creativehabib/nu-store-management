<?php

namespace App\Livewire\Workflow;

use Livewire\Component;
use Livewire\WithPagination; // পেজিনেশনের জন্য যুক্ত করা হলো
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Flux\Flux;

class ApprovalQueue extends Component
{
    use WithPagination; // ট্রেইট ব্যবহার করা হলো

    // সার্চ এবং ফিল্টারের প্রোপার্টিসমূহ
    public $search = '';
    public $start_date = '';
    public $end_date = '';
    public $department = '';

    public $selectedRequisition;
    public $suppliedQuantities = [];
    public $comment = '';

    // ফিল্টার চেঞ্জ হলে পেজিনেশন পেজ রিসেট করার জন্য লজিক
    public function updatingSearch() { $this->resetPage(); }
    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }
    public function updatingDepartment() { $this->resetPage(); }

    // ইউজারের রোল অনুযায়ী কোন স্ট্যাটাসের ডাটা আসবে তা নির্ধারণ
    public function getRoleStatus()
    {
        $role = Auth::user()->role;
        if ($role === 'assistant_director') return 'initiator_checked';
        if ($role === 'deputy_director') return 'ad_approved';
        if ($role === 'director') return 'dd_approved';
        return null;
    }

    public function viewRequisition($id)
    {
        $this->selectedRequisition = Requisition::with(['user', 'items.product'])->findOrFail($id);

        $this->suppliedQuantities = [];
        foreach ($this->selectedRequisition->items as $item) {
            $this->suppliedQuantities[$item->id] = $item->supplied_qty;
        }
        $this->comment = '';
        Flux::modal('view-action-modal')->show();
    }

    public function processAction($actionType)
    {
        $role = Auth::user()->role;
        $nextStatus = '';

        if ($actionType === 'return') {
            $nextStatus = 'returned';
            $msg = 'রিকুইজিশনটি Initiator-এর কাছে ফেরত পাঠানো হয়েছে!';
        } else {
            if ($role === 'assistant_director') $nextStatus = 'ad_approved';
            if ($role === 'deputy_director') $nextStatus = 'dd_approved';
            if ($role === 'director') $nextStatus = 'director_approved';
            $msg = 'রিকুইজিশনটি সফলভাবে অনুমোদিত হয়েছে!';

            foreach ($this->selectedRequisition->items as $item) {
                if (isset($this->suppliedQuantities[$item->id])) {
                    $item->update(['supplied_qty' => $this->suppliedQuantities[$item->id]]);
                }
            }

            // ১. নোটিফিকেশন টার্গেট সেট করা
            $targetRole = '';
            $message = "নতুন রিকুইজিশন ({$this->selectedRequisition->requisition_no}) আপনার অনুমোদনের অপেক্ষায় আছে।";
            $url = route('workflow.approval'); // ডিফল্ট ইউআরএল

            if ($nextStatus === 'ad_approved') {
                $targetRole = 'deputy_director';
            } elseif ($nextStatus === 'dd_approved') {
                $targetRole = 'director';
            } elseif ($nextStatus === 'director_approved') {
                $targetRole = 'initiator';
                $message = "রিকুইজিশন ({$this->selectedRequisition->requisition_no}) প্রিন্ট ও বিতরণের জন্য প্রস্তুত।";
                $url = route('workflow.initiator'); // ইনিশিয়েটরের কিউতে পাঠাবে
            } elseif ($nextStatus === 'returned') {
                $targetRole = 'initiator';
                $message = "রিকুইজিশন ({$this->selectedRequisition->requisition_no}) আপনার কাছে ফেরত এসেছে।";
                $url = route('workflow.initiator');
            }

            // ২. টার্গেট ইউজারের কাছে নোটিফিকেশন পাঠানো
            if ($targetRole) {
                $targetUsers = User::where('role', $targetRole)->get();
                \Illuminate\Support\Facades\Notification::send($targetUsers, new \App\Notifications\RequisitionNotification($this->selectedRequisition, $message, $url));
            }
        }

        $history = $this->selectedRequisition->approval_history ?? [];
        $history[] = [
            'role' => $role,
            'name' => Auth::user()->name,
            'action' => $actionType,
            'comment' => $this->comment,
            'date' => now()->toDateTimeString(),
            'signature' => Auth::user()->digital_signature
        ];

        $this->selectedRequisition->update([
            'status' => $nextStatus,
            'approval_history' => $history
        ]);

        Flux::toast($msg);
        Flux::modal('view-action-modal')->close();
        $this->selectedRequisition = null;
    }

    public function render()
    {
        $status = $this->getRoleStatus();

        // রিলেশনসহ বেস কোয়েরি বিল্ড করা
        $query = Requisition::with(['user', 'items.product']);

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->whereRaw('1 = 0'); // রোল না মিললে খালি দেখাবে
        }

        // ১. সার্চ ফিল্টার লজিক (রিকুইজিশন নম্বর, নাম অথবা PF No দিয়ে সার্চ)
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('requisition_no', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function($userQuery) {
                        $userQuery->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('pf_no', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // ২. ডেট রেঞ্জ ফিল্টার লজিক
        if (!empty($this->start_date) && !empty($this->end_date)) {
            $query->whereBetween('created_at', [$this->start_date . ' 00:00:00', $this->end_date . ' 23:59:59']);
        }

        // ৩. ডিপার্টমেন্ট ফিল্টার লজিক
        if (!empty($this->department)) {
            $query->whereHas('user', function($q) {
                $q->where('department', $this->department);
            });
        }

        // ড্রপডাউনে দেখানোর জন্য ইউনিক ডিপার্টমেন্টের লিস্ট তৈরি
        $departments = User::whereNotNull('department')->distinct()->pluck('department');

        return view('livewire.workflow.approval-queue', [
            'requisitions' => $query->latest()->paginate(10), // প্রতি পেজে ১০টি করে আসবে
            'departments' => $departments
        ]);
    }
}
