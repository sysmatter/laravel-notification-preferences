<?php

use SysMatter\NotificationPreferences\Models\NotificationPreference;
use SysMatter\NotificationPreferences\NotificationPreferenceManager;
use SysMatter\NotificationPreferences\NotificationRegistry;
use SysMatter\NotificationPreferences\Tests\Fixtures\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->manager = app(NotificationPreferenceManager::class);
});

test('it returns default preference when none exists', function () {
    config(['notification-preferences.default_enabled' => true]);

    $preference = $this->manager->getPreference($this->user, 'TestNotification', 'mail');

    expect($preference)->toBeTrue();
});

test('it returns stored preference when it exists', function () {
    NotificationPreference::create([
        'user_id' => $this->user->id,
        'notification_type' => 'TestNotification',
        'channel' => 'mail',
        'enabled' => false,
    ]);

    $preference = $this->manager->getPreference($this->user, 'TestNotification', 'mail');

    expect($preference)->toBeFalse();
});

test('it sets a preference correctly', function () {
    $this->manager->setPreference($this->user, 'TestNotification', 'mail', false);

    $this->assertDatabaseHas('notification_preferences', [
        'user_id' => $this->user->id,
        'notification_type' => 'TestNotification',
        'channel' => 'mail',
        'enabled' => false,
    ]);
});

test('it updates existing preference', function () {
    NotificationPreference::create([
        'user_id' => $this->user->id,
        'notification_type' => 'TestNotification',
        'channel' => 'mail',
        'enabled' => true,
    ]);

    $this->manager->setPreference($this->user, 'TestNotification', 'mail', false);

    $preference = NotificationPreference::where([
        'user_id' => $this->user->id,
        'notification_type' => 'TestNotification',
        'channel' => 'mail',
    ])->first();

    expect($preference->enabled)->toBeFalse();
});

test('it generates preferences table correctly', function () {
    $registry = app(NotificationRegistry::class);
    $registry->register('TestNotification', 'Test Notification', ['mail', 'sms']);

    NotificationPreference::create([
        'user_id' => $this->user->id,
        'notification_type' => 'TestNotification',
        'channel' => 'mail',
        'enabled' => false,
    ]);

    $table = $this->manager->getPreferencesTable($this->user);

    expect($table)->toHaveCount(1);
    expect($table[0]['notification_type'])->toBe('TestNotification');
    expect($table[0]['notification_name'])->toBe('Test Notification');
    expect($table[0]['channels']['mail']['enabled'])->toBeFalse();
    expect($table[0]['channels']['sms']['enabled'])->toBeTrue(); // default
});

test('it updates multiple preferences at once', function () {
    $preferences = [
        'TestNotification' => [
            'mail' => true,
            'sms' => false,
        ],
        'AnotherNotification' => [
            'mail' => false,
        ],
    ];

    $this->manager->updatePreferences($this->user, $preferences);

    $this->assertDatabaseHas('notification_preferences', [
        'user_id' => $this->user->id,
        'notification_type' => 'TestNotification',
        'channel' => 'mail',
        'enabled' => true,
    ]);

    $this->assertDatabaseHas('notification_preferences', [
        'user_id' => $this->user->id,
        'notification_type' => 'TestNotification',
        'channel' => 'sms',
        'enabled' => false,
    ]);

    $this->assertDatabaseHas('notification_preferences', [
        'user_id' => $this->user->id,
        'notification_type' => 'AnotherNotification',
        'channel' => 'mail',
        'enabled' => false,
    ]);
});
