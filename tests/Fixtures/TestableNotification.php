<?php

namespace SysMatter\NotificationPreferences\Tests\Fixtures;

use Illuminate\Notifications\Notification;
use SysMatter\NotificationPreferences\Traits\HasPreferenceAwareNotifications;

class TestableNotification extends Notification
{
    use HasPreferenceAwareNotifications;

    protected function getOriginalChannels($notifiable): array
    {
        return ['mail', 'database'];
    }
}
