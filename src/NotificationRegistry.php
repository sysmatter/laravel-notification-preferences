<?php

namespace SysMatter\NotificationPreferences;

class NotificationRegistry
{
    private array $notifications = [];

    public function register(string $notificationClass, string $name, array $channels): void
    {
        $this->notifications[$notificationClass] = [
            'name' => $name,
            'channels' => $channels,
        ];
    }

    public function getRegisteredNotifications(): array
    {
        return $this->notifications;
    }

    public function isRegistered(string $notificationClass): bool
    {
        return isset($this->notifications[$notificationClass]);
    }

    public function getChannelsForNotification(string $notificationClass): array
    {
        return $this->notifications[$notificationClass]['channels'] ?? [];
    }
}
