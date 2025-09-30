<?php

namespace SysMatter\NotificationPreferences\Traits;

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
            ->getPreference($this, $notificationType, $channel);
    }

    public function setNotificationPreference(string $notificationType, string $channel, bool $enabled): void
    {
        app(NotificationPreferenceManager::class)
            ->setPreference($this, $notificationType, $channel, $enabled);
    }

    public function getNotificationPreferencesTable(): array
    {
        return app(NotificationPreferenceManager::class)
            ->getPreferencesTable($this);
    }

    public function updateNotificationPreferences(array $preferences): void
    {
        app(NotificationPreferenceManager::class)
            ->updatePreferences($this, $preferences);
    }
}
