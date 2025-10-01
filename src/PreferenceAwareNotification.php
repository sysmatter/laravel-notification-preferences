<?php

namespace SysMatter\NotificationPreferences;

use Illuminate\Notifications\Notification;
use SysMatter\NotificationPreferences\Traits\HasPreferenceAwareNotifications;

abstract class PreferenceAwareNotification extends Notification
{
    use HasPreferenceAwareNotifications;

    /**
     * Get notification metadata for registration and display.
     *
     * @return array{name: string, channels: array<int, string>, group: string|null}
     */
    abstract public static function notificationMeta(): array;

    /**
     * @param mixed $notifiable
     * @return array<string>
     */
    protected function getOriginalChannels(mixed $notifiable): array
    {
        return static::notificationMeta()['channels'];
    }
}
