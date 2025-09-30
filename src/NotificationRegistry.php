<?php

namespace SysMatter\NotificationPreferences;

class NotificationRegistry
{
    /**
     * @var array<string, array{name: string, channels: array<int, string>}>
     */
    private array $notifications = [];

    /**
     * @param  array<int, string>  $channels
     */
    public function register(string $notificationClass, string $name, array $channels): void
    {
        $this->notifications[$notificationClass] = [
            'name' => $name,
            'channels' => $channels,
        ];
    }

    /**
     * @return array<string, array{name: string, channels: array<int, string>}>
     */
    public function getRegisteredNotifications(): array
    {
        return $this->notifications;
    }

    public function isRegistered(string $notificationClass): bool
    {
        return isset($this->notifications[$notificationClass]);
    }

    /**
     * @return array<int, string>
     */
    public function getChannelsForNotification(string $notificationClass): array
    {
        return $this->notifications[$notificationClass]['channels'] ?? [];
    }
}
