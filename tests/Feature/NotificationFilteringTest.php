<?php

use Illuminate\Support\Facades\Notification;
use SysMatter\NotificationPreferences\Tests\Models\User;
use SysMatter\NotificationPreferences\Tests\Notifications\AutoFilteredNotification;
use SysMatter\NotificationPreferences\Tests\Notifications\TestNotification;

beforeEach(function () {
    Notification::fake();

    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    config()->set('notification-preferences.notifications', [
        AutoFilteredNotification::class => [
            'group' => 'test',
            'label' => 'Auto Filtered',
        ],
    ]);

    config()->set('notification-preferences.groups', [
        'test' => [
            'label' => 'Test Group',
            'default_preference' => 'opt_in',
        ],
    ]);
});

it('filters channels using trait', function () {
    $this->user->setNotificationPreference(TestNotification::class, 'mail', false);

    $notification = new TestNotification();
    $channels = $notification->via($this->user);

    expect($channels)->not->toContain('mail')
        ->and($channels)->toContain('database');
});

it('allows all channels when all are enabled', function () {
    $notification = new TestNotification();
    $channels = $notification->via($this->user);

    expect($channels)->toContain('mail')
        ->and($channels)->toContain('database');
});

it('returns empty array when all channels disabled', function () {
    $this->user->setNotificationPreference(TestNotification::class, 'mail', false);
    $this->user->setNotificationPreference(TestNotification::class, 'database', false);

    $notification = new TestNotification();
    $channels = $notification->via($this->user);

    expect($channels)->toBeEmpty();
});

it('respects forced channels in filtering', function () {
    config()->set('notification-preferences.notifications', [
        TestNotification::class => [
            'group' => 'test',
            'label' => 'Test Notification',
            'force_channels' => ['mail'],
        ],
    ]);

    $this->user->setNotificationPreference(TestNotification::class, 'mail', false);

    $notification = new TestNotification();
    $channels = $notification->via($this->user);

    expect($channels)->toContain('mail');
});
