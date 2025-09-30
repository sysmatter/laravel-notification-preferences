<?php

use SysMatter\NotificationPreferences\Tests\Fixtures\FilteredNotification;
use SysMatter\NotificationPreferences\Tests\Fixtures\User;

test('preference aware notification via method uses original channels by default', function () {
    $user = User::factory()->create();
    $notification = new FilteredNotification;

    expect($notification->via($user))->toBe(['mail', 'database']);
});

test('preference aware notification via method uses filtered channels when set', function () {
    $user = User::factory()->create();
    $notification = new FilteredNotification;

    $notification->preferenceFilteredChannels = ['database'];

    expect($notification->via($user))->toBe(['database']);
});
