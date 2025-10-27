# Laravel Notification Preferences

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sysmatter/laravel-notification-preferences.svg?style=flat-square)](https://packagist.org/packages/sysmatter/laravel-notification-preferences)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/sysmatter/laravel-notification-preferences/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/sysmatter/laravel-notification-preferences/actions?query=workflow%3Atests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/sysmatter/laravel-notification-preferences/code-style.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/sysmatter/laravel-notification-preferences/actions?query=workflow%3A"code+style"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/sysmatter/laravel-notification-preferences.svg?style=flat-square)](https://packagist.org/packages/sysmatter/laravel-notification-preferences)

A Laravel package for managing user notification preferences with support for multiple channels, notification groups,
and a structured table output for display.

## Features

- ✅ User-specific notification preferences per channel
- ✅ Automatic channel filtering for all notifications
- ✅ Opt-in trait for granular control
- ✅ Notification grouping for organization
- ✅ Configurable default behaviors (opt-in/opt-out)
- ✅ Forced channels that cannot be disabled
- ✅ Structured table output for UI rendering
- ✅ Laravel 12 compatible
- ✅ PostgreSQL 18 support

## Requirements

- PHP 8.2+
- Laravel 11+
- Any Laravel-supported database (PostgreSQL, MySQL, SQLite, etc.)

## Installation

```bash
composer require sysmatter/laravel-notification-preferences
```

Publish the config and migrations:

```bash
php artisan vendor:publish --tag=notification-preferences-config
php artisan vendor:publish --tag=notification-preferences-migrations
```

Run migrations:

```bash
php artisan migrate
```

## Uninstalling

To completely remove the package and its data:

```bash
# This will drop the notification_preferences table
php artisan notification-preferences:uninstall

# Or force without confirmation
php artisan notification-preferences:uninstall --force

# Then remove from composer
composer remove sysmatter/laravel-notification-preferences

# Optionally remove published config
rm config/notification-preferences.php
```

**Warning:** The uninstall command permanently deletes all notification preferences. Make sure to back up your data if
needed.

## Configuration

Edit `config/notification-preferences.php`:

```php
return [
    // Define available channels
    'channels' => [
        'mail' => ['label' => 'Email', 'enabled' => true],
        'database' => ['label' => 'In-App', 'enabled' => true],
        'broadcast' => ['label' => 'Push', 'enabled' => true],
        'sms' => ['label' => 'SMS', 'enabled' => true],
    ],

    // Global default: 'opt_in' or 'opt_out'
    'default_preference' => 'opt_in',

    // Define notification groups
    'groups' => [
        'system' => [
            'label' => 'System Notifications',
            'description' => 'Important system updates',
            'default_preference' => 'opt_in',
            'order' => 1,
        ],
        'marketing' => [
            'label' => 'Marketing',
            'description' => 'Promotional content',
            'default_preference' => 'opt_out',
            'order' => 2,
        ],
    ],

    // Register your notifications
    'notifications' => [
        \App\Notifications\OrderShipped::class => [
            'group' => 'system',
            'label' => 'Order Shipped',
            'description' => 'Notification when your order ships',
            'default_preference' => 'opt_in',
            'default_channels' => ['mail', 'database'],
            'force_channels' => [], // Channels that can't be disabled
            'order' => 1,
        ],
        \App\Notifications\WeeklyNewsletter::class => [
            'group' => 'marketing',
            'label' => 'Weekly Newsletter',
            'description' => 'Our weekly email digest',
            'default_channels' => ['mail'],
            'order' => 2,
        ],
    ],
];
```

## Usage

### Add Trait to User Model

```php
use SysMatter\NotificationPreferences\Concerns\HasNotificationPreferences;

class User extends Authenticatable
{
    use HasNotificationPreferences;
}
```

### Option A: Automatic Filtering (Recommended)

All registered notifications in the config will automatically filter channels based on user preferences. No changes
needed to your notification classes!

```php
// This notification will automatically respect user preferences
$user->notify(new OrderShipped($order));
```

### Option B: Explicit Control with Trait

For more control, use the `ChecksNotificationPreferences` trait in your notification:

```php
use SysMatter\NotificationPreferences\Concerns\ChecksNotificationPreferences;
use Illuminate\Notifications\Notification;

class OrderShipped extends Notification
{
    use ChecksNotificationPreferences;

    public function via($notifiable)
    {
        // Define all possible channels, preferences will filter them
        return $this->allowedChannels($notifiable, ['mail', 'database', 'broadcast']);
    }

    public function toMail($notifiable)
    {
        // ... 
    }
}
```

### Managing Preferences

```php
// Set a preference
$user->setNotificationPreference(
    OrderShipped::class,
    'mail',
    true // enabled
);

// Check a preference
$enabled = $user->getNotificationPreference(OrderShipped::class, 'mail');

// Get all preferences
$preferences = $user->getNotificationPreferences();

// Get structured table data for UI
$table = $user->getNotificationPreferencesTable();
```

## Bulk Update Operations

The package provides convenient methods for bulk updating notification preferences, making it easy to implement "disable
all emails" or "turn off marketing" features.

### Available Bulk Methods

#### Disable/Enable All Notifications in a Group for a Channel

Turn off all marketing emails:

```php
$user->setGroupChannelPreference('marketing', 'mail', false);
```

Turn on all system notifications for in-app:

```php
$user->setGroupChannelPreference('system', 'database', true);
```

#### Disable/Enable a Channel Across All Notifications

Turn off all email notifications:

```php
$user->setChannelPreferenceForAll('mail', false);
```

Enable push notifications for everything:

```php
$user->setChannelPreferenceForAll('broadcast', true);
```

#### Disable/Enable All Channels for a Notification Type

Turn off all channels for a specific notification:

```php
$user->setAllChannelsForNotification(OrderShipped::class, false);
```

Enable all channels for security alerts:

```php
$user->setAllChannelsForNotification(SecurityAlert::class, true);
```

### Return Values

All bulk methods return the **count of preferences updated**:

```php
$count = $user->setChannelPreferenceForAll('mail', false);
// Returns: 15 (updated 15 notification preferences)
```

This is useful for providing user feedback:

```php
$count = $user->setGroupChannelPreference('marketing', 'mail', false);

return response()->json([
    'message' => "Disabled email for {$count} marketing notifications"
]);
```

### Forced Channels are Skipped

Bulk operations automatically skip forced channels:

```php
'notifications' => [
    SecurityAlert::class => [
        'group' => 'security',
        'label' => 'Security Alerts',
        'force_channels' => ['mail'], // Always send emails
    ],
],
```

```php
// This will NOT disable email for SecurityAlert
$user->setChannelPreferenceForAll('mail', false);
```

### UI Implementation Examples

#### "Disable All Emails" Button

```php
public function disableAllEmails(Request $request)
{
    $count = $request->user()->setChannelPreferenceForAll('mail', false);
    
    return back()->with('success', "Disabled email notifications for {$count} notification types");
}
```

#### "Mute Marketing" Toggle

```php
public function toggleMarketing(Request $request)
{
    $enabled = $request->boolean('enabled');
    $count = $request->user()->setGroupChannelPreference('marketing', 'mail', $enabled);
    
    $action = $enabled ? 'enabled' : 'disabled';
    
    return back()->with('success', "Marketing emails {$action}");
}
```

#### "Notification Type Master Toggle"

```php
public function toggleNotificationType(Request $request, string $notificationType)
{
    $enabled = $request->boolean('enabled');
    $count = $request->user()->setAllChannelsForNotification($notificationType, $enabled);
    
    return response()->json([
        'updated' => $count,
        'enabled' => $enabled
    ]);
}
```

### Direct Manager Access

You can also use the manager directly:

```php
use SysMatter\NotificationPreferences\NotificationPreferenceManager;

$manager = app(NotificationPreferenceManager::class);

// Same methods available
$count = $manager->setGroupPreference($user, 'marketing', 'mail', false);
$count = $manager->setChannelPreference($user, 'mail', false);
$count = $manager->setNotificationPreference($user, OrderShipped::class, false);
```

### Building a Preferences UI

Here's a complete example of a preferences page controller:

```php
public function index(Request $request)
{
    $user = $request->user();
    
    return view('preferences.notifications', [
        'preferences' => $user->getNotificationPreferencesTable(),
        'channels' => config('notification-preferences.channels'),
    ]);
}

public function update(Request $request)
{
    $user = $request->user();
    
    $validated = $request->validate([
        'action' => 'required|in:single,group,channel,notification',
        'notification_type' => 'required_if:action,single,notification',
        'channel' => 'required_if:action,single,group,channel',
        'group' => 'required_if:action,group',
        'enabled' => 'required|boolean',
    ]);
    
    $count = match($validated['action']) {
        'single' => $user->setNotificationPreference(
            $validated['notification_type'],
            $validated['channel'],
            $validated['enabled']
        ) ? 1 : 0,
        
        'group' => $user->setGroupChannelPreference(
            $validated['group'],
            $validated['channel'],
            $validated['enabled']
        ),
        
        'channel' => $user->setChannelPreferenceForAll(
            $validated['channel'],
            $validated['enabled']
        ),
        
        'notification' => $user->setAllChannelsForNotification(
            $validated['notification_type'],
            $validated['enabled']
        ),
    };
    
    return response()->json([
        'success' => true,
        'count' => $count,
    ]);
}
```

### Table Structure Output

The `getNotificationPreferencesTable()` method returns data structured for easy UI rendering:

```php
[
    [
        'group' => 'system',
        'label' => 'System Notifications',
        'description' => 'Important system updates',
        'notifications' => [
            [
                'type' => 'App\Notifications\OrderShipped',
                'label' => 'Order Shipped',
                'description' => 'Notification when your order ships',
                'channels' => [
                    'mail' => ['enabled' => true, 'forced' => false],
                    'database' => ['enabled' => true, 'forced' => false],
                    'broadcast' => ['enabled' => false, 'forced' => false],
                ],
            ],
        ],
    ],
    // ... more groups
]
```

## API Endpoints Example

Create a controller to manage preferences:

```php
use SysMatter\NotificationPreferences\NotificationPreferenceManager;

class NotificationPreferenceController extends Controller
{
    public function index(Request $request)
    {
        return inertia('Settings/Notifications', [
            'preferences' => $request->user()->getNotificationPreferencesTable(),
        ]);
    }

    public function update(Request $request, NotificationPreferenceManager $manager)
    {
        $validated = $request->validate([
            'notification_type' => 'required|string',
            'channel' => 'required|string',
            'enabled' => 'required|boolean',
        ]);

        $manager->setPreference(
            $request->user(),
            $validated['notification_type'],
            $validated['channel'],
            $validated['enabled']
        );

        return back();
    }
}
```

Routes:

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/settings/notifications', [NotificationPreferenceController::class, 'index']);
    Route::put('/settings/notifications', [NotificationPreferenceController::class, 'update']);
});
```

## Frontend Example (Inertia/React 19)

```tsx
import {useForm} from '@inertiajs/react';
import {useState} from 'react';

interface Channel {
    enabled: boolean;
    forced: boolean;
}

interface Notification {
    type: string;
    label: string;
    description: string | null;
    channels: Record<string, Channel>;
}

interface Group {
    group: string;
    label: string;
    description: string | null;
    notifications: Notification[];
}

interface Props {
    preferences: Group[];
}

export default function NotificationPreferences({preferences}: Props) {
    const [channels] = useState(() => {
        // Extract channel names from first notification
        if (preferences.length > 0 && preferences[0].notifications.length > 0) {
            return Object.keys(preferences[0].notifications[0].channels);
        }
        return [];
    });

    const handleToggle = (notificationType: string, channel: string, currentValue: boolean) => {
        router.put(
            notificationPreferenceController.update.url(),
            {
                notification_type: notificationType,
                channel,
                enabled: !currentValue,
            },
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <div className="space-y-8">
            <div>
                <h2 className="text-2xl font-bold">Notification Preferences</h2>
                <p className="text-gray-600 mt-2">
                    Manage how you receive notifications
                </p>
            </div>

            {preferences.map((group) => (
                <div key={group.group} className="border rounded-lg overflow-hidden">
                    <div className="bg-gray-50 px-6 py-4 border-b">
                        <h3 className="font-semibold text-lg">{group.label}</h3>
                        {group.description && (
                            <p className="text-sm text-gray-600 mt-1">{group.description}</p>
                        )}
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-gray-50 border-b">
                            <tr>
                                <th className="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                    Notification
                                </th>
                                {channels.map((channel) => (
                                    <th
                                        key={channel}
                                        className="px-6 py-3 text-center text-sm font-medium text-gray-700"
                                    >
                                        {channel.charAt(0).toUpperCase() + channel.slice(1)}
                                    </th>
                                ))}
                            </tr>
                            </thead>
                            <tbody className="divide-y">
                            {group.notifications.map((notification) => (
                                <tr key={notification.type} className="hover:bg-gray-50">
                                    <td className="px-6 py-4">
                                        <div>
                                            <div className="font-medium text-gray-900">
                                                {notification.label}
                                            </div>
                                            {notification.description && (
                                                <div className="text-sm text-gray-500 mt-1">
                                                    {notification.description}
                                                </div>
                                            )}
                                        </div>
                                    </td>
                                    {channels.map((channelKey) => {
                                        const channel = notification.channels[channelKey];
                                        return (
                                            <td key={channelKey} className="px-6 py-4 text-center">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        !channel.forced &&
                                                        handleToggle(
                                                            notification.type,
                                                            channelKey,
                                                            channel.enabled
                                                        )
                                                    }
                                                    disabled={channel.forced}
                                                    className={`
                              relative inline-flex h-6 w-11 items-center rounded-full
                              transition-colors focus:outline-none focus:ring-2 
                              focus:ring-blue-500 focus:ring-offset-2
                              ${
                                                        channel.enabled
                                                            ? 'bg-blue-600'
                                                            : 'bg-gray-200'
                                                    }
                              ${
                                                        channel.forced
                                                            ? 'opacity-50 cursor-not-allowed'
                                                            : 'cursor-pointer'
                                                    }
                            `}
                                                    title={
                                                        channel.forced
                                                            ? 'This notification cannot be disabled'
                                                            : undefined
                                                    }
                                                >
                            <span
                                className={`
                                inline-block h-4 w-4 transform rounded-full 
                                bg-white transition-transform
                                ${
                                    channel.enabled
                                        ? 'translate-x-6'
                                        : 'translate-x-1'
                                }
                              `}
                            />
                                                </button>
                                            </td>
                                        );
                                    })}
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            ))}
        </div>
    );
}
```

## Testing with Pest

```php
use SysMatter\NotificationPreferences\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\OrderShipped;

it('filters channels based on user preferences', function () {
    $user = User::factory()->create();
    
    $user->setNotificationPreference(OrderShipped::class, 'mail', false);
    
    expect($user->getNotificationPreference(OrderShipped::class, 'mail'))
        ->toBeFalse();
});

it('returns structured table data', function () {
    $user = User::factory()->create();
    
    $table = $user->getNotificationPreferencesTable();
    
    expect($table)
        ->toBeArray()
        ->and($table[0])->toHaveKeys(['group', 'label', 'notifications'])
        ->and($table[0]['notifications'][0])->toHaveKeys(['type', 'label', 'channels']);
});
```

## Testing with Pest

The package includes a comprehensive test suite using Pest.

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run PHPStan analysis
composer analyse
```

### Test Structure

- **Unit Tests**: Test individual components in isolation
    - `NotificationPreferenceTest.php` - Model tests
    - `NotificationPreferenceManagerTest.php` - Manager logic tests
    - `PreferencesTableTest.php` - Table structure tests
    - `HasNotificationPreferencesTest.php` - Trait tests

- **Feature Tests**: Test integrated behavior
    - `NotificationFilteringTest.php` - Channel filtering tests
    - `DefaultPreferencesTest.php` - Default behavior tests

### Example Test

```php
use SysMatter\NotificationPreferences\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\OrderShipped;

it('filters channels based on user preferences', function () {
    $user = User::factory()->create();
    
    $user->setNotificationPreference(OrderShipped::class, 'mail', false);
    
    expect($user->getNotificationPreference(OrderShipped::class, 'mail'))
        ->toBeFalse();
});
```

## Advanced Features

### Forced Channels

Prevent users from disabling critical notifications on certain channels:

```php
'notifications' => [
    \App\Notifications\SecurityAlert::class => [
        'group' => 'system',
        'label' => 'Security Alerts',
        'force_channels' => ['mail', 'database'], // Can't be disabled
    ],
],
```

### Per-Channel Defaults

Set different defaults for each channel:

```php
'notifications' => [
    \App\Notifications\OrderShipped::class => [
        'group' => 'system',
        'label' => 'Order Shipped',
        'default_channels' => ['mail', 'database'], // Only these enabled by default
    ],
],
```

### Cache Management

The package caches preferences for performance. Clear cache when needed:

```php
use SysMatter\NotificationPreferences\NotificationPreferenceManager;

$manager = app(NotificationPreferenceManager::class);
$manager->clearUserCache($userId);
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

Please review [our security policy](SECURITY.md) for reporting vulnerabilities.

## Credits

- [Shavonn Brown](https://github.com/sysmatter)

## License

MIT License. See [LICENSE](LICENSE.md) file for details.
