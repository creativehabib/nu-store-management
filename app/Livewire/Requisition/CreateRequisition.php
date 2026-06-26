<?php

namespace App\Livewire\Requisition;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Flux\Flux;

class CreateRequisition extends Component
{
    public $requisitionItems = [];
    public $categories = [];

    // প্রতিটি রো-এর ক্যাটাগরি ট্র্যাক করার জন্য একটি অ্যারে
    public $selectedCategories = [];

    public function mount()
    {
        $this->categories = Category::orderBy('name')->get();
        $this->selectedCategories = []; // নিশ্চিত করছি এটি একদম খালি
        $this->addRow();
    }

    public function addRow()
    {
        $this->requisitionItems[] = [
            'product_id' => '',
            'demanded_qty' => 1,
            'purpose' => 'Official Use'
        ];
        $this->selectedCategories[count($this->requisitionItems) - 1] = null;
    }

    public function removeRow($index)
    {
        unset($this->requisitionItems[$index]);
        unset($this->selectedCategories[$index]);
        $this->requisitionItems = array_values($this->requisitionItems);
        $this->selectedCategories = array_values($this->selectedCategories);
    }

    public function submitDemand()
    {
        $this->validate([
            'requisitionItems.*.product_id' => 'required|exists:products,id',
            'requisitionItems.*.demanded_qty' => 'required|integer|min:1',
            'requisitionItems.*.purpose' => 'required|in:Training Purpose,Official Use',
        ]);

        DB::transaction(function () {
            $requisition = Requisition::create([
                'requisition_no' => 'REQ-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'user_id' => Auth::id(),
                'status' => 'pending',
                'approval_history' => []
            ]);

            foreach ($this->requisitionItems as $item) {
                $requisition->items()->create([
                    'product_id' => $item['product_id'],
                    'demanded_qty' => $item['demanded_qty'],
                    'supplied_qty' => 0,
                    'purpose' => $item['purpose']
                ]);
            }
        });

        Flux::toast('চাহিদা সফলভাবে জমা দেওয়া হয়েছে!');
        $this->requisitionItems = [];
        $this->selectedCategories = [];
        $this->addRow();
    }

    public function render()
    {
        return view('livewire.requisition.create-requisition', [
            // প্রতিটি রো-এর জন্য ক্যাটাগরি অনুযায়ী প্রোডাক্ট ফিল্টার করা
            'getProducts' => function ($index) {
                $catId = $this->selectedCategories[$index] ?? null;
                return $catId ? Product::where('category_id', $catId)->orderBy('name_bn')->get() : [];
            }
        ])->layout('layouts.app', ['title' => 'Create Requisition']);
    }
}
