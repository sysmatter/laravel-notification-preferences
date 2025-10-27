<?php

namespace SysMatter\NotificationPreferences\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use SysMatter\NotificationPreferences\Models\NotificationPreference;
use SysMatter\NotificationPreferences\NotificationPreferenceManager;

trait HasNotificationPreferences
{
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function getNotificationPreference(string $notificationType, string $channel): bool
    {
        return app(NotificationPreferenceManager::class)
            ->isChannelEnabled($this, $notificationType, $channel);
    }

    public function setNotificationPreference(
        string $notificationType,
        string $channel,
        bool   $enabled
    ): NotificationPreference {
        return app(NotificationPreferenceManager::class)
            ->setPreference($this, $notificationType, $channel, $enabled);
    }

    public function getNotificationPreferences(): array
    {
        return app(NotificationPreferenceManager::class)
            ->getPreferencesForUser($this);
    }

    public function getNotificationPreferencesTable(): array
    {
        return app(NotificationPreferenceManager::class)
            ->getPreferencesTable($this);
    }

    public function setGroupChannelPreference(string $groupKey, string $channel, bool $enabled): int
    {
        return app(NotificationPreferenceManager::class)
            ->setGroupPreference($this, $groupKey, $channel, $enabled);
    }

    public function setChannelPreferenceForAll(string $channel, bool $enabled): int
    {
        return app(NotificationPreferenceManager::class)
            ->setChannelPreference($this, $channel, $enabled);
    }

    public function setAllChannelsForNotification(string $notificationType, bool $enabled): int
    {
        return app(NotificationPreferenceManager::class)
            ->setNotificationPreference($this, $notificationType, $enabled);
    }
}
