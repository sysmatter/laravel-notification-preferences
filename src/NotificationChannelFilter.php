<?php

namespace SysMatter\NotificationPreferences;

use Illuminate\Notifications\Events\NotificationSending;
use ReflectionClass;

class NotificationChannelFilter
{
    public function __construct(
        protected NotificationPreferenceManager $manager
    ) {
    }

    /**
     * Handle the notification sending event.
     * This provides automatic filtering for all notifications.
     */
    public function handle(NotificationSending $event): bool
    {
        // If notification uses the ChecksNotificationPreferences trait,
        // it handles its own filtering
        $reflection = new ReflectionClass($event->notification);
        $traits = collect($reflection->getTraitNames());

        if ($traits->contains('SysMatter\NotificationPreferences\Concerns\ChecksNotificationPreferences')) {
            return true; // Let the notification handle it
        }

        // Otherwise, check preferences automatically
        $notificationType = get_class($event->notification);
        $channel = $event->channel;

        // Check if this notification type is registered in config
        $notifications = config('notification-preferences.notifications', []);
        if (!isset($notifications[$notificationType])) {
            return true; // Not managed by preferences, allow through
        }

        // Check forced channels
        $forcedChannels = $notifications[$notificationType]['force_channels'] ?? [];
        if (in_array($channel, $forcedChannels)) {
            return true; // Forced channels always send
        }

        return $this->manager->isChannelEnabled(
            $event->notifiable,
            $notificationType,
            $channel
        );
    }
}
