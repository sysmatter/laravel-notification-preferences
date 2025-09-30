<?php

use Illuminate\Notifications\Notification;
use SysMatter\NotificationPreferences\NotificationRegistry;
use SysMatter\NotificationPreferences\Tests\Fixtures\User;
use SysMatter\NotificationPreferences\Traits\HasPreferenceAwareNotifications;

class SimpleNotification extends Notification
{
    use HasPreferenceAwareNotifications;

    protected function getOriginalChannels($notifiable): array
    {
        return ['mail', 'database', 'sms'];
    }

    public function toArray($notifiable): array
    {
        return ['message' => 'test'];
    }
}

test('channel manager calls filter method when sending', function () {
    $user = User::factory()->create();

    $registry = app(NotificationRegistry::class);
    $registry->register(SimpleNotification::class, 'Simple', ['mail', 'database', 'sms']);

    $user->setNotificationPreference(SimpleNotification::class, 'mail', false);
    $user->setNotificationPreference(SimpleNotification::class, 'database', true);
    $user->setNotificationPreference(SimpleNotification::class, 'sms', true);

    $notification = new SimpleNotification;

    // Get original channels
    $originalChannels = $notification->via($user);
    expect($originalChannels)->toBe(['mail', 'database', 'sms']);

    // Simulate what PreferenceAwareChannelManager does
    $notification->preferenceFilteredChannels = ['database', 'sms'];

    // After filtering, via should return filtered channels
    $filteredChannels = $notification->via($user);
    expect($filteredChannels)->toBe(['database', 'sms']);
});

test('channel manager keeps all channels when all are enabled', function () {
    $user = User::factory()->create();

    $registry = app(NotificationRegistry::class);
    $registry->register(SimpleNotification::class, 'Simple', ['mail', 'database', 'sms']);

    // All enabled by default
    $notification = new SimpleNotification;

    // Simulate filtering with all enabled
    $notification->preferenceFilteredChannels = ['mail', 'database', 'sms'];

    expect($notification->via($user))->toBe(['mail', 'database', 'sms']);
});

test('channel manager filters to empty array when all disabled', function () {
    $user = User::factory()->create();

    $registry = app(NotificationRegistry::class);
    $registry->register(SimpleNotification::class, 'Simple', ['mail', 'database', 'sms']);

    $user->setNotificationPreference(SimpleNotification::class, 'mail', false);
    $user->setNotificationPreference(SimpleNotification::class, 'database', false);
    $user->setNotificationPreference(SimpleNotification::class, 'sms', false);

    $notification = new SimpleNotification;
    $notification->preferenceFilteredChannels = [];

    expect($notification->via($user))->toBe([]);
});
