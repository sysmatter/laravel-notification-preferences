<?php

namespace SysMatter\NotificationPreferences\Traits;

trait HasPreferenceAwareNotifications
{
    /**
     * @var array<int, string>|null
     */
    public ?array $preferenceFilteredChannels = null;

    /**
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

    abstract protected function getOriginalChannels($notifiable): array;
}
