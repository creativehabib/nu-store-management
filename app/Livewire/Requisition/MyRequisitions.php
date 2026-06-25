<?php

namespace App\Livewire\Requisition;

use App\Models\Requisition;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MyRequisitions extends Component
{
    use WithPagination;

    public $selectedRequisition;

    // মডাল ওপেন করে নির্দিষ্ট রিকুইজিশনের ডিটেইলস ও হিস্ট্রি দেখা
    public function viewHistory($id)
    {
        // নিজের রিকুইজিশন ছাড়া অন্য কারোটা যেন দেখতে না পারে, তাই user_id চেক করা হয়েছে
        $this->selectedRequisition = Requisition::with(['items.product'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        Flux::modal('history-modal')->show();
    }

    public function render()
    {
        // লগইন করা ইউজারের নিজের সব রিকুইজিশন
        $requisitions = Requisition::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('livewire.requisition.my-requisitions', [
            'requisitions' => $requisitions,
        ])->layout('layouts.app', ['title' => 'My Requisitions']);
    }
}
