<?php

namespace SysMatter\NotificationPreferences\Traits;

trait HasPreferenceAwareNotifications
{
    /**
     * Get the notification's delivery channels, filtered by user preferences.
     *
     * @param mixed $notifiable
     * @return array<string>
     */
    public function via(mixed $notifiable): array
    {
        $originalChannels = $this->getOriginalChannels($notifiable);

        // Filter channels based on preferences
        if (method_exists($notifiable, 'getNotificationPreference')) {
            $notificationType = get_class($this);
            $filteredChannels = [];

            foreach ($originalChannels as $channel) {
                if ($notifiable->getNotificationPreference($notificationType, $channel)) {
                    $filteredChannels[] = $channel;
                }
            }

            return $filteredChannels;
        }

        return $originalChannels;
    }

    /**
     * Get the original channels before preference filtering.
     * Override this method in your notification classes.
     *
     * @param mixed $notifiable
     * @return array<string>
     */
    abstract protected function getOriginalChannels(mixed $notifiable): array;
}
