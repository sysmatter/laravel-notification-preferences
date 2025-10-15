<?php

namespace SysMatter\NotificationPreferences;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use SysMatter\NotificationPreferences\Models\NotificationPreference;

class NotificationPreferenceManager
{
    /**
     * Get fresh config each time to support runtime changes in tests
     *
     * @return array<string, mixed>
     */
    protected function getConfig(): array
    {
        return config('notification-preferences', []);
    }

    /**
     * Check if a channel is enabled for a user and notification type.
     */
    public function isChannelEnabled(
        Authenticatable $user,
        string          $notificationType,
        string          $channel
    ): bool {
        $userId = $this->getUserId($user);
        $cacheKey = "notification_prefs.{$userId}.{$notificationType}.{$channel}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($userId, $notificationType, $channel) {
            $preference = NotificationPreference::where('user_id', $userId)
                ->forNotification($notificationType)
                ->forChannel($channel)
                ->first();

            if ($preference !== null) {
                return $preference->enabled;
            }

            return $this->getDefaultPreference($notificationType, $channel);
        });
    }

    /**
     * Filter channels based on user preferences.
     *
     * @param array<int, string> $channels
     * @return array<int, string>
     */
    public function filterChannels(
        Authenticatable $user,
        string          $notificationType,
        array           $channels
    ): array {
        $config = $this->getConfig();

        return array_values(array_filter($channels, function ($channel) use ($user, $notificationType, $config) {
            // Check for forced channels that can't be disabled
            $forcedChannels = $config['notifications'][$notificationType]['force_channels'] ?? [];
            if (in_array($channel, $forcedChannels, true)) {
                return true;
            }

            return $this->isChannelEnabled($user, $notificationType, $channel);
        }));
    }

    /**
     * Set a preference for a user.
     */
    public function setPreference(
        Authenticatable $user,
        string          $notificationType,
        string          $channel,
        bool            $enabled
    ): NotificationPreference {
        $userId = $this->getUserId($user);

        $preference = NotificationPreference::updateOrCreate(
            [
                'user_id' => $userId,
                'notification_type' => $notificationType,
                'channel' => $channel,
            ],
            [
                'enabled' => $enabled,
            ]
        );

        $this->clearCache($userId, $notificationType, $channel);

        return $preference;
    }

    /**
     * Get all preferences for a user.
     *
     * @return array<int, array{notification_type: string, channel: string, enabled: bool}>
     */
    public function getPreferencesForUser(Authenticatable $user): array
    {
        $userId = $this->getUserId($user);
        $preferences = NotificationPreference::where('user_id', $userId)->get();

        return $preferences->map(function (NotificationPreference $pref) {
            return [
                'notification_type' => $pref->notification_type,
                'channel' => $pref->channel,
                'enabled' => $pref->enabled,
            ];
        })->toArray();
    }

    /**
     * Get preferences structured as a table for UI display.
     *
     * @return array<int, array{group: string, label: string, description: string|null, notifications: array<int, array{type: string, label: string, description: string|null, channels: array<string, array{enabled: bool, forced: bool}>}>}>
     */
    public function getPreferencesTable(Authenticatable $user): array
    {
        $userId = $this->getUserId($user);
        $config = $this->getConfig();

        /** @var array<string, array{label?: string, description?: string, order?: int}> */
        $groups = $config['groups'] ?? [];

        /** @var array<string, array{group?: string, label?: string, description?: string, default_preference?: string, default_channels?: array<int, string>, force_channels?: array<int, string>, order?: int}> */
        $notifications = $config['notifications'] ?? [];

        $channels = $this->getEnabledChannels();

        $userPreferences = NotificationPreference::where('user_id', $userId)
            ->get()
            ->keyBy(function (NotificationPreference $pref) {
                return "{$pref->notification_type}:{$pref->channel}";
            });

        // Group notifications - manually to preserve keys
        $groupedNotifications = [];
        foreach ($notifications as $notificationClass => $config) {
            $group = $config['group'] ?? 'ungrouped';
            if (!isset($groupedNotifications[$group])) {
                $groupedNotifications[$group] = [];
            }
            $groupedNotifications[$group][$notificationClass] = $config;
        }

        // Sort groups by order
        uksort($groupedNotifications, function ($a, $b) use ($groups) {
            $orderA = $groups[$a]['order'] ?? 999;
            $orderB = $groups[$b]['order'] ?? 999;
            return $orderA <=> $orderB;
        });

        $result = [];
        foreach ($groupedNotifications as $groupKey => $groupNotifications) {
            // Sort notifications within group by order
            uasort($groupNotifications, function ($a, $b) {
                $orderA = $a['order'] ?? 999;
                $orderB = $b['order'] ?? 999;
                return $orderA <=> $orderB;
            });

            $notificationList = [];
            foreach ($groupNotifications as $notificationType => $notifConfig) {
                /** @var array<string, array{enabled: bool, forced: bool}> */
                $channelPreferences = [];

                foreach ($channels as $channelKey => $channelConfig) {
                    $prefKey = "{$notificationType}:{$channelKey}";
                    $forcedChannels = $notifConfig['force_channels'] ?? [];

                    $preference = $userPreferences->get($prefKey);
                    $channelPreferences[$channelKey] = [
                        'enabled' => $preference !== null
                            ? $preference->enabled
                            : $this->getDefaultPreference($notificationType, $channelKey),
                        'forced' => in_array($channelKey, $forcedChannels, true),
                    ];
                }

                $notificationList[] = [
                    'type' => $notificationType,
                    'label' => $notifConfig['label'] ?? class_basename($notificationType),
                    'description' => $notifConfig['description'] ?? null,
                    'channels' => $channelPreferences,
                ];
            }

            $result[] = [
                'group' => $groupKey,
                'label' => $groups[$groupKey]['label'] ?? ucfirst($groupKey),
                'description' => $groups[$groupKey]['description'] ?? null,
                'notifications' => $notificationList,
            ];
        }

        return $result;
    }

    /**
     * Get the default preference for a notification type and channel.
     */
    protected function getDefaultPreference(string $notificationType, string $channel): bool
    {
        $config = $this->getConfig();
        $notificationConfig = $config['notifications'][$notificationType] ?? [];

        // Check for channel-specific defaults
        if (isset($notificationConfig['default_channels'])) {
            return in_array($channel, $notificationConfig['default_channels'], true);
        }

        // Check notification-level default
        if (isset($notificationConfig['default_preference'])) {
            return $notificationConfig['default_preference'] === 'opt_in';
        }

        // Check group-level default
        $groupKey = $notificationConfig['group'] ?? null;
        if ($groupKey && isset($config['groups'][$groupKey]['default_preference'])) {
            return $config['groups'][$groupKey]['default_preference'] === 'opt_in';
        }

        // Fall back to global default
        return ($config['default_preference'] ?? 'opt_in') === 'opt_in';
    }

    /**
     * Get enabled channels from config.
     *
     * @return array<string, array{label: string, enabled: bool}>
     */
    protected function getEnabledChannels(): array
    {
        $config = $this->getConfig();

        /** @var array<string, array{label: string, enabled?: bool}> */
        $channels = $config['channels'] ?? [];

        return collect($channels)
            ->filter(fn ($config) => $config['enabled'] ?? true)
            ->toArray();
    }

    /**
     * Clear cache for a specific preference.
     *
     * @param int|string $userId
     */
    protected function clearCache($userId, string $notificationType, string $channel): void
    {
        $cacheKey = "notification_prefs.{$userId}.{$notificationType}.{$channel}";
        Cache::forget($cacheKey);
    }

    /**
     * Clear all cached preferences for a user.
     *
     * @param int|string $userId
     */
    public function clearUserCache($userId): void
    {
        // Since we can't use wildcards with all cache drivers,
        // we need to clear the entire cache or use tags
        // For testing and simplicity, we'll flush the cache
        Cache::flush();
    }

    /**
     * Get user ID from Authenticatable instance.
     *
     * @return int|string
     */
    protected function getUserId(Authenticatable $user)
    {
        return $user->getAuthIdentifier();
    }
}
