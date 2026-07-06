<?php

namespace App\Livewire\Dashboard;

use App\Models\Category;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\User;
use App\Support\ApprovalWorkflow;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardStats extends Component
{
    public $trendFilter = '30'; // ডিফল্ট ৩০ দিন

    // ফিল্টার ড্রপডাউন পরিবর্তন হলে চার্ট আপডেট করার ইভেন্ট ফায়ার করবে
    public function updatedTrendFilter()
    {
        $data = $this->getTrendData();
        $this->dispatch('update-trend-chart', labels: $data['labels'], values: $data['values']);
    }

    private function getQueueStatus($role): array
    {
        if ($role === 'initiator') {
            return ['pending', 'returned', 'director_approved', 'distributed'];
        }
        $status = ApprovalWorkflow::statusForRole($role);

        return $status ? [$status] : [];
    }

    // ফিল্টার অনুযায়ী ডাটা বের করার হেল্পার মেথড (Database Agnostic)
    private function getTrendData(): array
    {
        // 'All Time' ফিল্টার হলে মাসিক (Monthly) ডাটা দেখাবে
        if ($this->trendFilter === 'all') {
            $requisitions = Requisition::forUserDepartment()
                ->select('id', 'created_at')
                ->orderBy('created_at', 'asc')
                ->get();

            // লারাভেল কালেকশন ব্যবহার করে মাস অনুযায়ী গ্রুপ করা
            $trendData = $requisitions->groupBy(function ($item) {
                return $item->created_at->format('Y-m'); // '2026-07' ফরম্যাটে গ্রুপ
            })->map(function ($group) {
                return $group->count();
            });

            $labels = [];
            $values = [];

            foreach ($trendData as $month => $total) {
                $labels[] = Carbon::parse($month . '-01')->format('M Y');
                $values[] = $total;
            }

            return ['labels' => $labels, 'values' => $values];
        }

        // ৭, ১৫ বা ৩০ দিনের জন্য প্রতিদিনের (Daily) ডাটা দেখাবে
        $days = (int) $this->trendFilter;
        $startDate = now()->subDays($days - 1)->startOfDay();

        $trendData = clone Requisition::forUserDepartment()
            ->select('id', 'created_at')
            ->where('created_at', '>=', $startDate)
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            })
            ->map(function ($group) {
                return $group->count();
            });

        $labels = [];
        $values = [];

        // খালি দিনগুলোর জন্য 0 বসিয়ে ডাটা তৈরি করা
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d M'); // যেমন: 01 Jul
            $values[] = $trendData->get($date, 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function render()
    {
        $user = Auth::user();
        $role = $user->role;
        $stats = [];

        // কার্ডস ডাটা (অ্যাডমিন এবং ডিরেক্টর উভয়ই দেখবে)
        if (in_array($role, ['admin', 'super_admin', 'director'])) {
            $stats['total_users'] = User::count();
            $stats['pending_users'] = User::where('is_approved', false)->count();
            $stats['total_products'] = Product::count();
            $stats['low_stock'] = Product::where('stock', '<=', 10)->count();
        }

        if ($role === 'requisitioner') {
            $stats['total_submitted'] = Requisition::where('user_id', $user->id)->count();
            $stats['pending'] = Requisition::where('user_id', $user->id)->where('status', '!=', 'distributed')->count();
            $stats['distributed'] = Requisition::where('user_id', $user->id)->where('status', 'distributed')->count();
            $stats['returned'] = Requisition::where('user_id', $user->id)->where('status', 'returned')->count();
        }

        if (in_array($role, ['initiator', 'assistant_director', 'deputy_director', 'director'])) {
            $queueStatuses = $this->getQueueStatus($role);
            $stats['pending_action'] = Requisition::forUserDepartment()->whereIn('status', ['pending', 'returned'])->count();
            $stats['ready_to_print'] = Requisition::forUserDepartment()->whereIn('status', ['director_approved', 'distributed'])->count();
            $stats['pending_approval'] = Requisition::forUserDepartment()->whereIn('status', $queueStatuses)->count();
            $stats['total_requisitions'] = Requisition::forUserDepartment()->count();

            if ($role === 'initiator') {
                $stats['stock_out_products'] = Product::where('stock', '<=', 0)->count();
            }
        }

        $trendData = $this->getTrendData();

        return view('livewire.dashboard.dashboard-stats', [
            'role' => $role,
            'stats' => $stats,
            'trendLabels' => $trendData['labels'],
            'trendValues' => $trendData['values'],
            'categoryLabels' => Category::pluck('name'),
            'categoryValues' => Category::withCount('products')->pluck('products_count'),
            'recentRequisitions' => Requisition::with(['user.department'])->forUserDepartment()->latest()->take(5)->get(),
            'myOwnRequisitions' => Requisition::where('user_id', $user->id)->latest()->take(5)->get(),
        ])->layout('layouts.app', ['title' => __('Dashboard')]);
    }
}
