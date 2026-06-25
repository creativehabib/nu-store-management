<?php

namespace App\Livewire\Workflow;

use App\Models\Requisition;
use App\Models\User;
use App\Notifications\RequisitionNotification;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class InitiatorQueue extends Component
{
    public $requisitions;
    public ?Requisition $selectedRequisition = null;

    public $suppliedQuantities = [];

    public $comment = '';

    public function mount()
    {
        $this->loadRequisitions();
    }

    public function loadRequisitions(): void
    {
        // Initiator pending, returned, director_approved এবং distributed স্ট্যাটাসগুলোও দেখবে
        $this->requisitions = Requisition::with(['user', 'items.product'])
            ->whereIn('status', ['pending', 'returned', 'director_approved', 'distributed'])
            ->latest()
            ->get();
    }

    // একটি নির্দিষ্ট রিকুইজিশন ভিউ করা (মডাল ওপেন করার জন্য)
    public function viewRequisition($id): void
    {
        // findOrFail ব্যবহার করা হলো যাতে ডাটা না থাকলে আগে থেকেই আটকে দেয়
        $this->selectedRequisition = Requisition::with(['user', 'items.product'])->findOrFail($id);

        // ডিফল্টভাবে চাহিদার পরিমাণকে সরবরাহের পরিমাণ হিসেবে সেট করা হচ্ছে
        $this->suppliedQuantities = [];
        foreach ($this->selectedRequisition->items as $item) {
            $this->suppliedQuantities[$item->id] = $item->demanded_qty;
        }

        $this->comment = '';
        Flux::modal('view-action-modal')->show();
    }

    // অনুমোদন করে পরবর্তী ধাপে পাঠানো
    public function forwardRequisition(): void
    {
        // সিকিউরিটি চেক
        if (! $this->selectedRequisition) {
            Flux::toast(__('Requisition data not found! Please refresh the page.'), variant: 'danger');

            return;
        }

        // ১. প্রতিটি আইটেমের সরবরাহের পরিমাণ আপডেট করা
        foreach ($this->selectedRequisition->items as $item) {
            if (isset($this->suppliedQuantities[$item->id])) {
                $item->update([
                    'supplied_qty' => $this->suppliedQuantities[$item->id],
                ]);
            }
        }

        // ২. Approval History তে কমেন্ট এবং সিগনেচার লজিক যুক্ত করা
        $history = $this->selectedRequisition->approval_history ?? [];
        $history[] = [
            'role' => 'initiator',
            'name' => Auth::user()->name,
            'action' => 'forwarded',
            'comment' => $this->comment,
            'date' => now()->toDateTimeString(),
            'signature' => Auth::user()->digital_signature, // ডিজিটাল সিগনেচারের পাথ
        ];

        // ৩. স্ট্যাটাস আপডেট করে AD এর কাছে পাঠানো
        $this->selectedRequisition->update([
            'status' => 'initiator_checked',
            'approval_history' => $history,
        ]);

        // ৪. নোটিফিকেশন পাঠানো (অবজেক্ট null করার আগেই এই কাজ করতে হবে)
        $targetUsers = User::where('role', 'assistant_director')->get();

        // ট্রান্সলেশন স্ট্রিং এর ভেতরে ডাইনামিক ভ্যারিয়েবল পাস করার নিয়ম
        $message = __('New requisition (:req_no) is waiting for your approval.', ['req_no' => $this->selectedRequisition->requisition_no]);

        $url = route('workflow.approval');

        Notification::send($targetUsers, new RequisitionNotification($this->selectedRequisition, $message, $url));

        // ৫. সাকসেস মেসেজ এবং মডাল ক্লোজ
        Flux::toast(__('Requisition successfully forwarded to Assistant Director!'));
        Flux::modal('view-action-modal')->close();

        // ৬. একদম শেষে রিসেট করা
        $this->selectedRequisition = null;
        $this->loadRequisitions();
    }

    public function render()
    {
        return view('livewire.workflow.initiator-queue');
    }
}
