<?php

use App\Livewire\Layout\NotificationBell;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Livewire\Livewire;

function createDatabaseNotification(User $user, array $data = [], ?string $readAt = null): DatabaseNotification
{
    return DatabaseNotification::query()->create([
        'id' => (string) Str::uuid(),
        'type' => 'Testing\\Notification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => array_merge([
            'requisition_no' => 'REQ-NTF-001',
            'message' => 'A requisition needs your attention.',
            'url' => route('dashboard'),
        ], $data),
        'read_at' => $readAt,
    ]);
}

it('shows recent notifications with unread count and management actions', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'pf_no' => 'PF-NOTIFY-001',
        'mobile_no' => '01700005001',
        'is_approved' => true,
    ]);

    createDatabaseNotification($user);
    createDatabaseNotification($user, [
        'requisition_no' => 'REQ-NTF-READ',
        'message' => 'This notification was already read.',
    ], now()->toDateTimeString());

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->assertSee('REQ-NTF-001')
        ->assertSee('A requisition needs your attention.')
        ->assertSee('1 unread notification')
        ->assertSee('Mark all read')
        ->assertSee('Clear read')
        ->assertSee('Delete');
});

it('marks all notifications as read and clears read notifications', function () {
    $user = User::factory()->create([
        'role' => 'admin',
        'pf_no' => 'PF-NOTIFY-002',
        'mobile_no' => '01700005002',
        'is_approved' => true,
    ]);

    createDatabaseNotification($user);

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->call('markAllAsRead')
        ->assertSee('0 unread notifications')
        ->call('clearRead')
        ->assertSee('No notifications yet');

    expect($user->notifications()->count())->toBe(0);
});

it('opens a notification, marks it as read, and only allows deleting own notifications', function () {
    $user = User::factory()->create([
        'role' => 'initiator',
        'pf_no' => 'PF-NOTIFY-003',
        'mobile_no' => '01700005003',
        'is_approved' => true,
    ]);
    $otherUser = User::factory()->create([
        'role' => 'admin',
        'pf_no' => 'PF-NOTIFY-004',
        'mobile_no' => '01700005004',
        'is_approved' => true,
    ]);

    $notification = createDatabaseNotification($user, ['url' => route('dashboard')]);
    $otherNotification = createDatabaseNotification($otherUser, ['requisition_no' => 'REQ-OTHER']);

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->call('openNotification', $notification->id)
        ->assertRedirect(route('dashboard'));

    expect($notification->fresh()->read_at)->not->toBeNull();

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->call('deleteNotification', $otherNotification->id);

    expect($otherNotification->fresh())->not->toBeNull();
});
