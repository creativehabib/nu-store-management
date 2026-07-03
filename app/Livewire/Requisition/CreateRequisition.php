<?php

namespace App\Livewire\Requisition;

use App\Models\Category;
use App\Models\Product;
use App\Models\Requisition;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateRequisition extends Component
{
    public $requisitionItems = [];

    public $categories = [];
    public $selectedCategories = [];

    public function mount()
    {
        $this->categories = Category::orderBy('name')->get();

        $this->requisitionItems = [];
        $this->selectedCategories = [];

        $this->addRow();
    }

    public function addRow()
    {
        $this->requisitionItems[] = [
            'product_id' => '',
            'demanded_qty' => 1,
            'purpose' => 'Official Use',
        ];

        $this->selectedCategories[] = '';
    }

    public function removeRow($index): void
    {
        unset($this->requisitionItems[$index]);
        unset($this->selectedCategories[$index]);
        $this->requisitionItems = array_values($this->requisitionItems);
        $this->selectedCategories = array_values($this->selectedCategories);
    }

    public function submitDemand(): void
    {
        $this->validate([
            'selectedCategories.*' => 'required|exists:categories,id',

            'requisitionItems.*.product_id' => 'required|exists:products,id',
            'requisitionItems.*.demanded_qty' => 'required|integer|min:1',
            'requisitionItems.*.purpose' => 'required|in:Training Purpose,Official Use',
        ]);

        DB::transaction(function () {
            $requisition = Requisition::create([
                'requisition_no' => 'REQ-'.date('Ymd').'-'.strtoupper(Str::random(4)),
                'user_id' => Auth::id(),
                'status' => Requisition::initialStatus(Auth::user()?->department_id),
                'approval_history' => [],
            ]);

            foreach ($this->requisitionItems as $item) {
                $requisition->items()->create([
                    'product_id' => $item['product_id'],
                    'demanded_qty' => $item['demanded_qty'],
                    'supplied_qty' => 0,
                    'purpose' => $item['purpose'],
                ]);
            }
        });

        Flux::toast('চাহিদা সফলভাবে জমা দেওয়া হয়েছে!');
        $this->dispatch('workflow-queue-updated');
        $this->requisitionItems = [];
        $this->selectedCategories = [];
        $this->addRow();
    }

    public function render()
    {
        return view('livewire.requisition.create-requisition', [
            'getProducts' => function ($index) {
                $catId = $this->selectedCategories[$index] ?? null;

                return $catId ? Product::where('category_id', $catId)->orderBy('name_bn')->get() : [];
            },
        ])->layout('layouts.app', ['title' => 'Create Requisition']);
    }
}
