<?php

namespace SysMatter\NotificationPreferences\Traits;

trait HasPreferenceAwareNotifications
{
    /**
     * @var array<int, string>|null
     */
    public ?array $preferenceFilteredChannels = null;

    public function via(mixed $notifiable): ?array
    {
        // If channels were filtered by preferences, use those
        return $this->preferenceFilteredChannels ?? $this->getOriginalChannels($notifiable);
    }

    abstract protected function getOriginalChannels($notifiable): array;
}
