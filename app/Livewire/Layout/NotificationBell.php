<?php

namespace App\Livewire\Layout;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationBell extends Component
{
    // নোটিফিকেশনে ক্লিক করলে সেটি Read হিসেবে মার্ক হবে এবং নির্দিষ্ট লিংকে রিডাইরেক্ট করবে
    public function markAsRead($notificationId, $url)
    {
        $notification = Auth::user()->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
        }

        return redirect()->to($url);
    }

    public function render()
    {
        return view('livewire.layout.notification-bell', [
            // শুধুমাত্র আনরিড নোটিফিকেশনগুলো আনা হচ্ছে
            'notifications' => Auth::user()->unreadNotifications
        ]);
    }
}
