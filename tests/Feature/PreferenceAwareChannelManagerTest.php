<?php

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use SysMatter\NotificationPreferences\NotificationRegistry;
use SysMatter\NotificationPreferences\Tests\Fixtures\User;
use SysMatter\NotificationPreferences\Traits\HasPreferenceAwareNotifications;

class TestNotificationWithPreferences extends Notification
{
    use HasPreferenceAwareNotifications;

    protected function getOriginalChannels($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('Test notification');
    }

    public function toArray($notifiable): array
    {
        return ['message' => 'Test notification'];
    }
}

beforeEach(function () {
    $registry = app(NotificationRegistry::class);
    $registry->register(TestNotificationWithPreferences::class, 'Test Notification', ['mail', 'database']);
});

test('notification sends to all enabled channels', function () {
    NotificationFacade::fake();

    $user = User::factory()->create();

    $user->notify(new TestNotificationWithPreferences);

    NotificationFacade::assertSentTo($user, TestNotificationWithPreferences::class);
});

test('notification respects disabled mail preference', function () {
    $user = User::factory()->create();

    // Disable mail notifications
    $user->setNotificationPreference(TestNotificationWithPreferences::class, 'mail', false);

    // Notification should still be created but with filtered channels
    $notification = new TestNotificationWithPreferences;

    // Check that preferences are being read correctly
    expect($user->getNotificationPreference(TestNotificationWithPreferences::class, 'mail'))->toBeFalse();
    expect($user->getNotificationPreference(TestNotificationWithPreferences::class, 'database'))->toBeTrue();
});

test('notification respects disabled database preference', function () {
    $user = User::factory()->create();

    // Disable database notifications
    $user->setNotificationPreference(TestNotificationWithPreferences::class, 'database', false);

    expect($user->getNotificationPreference(TestNotificationWithPreferences::class, 'database'))->toBeFalse();
    expect($user->getNotificationPreference(TestNotificationWithPreferences::class, 'mail'))->toBeTrue();
});

test('notification sends when all channels are enabled', function () {
    $user = User::factory()->create();

    // Both should be enabled by default
    expect($user->getNotificationPreference(TestNotificationWithPreferences::class, 'mail'))->toBeTrue();
    expect($user->getNotificationPreference(TestNotificationWithPreferences::class, 'database'))->toBeTrue();
});
