<?php

namespace App\Livewire\Report;

use App\Models\Department;
use App\Models\Requisition;
use Livewire\Component;

class ReportManager extends Component
{
    public $start_date;

    public $end_date;

    public $department_id = '';

    public $status = '';

    public function mount()
    {
        // ডিফল্টভাবে চলতি মাসের ১ তারিখ থেকে শেষ তারিখ পর্যন্ত সেট করা
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
    }

    public function getFilteredData()
    {
        // ১. গ্লোবাল স্কোপ: সেন্ট্রাল স্টোর এবং সাধারণ ইউজারের ডাটা দেখার এক্সেস কন্ট্রোল করবে
        $query = Requisition::with(['user.department', 'items.product'])
            ->forUserDepartment();

        // ২. ড্রপডাউন থেকে নির্দিষ্ট ডিপার্টমেন্ট সিলেক্ট করা হলে ফিল্টার হবে
        if (!empty($this->department_id)) {
            $query->whereHas('user', function ($q) {
                $q->where('department_id', $this->department_id);
            });
        }

        // ৩. তারিখ ফিল্টার (whereBetween দিয়ে ফিক্স করা হলো, যাতে সময়সহ ডাটা মিস না হয়)
        if (!empty($this->start_date) && !empty($this->end_date)) {
            $query->whereBetween('created_at', [
                $this->start_date . ' 00:00:00',
                $this->end_date . ' 23:59:59'
            ]);
        }

        // ৪. স্ট্যাটাস ফিল্টার
        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        return $query->latest()->get();
    }
    public function getStockReport($type = 'in') // type: 'in' or 'out'
    {
        $query = \App\Models\Requisition::query()
            ->when($type === 'in', function ($q) {
                // স্টক ইন এর লজিক (আপনার সিস্টেম অনুযায়ী এটি 'purchases' বা 'stock_additions' টেবিল হতে পারে)
                return $q->where('status', 'stock_received');
            })
            ->when($type === 'out', function ($q) {
                // স্টক আউট এর লজিক (পণ্য বিতরণ বা distributed)
                return $q->where('status', 'distributed');
            })
            ->whereBetween('created_at', [$this->start_date . ' 00:00:00', $this->end_date . ' 23:59:59'])
            ->get();

        return $query;
    }

    public function resetFilters(): void
    {
        // ডিফল্ট মানগুলোতে রিস্টোর করা হচ্ছে
        $this->department_id = '';
        $this->status = '';
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
    }
    public function exportCSV()
    {
        $data = $this->getFilteredData();
        $fileName = 'Store_Report_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM যুক্ত করা হলো যাতে বাংলা অক্ষর ঠিকমতো সাপোর্ট করে
            fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['তারিখ', 'রিকুইজিশন নং', 'আবেদনকারী', 'দপ্তর', 'স্ট্যাটাস', 'মোট চাহিদাকৃত আইটেম', 'মোট সরবরাহকৃত আইটেম']);

            foreach ($data as $req) {
                fputcsv($handle, [
                    $req->created_at->format('d M, Y'),
                    $req->requisition_no,
                    $req->user->name ?? 'N/A',
                    $req->user->department->name ?? 'N/A',
                    strtoupper($req->status),
                    $req->items->sum('demanded_qty'),
                    $req->items->sum('supplied_qty'),
                ]);
            }
            fclose($handle);
        }, $fileName);
    }

    public function render()
    {
        return view('livewire.report.report-manager', [
            'departments' => Department::orderBy('name')->get(),
            'reportData' => $this->getFilteredData(),
        ])->layout('layouts.app', ['title' => 'Report Manager']);
    }
}
