<?php

namespace App\Livewire\Report;

use Livewire\Component;
use App\Models\Requisition;
use App\Models\User;

class ReportManager extends Component
{
    public $start_date;
    public $end_date;
    public $department = '';
    public $status = '';

    public function mount()
    {
        // ডিফল্টভাবে চলতি মাসের প্রথম এবং শেষ তারিখ সেট করা
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
    }

    // ফিল্টার করা ডাটা আনার মেথড
    public function getFilteredData()
    {
        $query = Requisition::with(['user', 'items.product']);

        if ($this->start_date && $this->end_date) {
            $query->whereBetween('created_at', [$this->start_date . ' 00:00:00', $this->end_date . ' 23:59:59']);
        }

        if (!empty($this->department)) {
            $query->whereHas('user', function($q) {
                $q->where('department', $this->department);
            });
        }

        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        return $query->latest()->get();
    }

    // CSV/Excel ফাইলে এক্সপোর্ট করার মেথড
    public function exportCSV()
    {
        $data = $this->getFilteredData();
        $fileName = 'Store_Report_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            // বাংলা (UTF-8) ফন্ট যেন এক্সেলে ঠিকমতো সাপোর্ট করে, সেজন্য BOM যুক্ত করা হলো
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV এর হেডার বা কলামের নাম
            fputcsv($handle, ['তারিখ', 'রিকুইজিশন নং', 'আবেদনকারী', 'দপ্তর', 'স্ট্যাটাস', 'মোট চাহিদাকৃত আইটেম', 'মোট সরবরাহকৃত আইটেম']);

            foreach ($data as $req) {
                $demanded = $req->items->sum('demanded_qty');
                $supplied = $req->items->sum('supplied_qty');

                fputcsv($handle, [
                    $req->created_at->format('d M, Y'),
                    $req->requisition_no,
                    $req->user->name,
                    $req->user->department,
                    strtoupper($req->status),
                    $demanded,
                    $supplied
                ]);
            }
            fclose($handle);
        }, $fileName);
    }

    public function render()
    {
        // সিস্টেমে যতগুলো ইউনিক ডিপার্টমেন্ট আছে তার লিস্ট আনা হচ্ছে ড্রপডাউনের জন্য
        $departments = User::whereNotNull('department')->distinct()->pluck('department');
        $reportData = $this->getFilteredData();

        return view('livewire.report.report-manager', [
            'departments' => $departments,
            'reportData' => $reportData
        ]);
    }
}
