<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The model that represents users in your application
    |
    */
    'user_model' => 'App\Models\User',

    /*
    |--------------------------------------------------------------------------
    | Default Channels
    |--------------------------------------------------------------------------
    |
    | Default notification channels available in your application
    |
    */
    'default_channels' => [
        'mail' => 'Email',
        'database' => 'In-App',
        'sms' => 'SMS',
        'push' => 'Push Notifications',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Preferences
    |--------------------------------------------------------------------------
    |
    | Default preference state for new notifications/users
    |
    */
    'default_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Cache preferences for performance
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'notification_preferences',
    ],
];
