<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\StockEntry;
use Illuminate\Support\Facades\DB;
use Flux\Flux;

class StockInManager extends Component
{
    use WithPagination;

    public $entryId, $product_id, $quantity, $voucher_no, $supplier, $expire_date;
    public $isEditMode = false;

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'voucher_no' => 'nullable|string|max:100',
        'supplier' => 'nullable|string|max:255',
        'expire_date' => 'nullable|date',
    ];

    public function saveStock()
    {
        $this->validate();

        DB::transaction(function () {
            if ($this->isEditMode) {
                // এডিট মোড লজিক
                $oldEntry = StockEntry::findOrFail($this->entryId);

                // ১. আগের প্রোডাক্টের স্টক থেকে পুরোনো পরিমাণ মাইনাস করা
                $oldProduct = Product::find($oldEntry->product_id);
                if ($oldProduct) {
                    $oldProduct->decrement('stock', $oldEntry->quantity);
                }

                // ২. এন্ট্রি ডাটা আপডেট করা
                $oldEntry->update([
                    'product_id' => $this->product_id,
                    'quantity' => $this->quantity,
                    'voucher_no' => $this->voucher_no,
                    'supplier' => $this->supplier,
                    'expire_date' => $this->expire_date,
                ]);

                // ৩. নতুন (বা একই) প্রোডাক্টের স্টকে নতুন পরিমাণ যোগ করা
                $newProduct = Product::find($this->product_id);
                if ($newProduct) {
                    $newProduct->increment('stock', $this->quantity);
                }

                Flux::toast('স্টক এন্ট্রি সফলভাবে আপডেট করা হয়েছে!');
            } else {
                // নতুন তৈরি করার লজিক
                StockEntry::create([
                    'product_id' => $this->product_id,
                    'quantity' => $this->quantity,
                    'voucher_no' => $this->voucher_no,
                    'supplier' => $this->supplier,
                    'expire_date' => $this->expire_date,
                ]);

                $product = Product::find($this->product_id);
                $product->increment('stock', $this->quantity);

                Flux::toast('নতুন স্টক সফলভাবে বৃদ্ধি করা হয়েছে!');
            }
        });

        $this->resetFields();
    }

    public function edit($id)
    {
        $entry = StockEntry::findOrFail($id);

        $this->entryId = $entry->id;
        $this->product_id = $entry->product_id;
        $this->quantity = $entry->quantity;
        $this->voucher_no = $entry->voucher_no;
        $this->supplier = $entry->supplier;
        $this->expire_date = $entry->expire_date;

        $this->isEditMode = true;
    }

    public function deleteEntry($id)
    {
        DB::transaction(function () use ($id) {
            $entry = StockEntry::findOrFail($id);

            // ডিলিট করার আগে মূল প্রোডাক্টের স্টক থেকে এই পরিমাণ মাইনাস করে দিতে হবে
            $product = Product::find($entry->product_id);
            if ($product) {
                $product->decrement('stock', $entry->quantity);
            }

            $entry->delete();
            Flux::toast('স্টক এন্ট্রি মুছে ফেলা হয়েছে এবং মূল স্টক রিভার্স করা হয়েছে!');
        });
    }

    public function resetFields()
    {
        $this->reset(['entryId', 'product_id', 'quantity', 'voucher_no', 'supplier', 'expire_date', 'isEditMode']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.inventory.stock-in-manager', [
            'products' => Product::orderBy('name_bn')->get(),
            'stockEntries' => StockEntry::with('product.category')->latest()->paginate(10)
        ])->layout('layouts.app', ['title' => 'Stock Manager']);
    }
}
