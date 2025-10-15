<?php

namespace SysMatter\NotificationPreferences\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use SysMatter\NotificationPreferences\Concerns\HasNotificationPreferences;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasNotificationPreferences;

    protected $guarded = [];
}
