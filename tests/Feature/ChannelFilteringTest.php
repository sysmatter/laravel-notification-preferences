<?php

use SysMatter\NotificationPreferences\NotificationRegistry;
use SysMatter\NotificationPreferences\Tests\Fixtures\FilteredNotification;
use SysMatter\NotificationPreferences\Tests\Fixtures\User;

test('preference aware notification via method uses original channels by default', function () {
    $user = User::factory()->create();
    $notification = new FilteredNotification();

    expect($notification->via($user))->toBe(['mail', 'database']);
});

test('preference aware notification via method uses filtered channels when set', function () {
    $user = User::factory()->create();

    $registry = app(NotificationRegistry::class);
    $registry->register(FilteredNotification::class, 'Test', ['mail', 'database']);

    // Set user's actual preferences
    $user->setNotificationPreference(FilteredNotification::class, 'mail', false);
    $user->setNotificationPreference(FilteredNotification::class, 'database', true);

    $notification = new FilteredNotification();

    expect($notification->via($user))->toBe(['database']);
});
