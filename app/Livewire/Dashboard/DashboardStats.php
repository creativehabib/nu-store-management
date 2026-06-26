<?php

namespace App\Livewire\Dashboard;

use App\Models\Category;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardStats extends Component
{
    // ইউজারের রোল অনুযায়ী কিউ স্ট্যাটাস বের করার মেথড
    private function getQueueStatus($role): array
    {
        if ($role === 'initiator') {
            return ['pending', 'returned', 'director_approved', 'distributed'];
        }
        if ($role === 'assistant_director') {
            return ['initiator_checked'];
        }
        if ($role === 'deputy_director') {
            return ['ad_approved'];
        }
        if ($role === 'director') {
            return ['dd_approved'];
        }

        return [];
    }

    public function render()
    {
        $user = Auth::user();
        $role = $user->role;
        $stats = [];

        // --- আপনার পূর্বের স্ট্যাটিস্টিকস লজিক ---
        if ($role === 'admin') {
            $stats['total_users'] = User::count();
            $stats['pending_users'] = User::where('is_approved', false)->count();
            $stats['total_products'] = Product::count();
            $stats['low_stock'] = Product::where('stock', '<=', 10)->count();
        } elseif ($role === 'requisitioner') {
            $stats['total_submitted'] = Requisition::where('user_id', $user->id)->count();
            $stats['pending'] = Requisition::where('user_id', $user->id)->where('status', '!=', 'distributed')->count();
            $stats['distributed'] = Requisition::where('user_id', $user->id)->where('status', 'distributed')->count();
            $stats['returned'] = Requisition::where('user_id', $user->id)->where('status', 'returned')->count();
        } else {
            $queueStatuses = $this->getQueueStatus($role);
            if ($role === 'initiator') {
                $stats['pending_action'] = Requisition::whereIn('status', ['pending', 'returned'])->count();
                $stats['ready_to_print'] = Requisition::whereIn('status', ['director_approved', 'distributed'])->count();
            } else {
                $stats['pending_approval'] = Requisition::whereIn('status', $queueStatuses)->count();
            }
            $stats['total_requisitions'] = Requisition::count();
        }

        // --- নতুন চার্ট ডাটা লজিক ---

        // ১. গত ৬ মাসের মাসিক রিকুইজিশন ট্রেন্ড
        $monthlyData = Requisition::selectRaw('count(*) as total, DATE_FORMAT(created_at, "%M") as month, MIN(created_at) as min_date')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('min_date', 'asc') // created_at এর বদলে MIN(created_at) ব্যবহার করুন
            ->pluck('total', 'month');

        // ২. ক্যাটাগরি ভিত্তিক পণ্য সংখ্যা (ইনভেন্টরি অ্যানালিটিক্স)
        $categoryData = Category::withCount('products')
            ->pluck('products_count', 'name');

        return view('livewire.dashboard.dashboard-stats', [
            'role' => $role,
            'stats' => $stats,
            // চার্টের জন্য ডাটা পাস করা
            'monthlyLabels' => $monthlyData->keys(),
            'monthlyValues' => $monthlyData->values(),
            'categoryLabels' => $categoryData->keys(),
            'categoryValues' => $categoryData->values(),
        ])->layout('layouts.app', ['title' => __('Dashboard')]);
    }
}
