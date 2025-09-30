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

    /**
     * @return array<int, array{
     *     notification_type: string,
     *     notification_name: string,
     *     channels: array<string, array{name: string, enabled: bool}>
     * }>
     */
    public function getNotificationPreferencesTable(): array
    {
        return app(NotificationPreferenceManager::class)
            ->getPreferencesTable($this);
    }

    /**
     * @param  array<string, array<string, bool>>  $preferences
     */
    public function updateNotificationPreferences(array $preferences): void
    {
        app(NotificationPreferenceManager::class)
            ->updatePreferences($this, $preferences);
    }
}
