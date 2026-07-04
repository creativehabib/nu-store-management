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

    public string $search = '';

    public string $statusFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    // মডাল ওপেন করে নির্দিষ্ট রিকুইজিশনের ডিটেইলস ও হিস্ট্রি দেখা
    public function viewHistory($id): void
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
        $baseQuery = Requisition::where('user_id', Auth::id());

        $requisitions = (clone $baseQuery)
            ->with(['items.product'])
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->search !== '', function ($query) {
                $query->where(function ($requisitionQuery) {
                    $requisitionQuery->where('requisition_no', 'like', '%'.$this->search.'%')
                        ->orWhereHas('items.product', function ($productQuery) {
                            $productQuery->where('name_bn', 'like', '%'.$this->search.'%')
                                ->orWhere('name_en', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.requisition.my-requisitions', [
            'requisitions' => $requisitions,
            'totalRequisitions' => (clone $baseQuery)->count(),
            'inProgressRequisitions' => (clone $baseQuery)->whereNotIn('status', ['distributed', 'returned'])->count(),
            'completedRequisitions' => (clone $baseQuery)->where('status', 'distributed')->count(),
            'returnedRequisitions' => (clone $baseQuery)->where('status', 'returned')->count(),
        ])->layout('layouts.app', ['title' => 'My Requisitions']);
    }
}
