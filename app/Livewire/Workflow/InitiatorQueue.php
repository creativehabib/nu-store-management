<?php

namespace App\Livewire\Workflow;

use App\Models\Requisition;
use App\Notifications\RequisitionNotification;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class InitiatorQueue extends Component
{
    public $requisitions;

    public $selectedRequisition; // মডালে দেখানোর জন্য

    public $suppliedQuantities = []; // ডাইনামিক সরবরাহের পরিমাণ

    public $comment = '';

    public function mount()
    {
        $this->loadRequisitions();
    }

    public function loadRequisitions()
    {
        // আপডেট: এখন Initiator pending, returned, director_approved এবং distributed স্ট্যাটাসগুলোও দেখবে
        $this->requisitions = Requisition::with(['user', 'items.product'])
            ->whereIn('status', ['pending', 'returned', 'director_approved', 'distributed'])
            ->latest()
            ->get();
    }

    // একটি নির্দিষ্ট রিকুইজিশন ভিউ করা (মডাল ওপেন করার জন্য)
    public function viewRequisition($id)
    {
        $this->selectedRequisition = Requisition::with(['user', 'items.product'])->findOrFail($id);

        // ডিফল্টভাবে চাহিদার পরিমাণকে সরবরাহের পরিমাণ হিসেবে সেট করা হচ্ছে
        $this->suppliedQuantities = [];
        foreach ($this->selectedRequisition->items as $item) {
            $this->suppliedQuantities[$item->id] = $item->demanded_qty;
        }

        $this->comment = '';
    }

    // অনুমোদন করে পরবর্তী ধাপে পাঠানো
    public function forwardRequisition()
    {
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

        Flux::toast('রিকুইজিশনটি সফলভাবে পরবর্তী ধাপে (Assistant Director) পাঠানো হয়েছে!');

        // রিসেট করা
        $this->selectedRequisition = null;
        $this->loadRequisitions();

        $targetUsers = User::where('role', 'assistant_director')->get();
        $message = "নতুন রিকুইজিশন ({$this->selectedRequisition->requisition_no}) আপনার অনুমোদনের অপেক্ষায় আছে।";
        $url = route('workflow.approval');

        Notification::send($targetUsers, new RequisitionNotification($this->selectedRequisition, $message, $url));
    }

    public function render()
    {
        return view('livewire.workflow.initiator-queue');
    }
}
