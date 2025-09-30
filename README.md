# Laravel User Notification Preferences

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sysmatter/laravel-notification-preferences.svg?style=flat-square)](https://packagist.org/packages/sysmatter/laravel-notification-preferences)
[![Total Downloads](https://img.shields.io/packagist/dt/sysmatter/laravel-notification-preferences.svg?style=flat-square)](https://packagist.org/packages/sysmatter/laravel-notification-preferences)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/sysmatter/laravel-notification-preferences/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/sysmatter/laravel-notification-preferences/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/sysmatter/laravel-notification-preferences/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/sysmatter/laravel-notification-preferences/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)

A Laravel package that allows users to manage their notification preferences across different channels. Users can enable
or disable specific notifications for email, SMS, push notifications, and more.

## Features

- 🔧 **Seamless Integration**: Works with Laravel's built-in notification system
- 📊 **Table-Ready Output**: Perfect for building settings forms with notification/channel grids
- 🚀 **Automatic Filtering**: Notifications are automatically filtered based on user preferences
- 💾 **Caching Support**: Built-in caching for performance optimization
- 🎯 **Channel Flexibility**: Support for any notification channel (mail, SMS, push, database, etc.)
- 🧪 **Fully Tested**: Comprehensive test suite with Pest

## Requirements

- PHP 8.2, 8.3, 8.4
- Laravel 11, 12

## Installation

Install the package via Composer:

```bash
composer require sysmatter/laravel-notification-preferences
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="notification-preferences-migrations"
php artisan migrate
```

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag="notification-preferences-config"
```

This is the contents of the published config file:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The model that represents users in your application
    |
    */
    'user_model' => env('NOTIFICATION_PREFERENCES_USER_MODEL', 'App\Models\User'),

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

```

## Usage

### 1. Add the Trait to Your User Model

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use SysMatter\NotificationPreferences\Traits\HasNotificationPreferences;

class User extends Authenticatable
{
    use HasNotificationPreferences;
    
    // ... rest of your user model
}
```

### 2. Register Your Notifications

In your `AppServiceProvider`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SysMatter\NotificationPreferences\NotificationRegistry;
use App\Notifications\OrderShipped;
use App\Notifications\PaymentReceived;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $registry = app(NotificationRegistry::class);
        
        $registry->register(
            OrderShipped::class,
            'Order Updates',
            ['mail', 'database', 'sms']
        );
        
        $registry->register(
            PaymentReceived::class,
            'Payment Notifications',
            ['mail', 'database']
        );
    }
}
```

### 3. Update Your Notification Classes

Add the preference-aware trait to your notifications:

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use SysMatter\NotificationPreferences\Traits\HasPreferenceAwareNotifications;

class OrderShipped extends Notification
{
    use HasPreferenceAwareNotifications;

    protected function getOriginalChannels($notifiable): array
    {
        return ['mail', 'database', 'sms'];
    }

    // ... rest of your notification implementation
}
```

### 4. Create a Settings Form

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationPreferencesController extends Controller
{
    public function show(Request $request)
    {
        $request->user()->getNotificationPreferencesTable();

        return view('notification-preferences', compact('preferencesTable'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'preferences' => 'required|array',
            'preferences.*' => 'array',
            'preferences.*.*' => 'boolean',
        ]);

        $request->user()->updateNotificationPreferences($request->input('preferences'));

        return back()->with('success', 'Preferences updated successfully!');
    }
}
```

### 5. Build the Settings UI

The package provides table-ready data structure:

```blade
@foreach($preferencesTable as $notification)
    <tr>
        <td>{{ $notification['notification_name'] }}</td>
        @foreach($notification['channels'] as $channel => $channelData)
            <td>
                <input 
                    type="checkbox" 
                    name="preferences[{{ $notification['notification_type'] }}][{{ $channel }}]"
                    value="1"
                    {{ $channelData['enabled'] ? 'checked' : '' }}
                >
                {{ $channelData['name'] }}
            </td>
        @endforeach
    </tr>
@endforeach
```

## Usage with Inertia.js + React

### Controller Setup

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationPreferencesController extends Controller
{
    public function show(Request $request): Response
    {
        return Inertia::render('account/notification-preferences', [
            'preferencesTable' => $request->user()->getNotificationPreferencesTable(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'preferences' => 'required|array',
            'preferences.*' => 'array',
            'preferences.*.*' => 'boolean',
        ]);

        $request->user()->updateNotificationPreferences($request->input('preferences'));

        return back()->with('success', 'Notification preferences updated successfully!');
    }
}
```

### Form-Based React Component

```tsx
// resources/js/Pages/NotificationPreferences.tsx
import React from 'react';
import {Head, useForm} from '@inertiajs/react';

interface Channel {
    name: string;
    enabled: boolean;
}

interface NotificationRow {
    notification_type: string;
    notification_name: string;
    channels: Record<string, Channel>;
}

interface Props {
    preferencesTable: NotificationRow[];
    flash?: {
        success?: string;
    };
}

export default function NotificationPreferences({preferencesTable, flash}: Props) {
    // Use Inertia's useForm with the preferences data structure
    const {data, setData, post, processing} = useForm({
        preferences: Object.fromEntries(
            preferencesTable.map(notification => [
                notification.notification_type,
                Object.fromEntries(
                    Object.entries(notification.channels).map(([channel, channelData]) => [
                        channel,
                        channelData.enabled
                    ])
                )
            ])
        )
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('notification-preferences.update'));
    };

    const updatePreference = (notificationType: string, channel: string, enabled: boolean) => {
        setData('preferences', {
            ...data.preferences,
            [notificationType]: {
                ...data.preferences[notificationType],
                [channel]: enabled
            }
        });
    };

    // Get all unique channels for headers
    const allChannels = Array.from(new Set(
        preferencesTable.flatMap(n => Object.keys(n.channels))
    ));

    const getChannelName = (channel: string): string => {
        const firstNotificationWithChannel = preferencesTable.find(n => n.channels[channel]);
        return firstNotificationWithChannel?.channels[channel]?.name || channel;
    };

    return (
        <>
            <Head title="Notification Preferences"/>

            <div className="max-w-6xl mx-auto py-8 px-4">
                {flash?.success && (
                    <div className="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {flash.success}
                    </div>
                )}

                <h1 className="text-2xl font-bold mb-6">Notification Preferences</h1>

                <div className="bg-white shadow rounded-lg overflow-hidden">
                    <form onSubmit={handleSubmit}>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Notification Type
                                    </th>
                                    {allChannels.map(channel => (
                                        <th key={channel}
                                            className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {getChannelName(channel)}
                                        </th>
                                    ))}
                                </tr>
                                </thead>

                                <tbody className="bg-white divide-y divide-gray-200">
                                {preferencesTable.map(notification => (
                                    <tr key={notification.notification_type} className="hover:bg-gray-50">
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {notification.notification_name}
                                        </td>

                                        {allChannels.map(channel => {
                                            const channelData = notification.channels[channel];

                                            return (
                                                <td key={channel} className="px-6 py-4 whitespace-nowrap text-center">
                                                    {channelData ? (
                                                        <input
                                                            type="checkbox"
                                                            checked={data.preferences[notification.notification_type]?.[channel] || false}
                                                            onChange={(e) => updatePreference(
                                                                notification.notification_type,
                                                                channel,
                                                                e.target.checked
                                                            )}
                                                            className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                        />
                                                    ) : (
                                                        <span className="text-gray-400">—</span>
                                                    )}
                                                </td>
                                            );
                                        })}
                                    </tr>
                                ))}
                                </tbody>
                            </table>
                        </div>

                        <div className="px-6 py-4 bg-gray-50 text-right">
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
                            >
                                {processing ? 'Saving...' : 'Save Preferences'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
}
```

### Routes

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/notification-preferences', [NotificationPreferencesController::class, 'show'])
        ->name('notification-preferences.show');
    Route::post('/notification-preferences', [NotificationPreferencesController::class, 'update'])
        ->name('notification-preferences.update');
});
```

### Key Advantages of the Inertia Form-Based Approach

1. **Simple State Management**: Uses Inertia's `useForm` - no complex React state
2. **Type Safety**: Full TypeScript support with proper interfaces
3. **Automatic Form Handling**: Inertia manages serialization and validation
4. **Loading States**: Built-in `processing` state for UI feedback
5. **Flash Messages**: Automatic success/error message handling
6. **Progressive Enhancement**: Works even if JavaScript fails

## Usage

### Setting Individual Preferences

```php
// Disable email notifications for order updates
$user->setNotificationPreference(OrderShipped::class, 'mail', false);

// Enable SMS notifications for payments
$user->setNotificationPreference(PaymentReceived::class, 'sms', true);
```

### Getting Preferences

```php
// Check if user wants email notifications for orders
$wantsEmail = $user->getNotificationPreference(OrderShipped::class, 'mail');

// Get the full table structure for building forms
$preferencesTable = $user->getNotificationPreferencesTable();
```

### Bulk Updates

```php
$preferences = [
    OrderShipped::class => [
        'mail' => true,
        'sms' => false,
        'database' => true,
    ],
    PaymentReceived::class => [
        'mail' => false,
        'database' => true,
    ],
];

$user->updateNotificationPreferences($preferences);
```

### Sending Notifications (Automatic Filtering)

Just send notifications normally - the package automatically respects user preferences:

```php
// This will only send via channels the user has enabled
$user->notify(new OrderShipped($order));
```

## Configuration

The config file allows you to customize:

```php
return [
    // User model to use
    'user_model' => env('NOTIFICATION_PREFERENCES_USER_MODEL', 'App\Models\User'),

    // Available notification channels
    'default_channels' => [
        'mail' => 'Email',
        'database' => 'In-App',
        'sms' => 'SMS',
        'push' => 'Push Notifications',
    ],

    // Default state for new notifications
    'default_enabled' => true,

    // Caching settings
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'notification_preferences',
    ],
];
```

## API Reference

### User Methods (via `HasNotificationPreferences` trait)

```php
// Get a specific preference
$user->getNotificationPreference(string $notificationType, string $channel): bool

// Set a specific preference
$user->setNotificationPreference(string $notificationType, string $channel, bool $enabled): void

// Get table structure for forms
$user->getNotificationPreferencesTable(): array

// Bulk update preferences
$user->updateNotificationPreferences(array $preferences): void

// Get the relationship
$user->notificationPreferences(): HasMany
```

### Registry Methods

```php
$registry = app(\SysMatter\NotificationPreferences\NotificationRegistry::class);

// Register a notification type
$registry->register(string $notificationClass, string $name, array $channels): void

// Check if notification is registered
$registry->isRegistered(string $notificationClass): bool

// Get available channels for a notification
$registry->getChannelsForNotification(string $notificationClass): array

// Get all registered notifications
$registry->getRegisteredNotifications(): array
```

## Table Structure

The `getNotificationPreferencesTable()` method returns data structured like this:

```php
[
    [
        'notification_type' => 'App\Notifications\OrderShipped',
        'notification_name' => 'Order Updates',
        'channels' => [
            'mail' => [
                'name' => 'Email',
                'enabled' => true
            ],
            'sms' => [
                'name' => 'SMS', 
                'enabled' => false
            ]
        ]
    ],
    // ... more notifications
]
```

This structure makes it easy to build tables where:

- Each row is a notification type
- Each column is a channel
- Each cell shows if that notification/channel combination is enabled

## Testing

Run tests with:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

Format code:

```bash
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Shavonn Brown](https://github.com/sysmatter)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.