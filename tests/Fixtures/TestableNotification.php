<?php

namespace SysMatter\NotificationPreferences\Tests\Fixtures;

use SysMatter\NotificationPreferences\PreferenceAwareNotification;

class TestableNotification extends PreferenceAwareNotification
{
    public static function notificationMeta(): array
    {
        return [
            'name' => 'Test Notification',
            'channels' => ['mail', 'database'],
            'group' => null,
        ];
    }

    public function toMail($notifiable): array
    {
        return [];
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
