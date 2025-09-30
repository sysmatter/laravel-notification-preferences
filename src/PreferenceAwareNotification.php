<?php

namespace SysMatter\NotificationPreferences;

use Illuminate\Notifications\Notification;
use SysMatter\NotificationPreferences\Traits\HasPreferenceAwareNotifications;

abstract class PreferenceAwareNotification extends Notification
{
    use HasPreferenceAwareNotifications;

    /**
     * @param mixed $notifiable
     * @return array<string>
     */
    protected function getOriginalChannels(mixed $notifiable): array
    {
        return array_keys(config('notification-preferences.default_channels'));
    }
}
