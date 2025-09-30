<?php

namespace SysMatter\NotificationPreferences;

use Illuminate\Container\Container;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Notification;
use ReflectionClass;

class PreferenceAwareChannelManager extends ChannelManager
{
    private ChannelManager $originalManager;

    public function __construct(Container $app, ChannelManager $originalManager)
    {
        parent::__construct($app);
        $this->originalManager = $originalManager;
    }

    public function send(mixed $notifiables, $notification): void
    {
        // Filter channels based on user preferences
        if (method_exists($notification, 'via')) {
            $originalChannels = $notification->via($notifiables);
            $filteredChannels = $this->filterChannelsByPreferences($notifiables, $notification, $originalChannels);

            // Temporarily override the via method
            $this->overrideViaMethod($notification, $filteredChannels);
        }

        $this->originalManager->send($notifiables, $notification);
    }

    /**
     * @param  array<int, string>  $channels
     * @return array<int, string>
     */
    private function filterChannelsByPreferences(mixed $notifiables, Notification $notification, array $channels): array
    {
        $notifiableCollection = is_iterable($notifiables) ? $notifiables : [$notifiables];
        $notificationType = get_class($notification);
        $preferenceManager = app(NotificationPreferenceManager::class);

        $filteredChannels = [];

        foreach ($channels as $channel) {
            $shouldSend = false;

            foreach ($notifiableCollection as $notifiable) {
                if (method_exists($notifiable, 'getNotificationPreference')) {
                    if ($notifiable->getNotificationPreference($notificationType, $channel)) {
                        $shouldSend = true;
                        break;
                    }
                } else {
                    // Fallback: send if no preference system implemented
                    $shouldSend = true;
                    break;
                }
            }

            if ($shouldSend) {
                $filteredChannels[] = $channel;
            }
        }

        return $filteredChannels;
    }

    /**
     * @param  array<int, string>  $channels
     */
    private function overrideViaMethod(Notification $notification, array $channels): void
    {
        // Use reflection to temporarily override the via method
        $reflection = new ReflectionClass($notification);
        if ($reflection->hasMethod('via')) {
            $notification->preferenceFilteredChannels = $channels;
        }
    }
}
