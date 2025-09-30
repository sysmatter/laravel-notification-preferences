<?php

use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Notification;
use SysMatter\NotificationPreferences\NotificationRegistry;
use SysMatter\NotificationPreferences\PreferenceAwareChannelManager;
use SysMatter\NotificationPreferences\Tests\Fixtures\User;
use SysMatter\NotificationPreferences\Traits\HasPreferenceAwareNotifications;

class TestableNotification extends Notification
{
    use HasPreferenceAwareNotifications;

    protected function getOriginalChannels($notifiable): array
    {
        return ['mail', 'database'];
    }
}

test('filterChannelsByPreferences method filters channels based on user preferences', function () {
    $user = User::factory()->create();

    $registry = app(NotificationRegistry::class);
    $registry->register(TestableNotification::class, 'Test', ['mail', 'database']);

    // Set preferences
    $user->setNotificationPreference(TestableNotification::class, 'mail', false);
    $user->setNotificationPreference(TestableNotification::class, 'database', true);

    $originalManager = app(ChannelManager::class);
    $manager = new PreferenceAwareChannelManager(app(), $originalManager);

    $notification = new TestableNotification;

    // Use reflection to call the private method
    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('filterChannelsByPreferences');
    $method->setAccessible(true);

    $result = $method->invoke($manager, $user, $notification, ['mail', 'database']);

    expect($result)->toBe(['database']);
});

test('filterChannelsByPreferences keeps all channels when all enabled', function () {
    $user = User::factory()->create();

    $registry = app(NotificationRegistry::class);
    $registry->register(TestableNotification::class, 'Test', ['mail', 'database']);

    $originalManager = app(ChannelManager::class);
    $manager = new PreferenceAwareChannelManager(app(), $originalManager);

    $notification = new TestableNotification;

    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('filterChannelsByPreferences');
    $method->setAccessible(true);

    $result = $method->invoke($manager, $user, $notification, ['mail', 'database']);

    expect($result)->toBe(['mail', 'database']);
});

test('filterChannelsByPreferences returns empty array when all disabled', function () {
    $user = User::factory()->create();

    $registry = app(NotificationRegistry::class);
    $registry->register(TestableNotification::class, 'Test', ['mail', 'database']);

    $user->setNotificationPreference(TestableNotification::class, 'mail', false);
    $user->setNotificationPreference(TestableNotification::class, 'database', false);

    $originalManager = app(ChannelManager::class);
    $manager = new PreferenceAwareChannelManager(app(), $originalManager);

    $notification = new TestableNotification;

    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('filterChannelsByPreferences');
    $method->setAccessible(true);

    $result = $method->invoke($manager, $user, $notification, ['mail', 'database']);

    expect($result)->toBe([]);
});

test('overrideViaMethod sets preferenceFilteredChannels property', function () {
    $originalManager = app(ChannelManager::class);
    $manager = new PreferenceAwareChannelManager(app(), $originalManager);

    $notification = new TestableNotification;

    $reflection = new ReflectionClass($manager);
    $method = $reflection->getMethod('overrideViaMethod');
    $method->setAccessible(true);

    $method->invoke($manager, $notification, ['database']);

    expect($notification->preferenceFilteredChannels)->toBe(['database']);
});
