<?php

namespace SysMatter\NotificationPreferences\Tests\Fixtures;

use SysMatter\NotificationPreferences\PreferenceAwareNotification;

class FilteredNotification extends PreferenceAwareNotification
{
    public static function notificationMeta(): array
    {
        return [
            'name' => 'Filtered Test Notification',
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
