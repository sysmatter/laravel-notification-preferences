<?php

use SysMatter\NotificationPreferences\NotificationRegistry;
use SysMatter\NotificationPreferences\Tests\Fixtures\TestableNotification;

beforeEach(function () {
    $this->registry = new NotificationRegistry();
});

test('it registers notifications correctly', function () {
    $this->registry->register('TestNotification', 'Test Notification', ['mail', 'sms']);

    expect($this->registry->isRegistered('TestNotification'))->toBeTrue();
    expect($this->registry->getChannelsForNotification('TestNotification'))->toBe(['mail', 'sms']);
});

test('it registers notifications with groups', function () {
    $this->registry->register('TestNotification', 'Test Notification', ['mail', 'sms'], 'orders');

    expect($this->registry->isRegistered('TestNotification'))->toBeTrue();
    expect($this->registry->getGroupForNotification('TestNotification'))->toBe('orders');
});

test('it returns all registered notifications', function () {
    $this->registry->register('TestNotification', 'Test Notification', ['mail']);
    $this->registry->register('AnotherNotification', 'Another Notification', ['sms']);

    $notifications = $this->registry->getRegisteredNotifications();

    expect($notifications)->toHaveCount(2);
    expect($notifications['TestNotification']['name'])->toBe('Test Notification');
    expect($notifications['AnotherNotification']['name'])->toBe('Another Notification');
});

test('it returns false for unregistered notification', function () {
    expect($this->registry->isRegistered('UnregisteredNotification'))->toBeFalse();
});

test('it returns empty array for unregistered notification channels', function () {
    $channels = $this->registry->getChannelsForNotification('UnregisteredNotification');

    expect($channels)->toBe([]);
});

test('it returns null for unregistered notification group', function () {
    $group = $this->registry->getGroupForNotification('UnregisteredNotification');

    expect($group)->toBeNull();
});

test('it registers groups', function () {
    $this->registry->registerGroup('orders', 'Order & Shipping', 'Notifications about your orders');

    $groups = $this->registry->getRegisteredGroups();

    expect($groups)->toHaveKey('orders');
    expect($groups['orders']['name'])->toBe('Order & Shipping');
    expect($groups['orders']['description'])->toBe('Notifications about your orders');
});

test('it registers notifications from meta', function () {
    $this->registry->registerFromMeta([
        TestableNotification::class,
    ]);

    expect($this->registry->isRegistered(TestableNotification::class))->toBeTrue();
    expect($this->registry->getChannelsForNotification(TestableNotification::class))->toBe(['mail', 'database']);
});

test('it skips classes without notificationMeta method', function () {
    $classWithoutMeta = new class () {
    };

    $this->registry->registerFromMeta([
        get_class($classWithoutMeta),
    ]);

    expect($this->registry->isRegistered(get_class($classWithoutMeta)))->toBeFalse();
});
