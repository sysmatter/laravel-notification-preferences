<?php

use Illuminate\Notifications\Notification;
use SysMatter\NotificationPreferences\Tests\Fixtures\User;
use SysMatter\NotificationPreferences\Traits\HasPreferenceAwareNotifications;

class TestFilteredNotification extends Notification
{
    use HasPreferenceAwareNotifications;

    protected function getOriginalChannels($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): array
    {
        return [];
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}

test('preference aware notification via method uses original channels by default', function () {
    $user = User::factory()->create();
    $notification = new TestFilteredNotification;

    expect($notification->via($user))->toBe(['mail', 'database']);
});

test('preference aware notification via method uses filtered channels when set', function () {
    $user = User::factory()->create();
    $notification = new TestFilteredNotification;

    $notification->preferenceFilteredChannels = ['database'];

    expect($notification->via($user))->toBe(['database']);
});
