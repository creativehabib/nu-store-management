<?php

namespace App\Livewire\Requisition;

use Livewire\Component;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Flux\Flux;

class CreateRequisition extends Component
{
    public $products = [];
    public $requisitionItems = [];

    public function mount()
    {
        $this->products = Product::orderBy('name_bn')->get();
        $this->addRow();
    }

    public function addRow()
    {
        $this->requisitionItems[] = [
            'product_id' => '',
            'demanded_qty' => 1,
            'purpose' => 'Official Use'
        ];
    }

    public function removeRow($index)
    {
        unset($this->requisitionItems[$index]);
        $this->requisitionItems = array_values($this->requisitionItems);
    }

    public function submitDemand()
    {
        $this->validate([
            'requisitionItems.*.product_id' => 'required',
            'requisitionItems.*.demanded_qty' => 'required|integer|min:1',
            'requisitionItems.*.purpose' => 'required|in:Training Purpose,Official Use',
        ]);

        $requisition = Requisition::create([
            'requisition_no' => 'REQ-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
            'user_id' => Auth::id(),
            'status' => 'pending',
            'approval_history' => []
        ]);

        foreach ($this->requisitionItems as $item) {
            RequisitionItem::create([
                'requisition_id' => $requisition->id,
                'product_id' => $item['product_id'],
                'demanded_qty' => $item['demanded_qty'],
                'supplied_qty' => $item['demanded_qty'],
                'purpose' => $item['purpose']
            ]);
        }

        Flux::toast('চাহিদা সফলভাবে জমা দেওয়া হয়েছে!');

        $this->requisitionItems = [];
        $this->addRow();
    }

    public function render()
    {
        return view('livewire.requisition.create-requisition');
    }
}
