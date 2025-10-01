test('flat table includes group in each row', function () {
$this->registry->registerGroup('orders', 'Orders & Shipping');
$this->registry->register('OrderNotification', 'Order Updates', ['mail'], 'orders');
$this->registry->register('AccountNotification', 'Account Updates', ['mail'], null);

$table = $this->user->getNotificationPreferences(false);

expect($table)->toHaveCount(2);
expect($table[0]['group'])->toBe('orders');
expect($table[1]['group'])->toBeNull();
});

test('grouped table organizes notifications by group', function () {
$this->registry->registerGroup('orders', 'Orders & Shipping');
$this->registry->registerGroup('account', 'Account & Security');

$this->registry->register('OrderNotification', 'Order Updates', ['mail'], 'orders');
$this->registry->register('ShippingNotification', 'Shipping Updates', ['mail'], 'orders');
$this->registry->register('AccountNotification', 'Account Updates', ['mail'], 'account');

$table = $this->user->getNotificationPreferences(true);

expect($table)->toHaveKeys(['orders', 'account']);
expect($table['orders']['name'])->toBe('Orders & Shipping');
expect($table['orders']['notifications'])->toHaveCount(2);
expect($table['account']['notifications'])->toHaveCount(1);
});

test('grouped table includes uncategorized group for notifications without group', function () {
$this->registry->registerGroup('orders', 'Orders & Shipping');
$this->registry->register('OrderNotification', 'Order Updates', ['mail'], 'orders');
$this->registry->register('RandomNotification', 'Random Updates', ['mail'], null);

$table = $this->user->getNotificationPreferences(true);

expect($table)->toHaveKeys(['orders', 'uncategorized']);
expect($table['uncategorized']['name'])->toBe('Other');
expect($table['uncategorized']['notifications'])->toHaveCount(1);
});

test('grouped table includes<?php

// tests/Feature/GroupedPreferencesTest.php

use SysMatter\NotificationPreferences\NotificationRegistry;
use SysMatter\NotificationPreferences\PreferenceAwareNotification;
use SysMatter\NotificationPreferences\Tests\Fixtures\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->registry = app(NotificationRegistry::class);
});

test('flat table includes group in each row', function () {
    $this->registry->registerGroup('orders', 'Orders & Shipping');
    $this->registry->register('OrderNotification', 'Order Updates', ['mail'], 'orders');
    $this->registry->register('AccountNotification', 'Account Updates', ['mail'], null);

    $table = $this->user->getNotificationPreferences(grouped: false);

    expect($table)->toHaveCount(2);
    expect($table[0]['group'])->toBe('orders');
    expect($table[1]['group'])->toBeNull();
});

test('grouped table organizes notifications by group', function () {
    $this->registry->registerGroup('orders', 'Orders & Shipping');
    $this->registry->registerGroup('account', 'Account & Security');

    $this->registry->register('OrderNotification', 'Order Updates', ['mail'], 'orders');
    $this->registry->register('ShippingNotification', 'Shipping Updates', ['mail'], 'orders');
    $this->registry->register('AccountNotification', 'Account Updates', ['mail'], 'account');

    $table = $this->user->getNotificationPreferences(grouped: true);

    expect($table)->toHaveKeys(['orders', 'account']);
    expect($table['orders']['name'])->toBe('Orders & Shipping');
    expect($table['orders']['notifications'])->toHaveCount(2);
    expect($table['account']['notifications'])->toHaveCount(1);
});

test('grouped table includes uncategorized group for notifications without group', function () {
    $this->registry->registerGroup('orders', 'Orders & Shipping');
    $this->registry->register('OrderNotification', 'Order Updates', ['mail'], 'orders');
    $this->registry->register('RandomNotification', 'Random Updates', ['mail'], null);

    $table = $this->user->getNotificationPreferences(grouped: true);

    expect($table)->toHaveKeys(['orders', 'uncategorized']);
    expect($table['uncategorized']['name'])->toBe('Other');
    expect($table['uncategorized']['notifications'])->toHaveCount(1);
});

test('grouped table includes group descriptions', function () {
    $this->registry->registerGroup('orders', 'Orders & Shipping', 'Get notified about your orders');
    $this->registry->register('OrderNotification', 'Order Updates', ['mail'], 'orders');

    $table = $this->user->getNotificationPreferences(grouped: true);

    expect($table['orders']['description'])->toBe('Get notified about your orders');
});

test('registerFromMeta registers notifications with groups', function () {
    // Create a test notification class with group
    $notificationClass = new class () extends PreferenceAwareNotification {
        public static function notificationMeta(): array
        {
            return [
                'name' => 'Test with Group',
                'channels' => ['mail'],
                'group' => 'test-group',
            ];
        }
    };

    $this->registry->registerFromMeta([
        get_class($notificationClass),
    ]);

    expect($this->registry->getGroupForNotification(get_class($notificationClass)))->toBe('test-group');
});
