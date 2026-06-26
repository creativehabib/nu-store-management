<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Livewire\Component;

class ProductSummaryReport extends Component
{
    public $startDate;
    public $endDate;
    public $categoryId = '';
    public $categories = [];

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::now()->endOfMonth()->toDateString();

        if (class_exists(Category::class)) {
            $this->categories = Category::orderBy('name')->get();
        }
    }

    public function getSummaryProperty()
    {
        $products = Product::with(['category'])
            ->withSum(['items as total_qty' => function ($query) {
                $query->whereHas('requisition', function ($q) {
                    if ($this->startDate) {
                        $q->whereDate('created_at', '>=', $this->startDate);
                    }
                    if ($this->endDate) {
                        $q->whereDate('created_at', '<=', $this->endDate);
                    }
                });
            }], 'demanded_qty')
            ->when($this->categoryId, function ($query) {
                $query->where('category_id', $this->categoryId);
            })
            ->get();

        return $products->groupBy(function ($product) {
            return $product->category->name ?? 'Uncategorized';
        });
    }

    // টপ কার্ডগুলোর ডাটা ক্যালকুলেট করার জন্য নতুন প্রপার্টি
    public function getStatsProperty()
    {
        $summary = $this->summary;
        $totalDemanded = 0;
        $shortageCount = 0;

        foreach ($summary as $products) {
            foreach ($products as $product) {
                $totalDemanded += ($product->total_qty ?? 0);

                // যদি চাহিদা স্টকের চেয়ে বেশি হয়, তবে সেটি Shortage
                if (($product->total_qty ?? 0) > ($product->stock ?? 0)) {
                    $shortageCount++;
                }
            }
        }

        return [
            'total_demanded' => $totalDemanded,
            'shortage_count' => $shortageCount,
            'active_categories' => $summary->count(),
        ];
    }

    // CSV এক্সপোর্ট করার মেথড
    public function exportToCSV()
    {
        $fileName = 'product_summary_' . now()->format('Y_m_d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM সাপোর্ট

            fputcsv($file, ['Category', 'Product Name', 'Current Stock', 'Total Demanded', 'Status']);

            foreach ($this->summary as $categoryName => $products) {
                foreach ($products as $product) {
                    $status = (($product->total_qty ?? 0) > ($product->stock ?? 0)) ? 'Shortage' : 'OK';

                    fputcsv($file, [
                        $categoryName,
                        $product->name_bn ?? $product->name,
                        $product->stock ?? 0,
                        $product->total_qty ?? 0,
                        $status
                    ]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.admin.product-summary-report', [
            'summaryGroups' => $this->summary,
            'stats' => $this->stats, // কার্ডের ডাটা ব্লেডে পাঠানো হলো
        ])->layout('layouts.app', ['title' => 'Product Summary Report']);
    }
}
