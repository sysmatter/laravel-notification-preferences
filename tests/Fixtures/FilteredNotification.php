<?php

namespace SysMatter\NotificationPreferences\Tests\Fixtures;

use Illuminate\Notifications\Notification;
use SysMatter\NotificationPreferences\Traits\HasPreferenceAwareNotifications;

class FilteredNotification extends Notification
{
    use HasPreferenceAwareNotifications;

    protected function getOriginalChannels($notifiable): array
    {
        return ['mail', 'database'];
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
