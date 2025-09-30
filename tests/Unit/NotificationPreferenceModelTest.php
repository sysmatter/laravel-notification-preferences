<?php

use SysMatter\NotificationPreferences\Models\NotificationPreference;
use SysMatter\NotificationPreferences\Tests\Fixtures\User;

test('forUser scope filters by user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    NotificationPreference::create([
        'user_id' => $user1->id,
        'notification_type' => 'Test',
        'channel' => 'mail',
        'enabled' => true,
    ]);

    NotificationPreference::create([
        'user_id' => $user2->id,
        'notification_type' => 'Test',
        'channel' => 'mail',
        'enabled' => true,
    ]);

    $preferences = NotificationPreference::forUser($user1->id)->get();

    expect($preferences)->toHaveCount(1);
    expect($preferences->first()->user_id)->toBe($user1->id);
});

test('forChannel scope filters by channel', function () {
    $user = User::factory()->create();

    NotificationPreference::create([
        'user_id' => $user->id,
        'notification_type' => 'Test',
        'channel' => 'mail',
        'enabled' => true,
    ]);

    NotificationPreference::create([
        'user_id' => $user->id,
        'notification_type' => 'Test',
        'channel' => 'sms',
        'enabled' => true,
    ]);

    $preferences = NotificationPreference::forChannel('mail')->get();

    expect($preferences)->toHaveCount(1);
    expect($preferences->first()->channel)->toBe('mail');
});

test('isEnabled scope filters enabled preferences', function () {
    $user = User::factory()->create();

    NotificationPreference::create([
        'user_id' => $user->id,
        'notification_type' => 'Test',
        'channel' => 'mail',
        'enabled' => true,
    ]);

    NotificationPreference::create([
        'user_id' => $user->id,
        'notification_type' => 'Test',
        'channel' => 'sms',
        'enabled' => false,
    ]);

    $preferences = NotificationPreference::isEnabled()->get();

    expect($preferences)->toHaveCount(1);
    expect($preferences->first()->enabled)->toBeTrue();
});
