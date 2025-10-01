<?php

namespace SysMatter\NotificationPreferences;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use SysMatter\NotificationPreferences\Models\NotificationPreference;

class NotificationPreferenceManager
{
    public function getPreference(Model $user, string $notificationType, string $channel): bool
    {
        $cacheKey = $this->getCacheKey($user->id, $notificationType, $channel);

        if (config('notification-preferences.cache.enabled')) {
            return Cache::remember($cacheKey, config('notification-preferences.cache.ttl'), function () use ($user, $notificationType, $channel) {
                return $this->fetchPreference($user, $notificationType, $channel);
            });
        }

        return $this->fetchPreference($user, $notificationType, $channel);
    }

    public function setPreference(Model $user, string $notificationType, string $channel, bool $enabled): void
    {
        NotificationPreference::updateOrCreate([
            'user_id' => $user->id,
            'notification_type' => $notificationType,
            'channel' => $channel,
        ], [
            'enabled' => $enabled,
        ]);

        $this->clearCache($user->id, $notificationType, $channel);
    }

    /**
     * Get preferences table structure for building forms.
     *
     * @return array<int, array{
     *     notification_type: string,
     *     notification_name: string,
     *     channels: array<string, array{name: string, enabled: bool}>,
     *     group: string|null
     * }>|array<string, array{
     *     name: string,
     *     description: string|null,
     *     notifications: array<int, array{
     *         notification_type: string,
     *         notification_name: string,
     *         channels: array<string, array{name: string, enabled: bool}>
     *     }>
     * }>
     */
    public function getPreferencesTable(Model $user, bool $grouped = false): array
    {
        $registry = app(NotificationRegistry::class);
        $registeredNotifications = $registry->getRegisteredNotifications();
        $channels = config('notification-preferences.default_channels');

        $table = [];

        foreach ($registeredNotifications as $notificationType => $notificationData) {
            $row = [
                'notification_type' => $notificationType,
                'notification_name' => $notificationData['name'],
                'channels' => [],
            ];

            if (!$grouped) {
                $row['group'] = $notificationData['group'];
            }

            foreach ($notificationData['channels'] as $channel) {
                if (isset($channels[$channel])) {
                    $row['channels'][$channel] = [
                        'name' => $channels[$channel],
                        'enabled' => $this->getPreference($user, $notificationType, $channel),
                    ];
                }
            }

            if ($grouped && $notificationData['group']) {
                $table[$notificationData['group']]['notifications'][] = $row;
            } elseif ($grouped) {
                $table['uncategorized']['notifications'][] = $row;
            } else {
                $table[] = $row;
            }
        }

        // Add group metadata if grouped
        if ($grouped) {
            $registeredGroups = $registry->getRegisteredGroups();

            foreach ($table as $groupKey => &$groupData) {
                if (isset($registeredGroups[$groupKey])) {
                    $groupData['name'] = $registeredGroups[$groupKey]['name'];
                    $groupData['description'] = $registeredGroups[$groupKey]['description'];
                } elseif ($groupKey === 'uncategorized') {
                    $groupData['name'] = 'Other';
                    $groupData['description'] = null;
                }
            }
        }

        return $table;
    }

    /**
     * @param array<string, array<string, bool>> $preferences
     */
    public function updatePreferences(Model $user, array $preferences): void
    {
        foreach ($preferences as $notificationType => $channels) {
            foreach ($channels as $channel => $enabled) {
                $this->setPreference($user, $notificationType, $channel, (bool)$enabled);
            }
        }
    }

    private function fetchPreference(Model $user, string $notificationType, string $channel): bool
    {
        $preference = NotificationPreference::forUser($user->id)
            ->forNotification($notificationType)
            ->forChannel($channel)
            ->first();

        return $preference->enabled ?? config('notification-preferences.default_enabled', true);
    }

    private function getCacheKey(int|string $userId, string $notificationType, string $channel): string
    {
        $prefix = config('notification-preferences.cache.prefix');

        return "{$prefix}:{$userId}:{$notificationType}:{$channel}";
    }

    private function clearCache(int|string $userId, string $notificationType, string $channel): void
    {
        if (config('notification-preferences.cache.enabled')) {
            $cacheKey = $this->getCacheKey($userId, $notificationType, $channel);
            Cache::forget($cacheKey);
        }
    }
}
