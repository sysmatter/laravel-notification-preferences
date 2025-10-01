<?php

namespace SysMatter\NotificationPreferences;

class NotificationRegistry
{
    /**
     * @var array<string, array{name: string, channels: array<int, string>, group: string|null}>
     */
    private array $notifications = [];

    /**
     * @var array<string, array{name: string, description: string|null}>
     */
    private array $groups = [];

    /**
     * Register a notification type.
     *
     * @param array<int, string> $channels
     */
    public function register(string $notificationClass, string $name, array $channels, ?string $group = null): void
    {
        $this->notifications[$notificationClass] = [
            'name' => $name,
            'channels' => $channels,
            'group' => $group,
        ];
    }

    /**
     * Register multiple notifications from their metadata.
     *
     * @param array<int, string> $notificationClasses
     */
    public function registerFromMeta(array $notificationClasses): void
    {
        foreach ($notificationClasses as $class) {
            if (method_exists($class, 'notificationMeta')) {
                $meta = $class::notificationMeta();
                $this->register(
                    $class,
                    $meta['name'],
                    $meta['channels'],
                    $meta['group'] ?? null
                );
            }
        }
    }

    /**
     * Register a notification group.
     */
    public function registerGroup(string $key, string $name, ?string $description = null): void
    {
        $this->groups[$key] = [
            'name' => $name,
            'description' => $description,
        ];
    }

    /**
     * @return array<string, array{name: string, channels: array<int, string>, group: string|null}>
     */
    public function getRegisteredNotifications(): array
    {
        return $this->notifications;
    }

    /**
     * @return array<string, array{name: string, description: string|null}>
     */
    public function getRegisteredGroups(): array
    {
        return $this->groups;
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

    public function getGroupForNotification(string $notificationClass): ?string
    {
        return $this->notifications[$notificationClass]['group'] ?? null;
    }
}
