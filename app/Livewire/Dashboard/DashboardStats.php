<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\User;
use App\Models\Product;
use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;

class DashboardStats extends Component
{
    // ইউজারের রোল অনুযায়ী কিউ স্ট্যাটাস বের করার মেথড
    private function getQueueStatus($role)
    {
        if ($role === 'initiator') return ['pending', 'returned', 'director_approved', 'distributed'];
        if ($role === 'assistant_director') return ['initiator_checked'];
        if ($role === 'deputy_director') return ['ad_approved'];
        if ($role === 'director') return ['dd_approved'];
        return [];
    }

    public function render()
    {
        $user = Auth::user();
        $role = $user->role;
        $stats = [];

        // Admin এর জন্য স্ট্যাটিস্টিকস
        if ($role === 'admin') {
            $stats['total_users'] = User::count();
            $stats['pending_users'] = User::where('is_approved', false)->count();
            $stats['total_products'] = Product::count();
            $stats['low_stock'] = Product::where('stock', '<=', 10)->count(); // স্টক ১০ বা তার কম হলে
        }
        // Requisitioner এর জন্য স্ট্যাটিস্টিকস
        elseif ($role === 'requisitioner') {
            $stats['total_submitted'] = Requisition::where('user_id', $user->id)->count();
            $stats['pending'] = Requisition::where('user_id', $user->id)->where('status', '!=', 'distributed')->count();
            $stats['distributed'] = Requisition::where('user_id', $user->id)->where('status', 'distributed')->count();
            $stats['returned'] = Requisition::where('user_id', $user->id)->where('status', 'returned')->count();
        }
        // Approvers (Initiator, AD, DD, Director) এর জন্য স্ট্যাটিস্টিকস
        else {
            $queueStatuses = $this->getQueueStatus($role);

            if ($role === 'initiator') {
                $stats['pending_action'] = Requisition::whereIn('status', ['pending', 'returned'])->count();
                $stats['ready_to_print'] = Requisition::whereIn('status', ['director_approved', 'distributed'])->count();
            } else {
                $stats['pending_approval'] = Requisition::whereIn('status', $queueStatuses)->count();
            }

            // সিস্টেমে মোট কতগুলো রিকুইজিশন প্রসেস হচ্ছে
            $stats['total_requisitions'] = Requisition::count();
        }

        return view('livewire.dashboard.dashboard-stats', [
            'role' => $role,
            'stats' => $stats
        ])->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
