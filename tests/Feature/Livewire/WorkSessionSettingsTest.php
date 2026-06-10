<?php

use App\Enums\AppSettingKey;
use App\Livewire\Settings\WorkSessionSettings;
use App\Models\AppSetting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

test('settings form loads with default values when not configured', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    Livewire::test(WorkSessionSettings::class)
        ->assertSet('reminderEnabled', true)
        ->assertSet('reminderDelayMinutes', 120);
});

test('settings form loads configured values', function () {
    AppSetting::setValue(AppSettingKey::WorkSessionReminderDelayMinutes, 60);
    AppSetting::setValue(AppSettingKey::WorkSessionReminderEnabled, false);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    Livewire::test(WorkSessionSettings::class)
        ->assertSet('reminderEnabled', false)
        ->assertSet('reminderDelayMinutes', 60);
});

test('save persists reminder delay to app settings', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    Livewire::test(WorkSessionSettings::class)
        ->set('reminderDelayMinutes', 90)
        ->call('save')
        ->assertHasNoErrors();

    expect((int) AppSetting::getValue(AppSettingKey::WorkSessionReminderDelayMinutes))->toBe(90);
});

test('save validates minimum delay of 15 minutes', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    Livewire::test(WorkSessionSettings::class)
        ->set('reminderDelayMinutes', 5)
        ->call('save')
        ->assertHasErrors(['reminderDelayMinutes']);
});

test('save can disable reminder without validating delay', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    Livewire::test(WorkSessionSettings::class)
        ->set('reminderEnabled', false)
        ->set('reminderDelayMinutes', 5)
        ->call('save')
        ->assertHasNoErrors();

    expect((bool) AppSetting::getValue(AppSettingKey::WorkSessionReminderEnabled))->toBeFalse();
});

test('non-super-admin cannot view work session settings', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage-settings');
    $this->actingAs($user);

    Livewire::test(WorkSessionSettings::class)
        ->assertForbidden();
});
