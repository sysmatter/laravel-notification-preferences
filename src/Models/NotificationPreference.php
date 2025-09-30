<?php

namespace SysMatter\NotificationPreferences\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notification_type',
        'channel',
        'enabled',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('notification-preferences.user_model'));
    }

    #[Scope]
    protected function forUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    #[Scope]
    protected function forNotification(Builder $query, string $notificationType): Builder
    {
        return $query->where('notification_type', $notificationType);
    }

    #[Scope]
    protected function forChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    #[Scope]
    protected function enabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }
}
