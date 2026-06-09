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

test('settings form loads with default delay when not configured', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage-settings');
    $this->actingAs($user);

    Livewire::test(WorkSessionSettings::class)
        ->assertSet('reminderDelayMinutes', 120);
});

test('settings form loads configured delay', function () {
    AppSetting::setValue(AppSettingKey::WorkSessionReminderDelayMinutes, 60);

    $user = User::factory()->create();
    $user->givePermissionTo('manage-settings');
    $this->actingAs($user);

    Livewire::test(WorkSessionSettings::class)
        ->assertSet('reminderDelayMinutes', 60);
});

test('save persists reminder delay to app settings', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage-settings');
    $this->actingAs($user);

    Livewire::test(WorkSessionSettings::class)
        ->set('reminderDelayMinutes', 90)
        ->call('save')
        ->assertHasNoErrors();

    expect((int) AppSetting::getValue(AppSettingKey::WorkSessionReminderDelayMinutes))->toBe(90);
});

test('save validates minimum delay of 15 minutes', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('manage-settings');
    $this->actingAs($user);

    Livewire::test(WorkSessionSettings::class)
        ->set('reminderDelayMinutes', 5)
        ->call('save')
        ->assertHasErrors(['reminderDelayMinutes']);
});
