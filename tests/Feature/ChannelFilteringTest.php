<?php

use SysMatter\NotificationPreferences\NotificationRegistry;
use SysMatter\NotificationPreferences\Tests\Fixtures\FilteredNotification;
use SysMatter\NotificationPreferences\Tests\Fixtures\User;

test('notification uses original channels by default when no preferences set', function () {
    $user = User::factory()->create();
    $notification = new FilteredNotification();

    expect($notification->via($user))->toBe(['mail', 'database']);
});

test('notification filters channels based on user preferences', function () {
    $user = User::factory()->create();

    $registry = app(NotificationRegistry::class);
    $registry->register(FilteredNotification::class, 'Test', ['mail', 'database']);

    // Set user preferences
    $user->setNotificationPreference(FilteredNotification::class, 'mail', false);
    $user->setNotificationPreference(FilteredNotification::class, 'database', true);

    $notification = new FilteredNotification();

    expect($notification->via($user))->toBe(['database']);
});

test('notification sends through all channels when all enabled', function () {
    $user = User::factory()->create();

    $registry = app(NotificationRegistry::class);
    $registry->register(FilteredNotification::class, 'Test', ['mail', 'database']);

    // Both channels enabled (default)
    $notification = new FilteredNotification();

    expect($notification->via($user))->toBe(['mail', 'database']);
});

test('notification sends through no channels when all disabled', function () {
    $user = User::factory()->create();

    $registry = app(NotificationRegistry::class);
    $registry->register(FilteredNotification::class, 'Test', ['mail', 'database']);

    // Disable both channels
    $user->setNotificationPreference(FilteredNotification::class, 'mail', false);
    $user->setNotificationPreference(FilteredNotification::class, 'database', false);

    $notification = new FilteredNotification();

    expect($notification->via($user))->toBe([]);
});

test('notification respects mixed preferences across channels', function () {
    $user = User::factory()->create();

    $registry = app(NotificationRegistry::class);
    $registry->register(FilteredNotification::class, 'Test', ['mail', 'database']);

    // Mixed preferences - enable mail, disable database
    $user->setNotificationPreference(FilteredNotification::class, 'mail', true);
    $user->setNotificationPreference(FilteredNotification::class, 'database', false);

    $notification = new FilteredNotification();

    expect($notification->via($user))
        ->toBe(['mail'])
        ->not->toContain('database');
});

test('notification works with notifiable without preference method', function () {
    $user = new class () {
        // No getNotificationPreference method
    };

    $notification = new FilteredNotification();

    // Should fall back to original channels
    expect($notification->via($user))->toBe(['mail', 'database']);
});
