<?php

namespace App\Livewire\Workflow;

use Livewire\Component;
use App\Models\Requisition;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Flux\Flux;

class FinalPrint extends Component
{
    public $requisition;

    public function mount($id)
    {
        // রিকুইজিশনের যাবতীয় ডাটা রিলেশনশিপসহ লোড করা হচ্ছে
        $this->requisition = Requisition::with(['user', 'items.product'])->findOrFail($id);
    }

    // Approval History থেকে ইউজারের ডিজিটাল সিগনেচার বের করার মেথড
    public function getSignature($role)
    {
        $history = $this->requisition->approval_history ?? [];
        foreach ($history as $h) {
            if ($h['role'] === $role && isset($h['signature'])) {
                return asset('storage/' . $h['signature']); // সিগনেচারের পাবলিক URL
            }
        }
        return null;
    }

    // প্রোডাক্ট ডিস্ট্রিবিউশন এবং স্টক মাইনাস লজিক
    public function distributeStock()
    {
        if ($this->requisition->status !== 'director_approved') {
            Flux::toast('রিকুইজিশনটি এখনো চূড়ান্ত অনুমোদন পায়নি বা ইতিমধ্যে বিতরণ হয়েছে!', 'error');
            return;
        }

        // DB Transaction ব্যবহার করা হচ্ছে যেন কোনো এরর হলে অর্ধেক ডাটা সেভ না হয়
        DB::transaction(function () {
            foreach ($this->requisition->items as $item) {
                $product = Product::find($item->product_id);
                if ($product && $product->stock >= $item->supplied_qty) {
                    $product->decrement('stock', $item->supplied_qty); // স্টক মাইনাস
                }
            }

            // স্ট্যাটাস আপডেট করে Distributed করে দেওয়া হলো
            $this->requisition->update(['status' => 'distributed']);
        });

        Flux::toast('সফলভাবে স্টক মাইনাস করা হয়েছে এবং পণ্য বিতরণ সম্পন্ন হয়েছে!');

        // কম্পোনেন্ট রিলোড করার জন্য
        $this->requisition->refresh();
    }

    public function render()
    {
        return view('livewire.workflow.final-print');
    }
}
