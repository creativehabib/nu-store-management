<?php

namespace App\Livewire\Layout;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public function openNotification(string $notificationId)
    {
        $notification = Auth::user()?->notifications()->find($notificationId);

        if (! $notification) {
            return redirect()->route('dashboard');
        }

        $notification->markAsRead();

        return redirect()->to($notification->data['url'] ?? route('dashboard'));
    }

    public function markAsRead(string $notificationId): void
    {
        Auth::user()?->notifications()->find($notificationId)?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        Auth::user()?->unreadNotifications()->update(['read_at' => now()]);
    }

    public function deleteNotification(string $notificationId): void
    {
        Auth::user()?->notifications()->find($notificationId)?->delete();
    }

    public function clearRead(): void
    {
        Auth::user()?->readNotifications()->delete();
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.layout.notification-bell', [
            'notifications' => $user?->notifications()->latest()->take(10)->get() ?? collect(),
            'unreadCount' => $user?->unreadNotifications()->count() ?? 0,
            'readCount' => $user?->readNotifications()->count() ?? 0,
        ]);
    }
}
