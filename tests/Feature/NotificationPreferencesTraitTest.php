<?php

use SysMatter\NotificationPreferences\Models\NotificationPreference;
use SysMatter\NotificationPreferences\Tests\Fixtures\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('user can get notification preference', function () {
    NotificationPreference::create([
        'user_id' => $this->user->id,
        'notification_type' => 'TestNotification',
        'channel' => 'mail',
        'enabled' => false,
    ]);

    $preference = $this->user->getNotificationPreference('TestNotification', 'mail');

    expect($preference)->toBeFalse();
});

test('user can set notification preference', function () {
    $this->user->setNotificationPreference('TestNotification', 'mail', false);

    $this->assertDatabaseHas('notification_preferences', [
        'user_id' => $this->user->id,
        'notification_type' => 'TestNotification',
        'channel' => 'mail',
        'enabled' => false,
    ]);
});

test('user can get preferences table', function () {
    $registry = app(\SysMatter\NotificationPreferences\NotificationRegistry::class);
    $registry->register('TestNotification', 'Test Notification', ['mail', 'sms']);

    $table = $this->user->getNotificationPreferencesTable();

    expect($table)->toBeArray();
    expect($table)->toHaveCount(1);
    expect($table[0]['notification_type'])->toBe('TestNotification');
});

test('user can update multiple preferences', function () {
    $preferences = [
        'TestNotification' => [
            'mail' => false,
            'sms' => true,
        ],
    ];

    $this->user->updateNotificationPreferences($preferences);

    $this->assertDatabaseHas('notification_preferences', [
        'user_id' => $this->user->id,
        'notification_type' => 'TestNotification',
        'channel' => 'mail',
        'enabled' => false,
    ]);

    $this->assertDatabaseHas('notification_preferences', [
        'user_id' => $this->user->id,
        'notification_type' => 'TestNotification',
        'channel' => 'sms',
        'enabled' => true,
    ]);
});

test('user has notification preferences relationship', function () {
    NotificationPreference::create([
        'user_id' => $this->user->id,
        'notification_type' => 'TestNotification',
        'channel' => 'mail',
        'enabled' => false,
    ]);

    expect($this->user->notificationPreferences)->toHaveCount(1);
    expect($this->user->notificationPreferences->first()->notification_type)->toBe('TestNotification');
});
