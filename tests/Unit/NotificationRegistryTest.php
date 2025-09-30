<?php

use SysMatter\NotificationPreferences\NotificationRegistry;

beforeEach(function () {
    $this->registry = new NotificationRegistry;
});

test('it registers notifications correctly', function () {
    $this->registry->register('TestNotification', 'Test Notification', ['mail', 'sms']);

    expect($this->registry->isRegistered('TestNotification'))->toBeTrue();
    expect($this->registry->getChannelsForNotification('TestNotification'))->toBe(['mail', 'sms']);
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
