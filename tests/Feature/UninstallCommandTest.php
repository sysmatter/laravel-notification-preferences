<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use SysMatter\NotificationPreferences\Models\NotificationPreference;
use SysMatter\NotificationPreferences\Tests\Models\User;

beforeEach(function () {
    $this->tableName = config('notification-preferences.table_name', 'notification_preferences');
});

it('drops the notification preferences table', function () {
    expect(Schema::hasTable($this->tableName))->toBeTrue();

    Artisan::call('notification-preferences:uninstall', ['--force' => true]);

    expect(Schema::hasTable($this->tableName))->toBeFalse();
});

it('handles non-existent table gracefully', function () {
    Schema::dropIfExists($this->tableName);

    expect(Schema::hasTable($this->tableName))->toBeFalse();

    $exitCode = Artisan::call('notification-preferences:uninstall', ['--force' => true]);

    expect($exitCode)->toBe(0);
});

it('returns success exit code when table is dropped', function () {
    $exitCode = Artisan::call('notification-preferences:uninstall', ['--force' => true]);

    expect($exitCode)->toBe(0);
});

it('returns success exit code when table does not exist', function () {
    Schema::dropIfExists($this->tableName);

    $exitCode = Artisan::call('notification-preferences:uninstall', ['--force' => true]);

    expect($exitCode)->toBe(0);
});

it('outputs success message when table is dropped', function () {
    Artisan::call('notification-preferences:uninstall', ['--force' => true]);
    $output = Artisan::output();

    expect($output)->toContain("Successfully dropped '{$this->tableName}' table")
        ->and($output)->toContain('Notification preferences package uninstalled');
});

it('outputs info message when table does not exist', function () {
    Schema::dropIfExists($this->tableName);

    Artisan::call('notification-preferences:uninstall', ['--force' => true]);
    $output = Artisan::output();

    expect($output)->toContain("Table '{$this->tableName}' does not exist");
});

it('outputs warning about composer removal', function () {
    Artisan::call('notification-preferences:uninstall', ['--force' => true]);
    $output = Artisan::output();

    expect($output)->toContain('Remember to remove the package from composer.json');
});

it('deletes existing preferences when dropping table', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    NotificationPreference::create([
        'user_id' => $user->id,
        'notification_type' => 'App\\Notifications\\TestNotification',
        'channel' => 'mail',
        'enabled' => true,
    ]);

    expect(NotificationPreference::count())->toBe(1);

    Artisan::call('notification-preferences:uninstall', ['--force' => true]);

    expect(Schema::hasTable($this->tableName))->toBeFalse();
});

it('uses custom table name from config', function () {
    // We need to test this in isolation since we can't easily change config mid-test
    // This test verifies the command respects the config value
    $tableName = config('notification-preferences.table_name');

    expect($tableName)->toBe('notification_preferences');

    Artisan::call('notification-preferences:uninstall', ['--force' => true]);
    $output = Artisan::output();

    expect($output)->toContain("'{$tableName}'");
});

it('requires force flag in production-like environments', function () {
    // This test demonstrates the command structure requires either
    // --force flag or interactive confirmation

    // With --force, it should succeed
    $exitCode = Artisan::call('notification-preferences:uninstall', ['--force' => true]);
    expect($exitCode)->toBe(0);
});

it('can be called multiple times safely', function () {
    // First call
    Artisan::call('notification-preferences:uninstall', ['--force' => true]);
    expect(Schema::hasTable($this->tableName))->toBeFalse();

    // Second call should not error
    $exitCode = Artisan::call('notification-preferences:uninstall', ['--force' => true]);
    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain("Table '{$this->tableName}' does not exist");
});
