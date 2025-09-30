<?php

namespace SysMatter\NotificationPreferences\Traits;

trait HasPreferenceAwareNotifications
{
    public function via($notifiable)
    {
        // If channels were filtered by preferences, use those
        if (isset($this->preferenceFilteredChannels)) {
            return $this->preferenceFilteredChannels;
        }

        // Otherwise, use the original via method
        return $this->getOriginalChannels($notifiable);
    }

    abstract protected function getOriginalChannels($notifiable): array;
}
