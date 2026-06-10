<?php

use App\Enums\AppSettingKey;
use App\Livewire\Admin\WorkSessions\Index;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\WorkSession;
use App\Models\WorkSessionUserSetting;
use Livewire\Livewire;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

test('admin can view work sessions index', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    $this->actingAsWithSession($admin);

    $this->get(route('admin.work-sessions.index'))->assertOk();
});

test('regular user cannot view work sessions index', function () {
    $user = User::factory()->create();
    $this->actingAsWithSession($user);

    $this->get(route('admin.work-sessions.index'))->assertForbidden();
});

test('index shows all work sessions', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    $this->actingAs($admin);

    $userA = User::factory()->create();
    $userB = User::factory()->create();

    WorkSession::create([
        'user_id' => $userA->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(4),
    ]);
    WorkSession::create([
        'user_id' => $userB->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
        'ended_at' => now()->subHour(),
        'duration_minutes' => 60,
    ]);

    Livewire::test(Index::class)
        ->assertSee($userA->name)
        ->assertSee($userB->name);
});

test('index filters by user', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    $this->actingAs($admin);

    $userA = User::factory()->create();
    $userB = User::factory()->create();

    WorkSession::create([
        'user_id' => $userA->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(4),
    ]);
    WorkSession::create([
        'user_id' => $userB->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
    ]);

    Livewire::test(Index::class)
        ->set('selectedUserId', (string) $userA->id)
        ->assertViewHas('sessions', function ($sessions) use ($userA, $userB) {
            return $sessions->contains('user_id', $userA->id)
                && ! $sessions->contains('user_id', $userB->id);
        });
});

test('super-admin can force finish an active session', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    $user = User::factory()->create();
    $session = WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
    ]);

    Livewire::test(Index::class)
        ->call('confirmFinish', $session->id)
        ->assertSet('pendingFinishId', $session->id)
        ->assertSet('showFinishConfirm', true)
        ->call('forceFinish')
        ->assertSet('showFinishConfirm', false);

    expect($session->fresh()->ended_at)->not->toBeNull()
        ->and($session->fresh()->duration_minutes)->toBeGreaterThan(0);
});

test('super-admin can delete a session', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    $user = User::factory()->create();
    $session = WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
    ]);

    Livewire::test(Index::class)
        ->call('confirmDelete', $session->id)
        ->assertSet('pendingDeleteId', $session->id)
        ->assertSet('showDeleteConfirm', true)
        ->call('delete')
        ->assertSet('showDeleteConfirm', false);

    expect(WorkSession::find($session->id))->toBeNull();
});

test('non-super-admin cannot open finish confirmation', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    $this->actingAs($admin);

    $user = User::factory()->create();
    $session = WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
    ]);

    Livewire::test(Index::class)
        ->call('confirmFinish', $session->id)
        ->assertForbidden();
});

test('non-super-admin cannot open delete confirmation', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    $this->actingAs($admin);

    $user = User::factory()->create();
    $session = WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
    ]);

    Livewire::test(Index::class)
        ->call('confirmDelete', $session->id)
        ->assertForbidden();
});

test('force finish is idempotent on already-finished session', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    $user = User::factory()->create();
    $session = WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
        'ended_at' => now()->subHour(),
        'duration_minutes' => 60,
    ]);

    Livewire::test(Index::class)
        ->call('confirmFinish', $session->id)
        ->call('forceFinish');

    expect($session->fresh()->duration_minutes)->toBe(60);
});

test('downloadReport returns pdf for all users in range', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    $this->actingAs($admin);

    $user = User::factory()->create();
    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => '2026-06-05',
        'started_at' => now()->subHours(8),
        'ended_at' => now()->subHour(),
        'duration_minutes' => 420,
    ]);

    $response = Livewire::test(Index::class)
        ->set('reportDateFrom', '2026-06-01')
        ->set('reportDateTo', '2026-06-30')
        ->call('downloadReport');

    $response->assertFileDownloaded();
});

test('downloadReport returns pdf filtered by user', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    $this->actingAs($admin);

    $user = User::factory()->create();
    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => '2026-06-05',
        'started_at' => now()->subHours(8),
        'ended_at' => now()->subHour(),
        'duration_minutes' => 420,
    ]);

    $response = Livewire::test(Index::class)
        ->set('reportUserId', (string) $user->id)
        ->set('reportDateFrom', '2026-06-01')
        ->set('reportDateTo', '2026-06-30')
        ->call('downloadReport');

    $response->assertFileDownloaded();
});

test('downloadReport requires date range', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->call('downloadReport')
        ->assertHasErrors(['reportDateFrom', 'reportDateTo']);
});

test('downloadReport requires dateTo not before dateFrom', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    $this->actingAs($admin);

    Livewire::test(Index::class)
        ->set('reportDateFrom', '2026-06-30')
        ->set('reportDateTo', '2026-06-01')
        ->call('downloadReport')
        ->assertHasErrors(['reportDateTo']);
});

test('index filters by date', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    $this->actingAs($admin);

    $user = User::factory()->create();

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => '2026-06-09',
        'started_at' => now()->subHours(4),
    ]);

    Livewire::test(Index::class)
        ->set('selectedDate', '2026-06-09')
        ->assertSee($user->name);

    Livewire::test(Index::class)
        ->set('selectedDate', '2026-06-08')
        ->assertSee('Nema pronađenih sesija.');
});

test('super-admin can open user reminder settings prefilled with global defaults', function () {
    AppSetting::setValue(AppSettingKey::WorkSessionReminderEnabled, true);
    AppSetting::setValue(AppSettingKey::WorkSessionReminderDelayMinutes, 120);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    $user = User::factory()->create();

    Livewire::test(Index::class)
        ->call('openUserSettings', $user->id)
        ->assertSet('showUserSettingsModal', true)
        ->assertSet('userSettingsUserId', $user->id)
        ->assertSet('userSettingsReminderEnabled', true)
        ->assertSet('userSettingsReminderDelayMinutes', 120);
});

test('super-admin can open user reminder settings prefilled with existing override', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    $user = User::factory()->create();
    WorkSessionUserSetting::create([
        'user_id' => $user->id,
        'reminder_enabled' => true,
        'reminder_delay_minutes' => 480,
    ]);

    Livewire::test(Index::class)
        ->call('openUserSettings', $user->id)
        ->assertSet('userSettingsReminderDelayMinutes', 480);
});

test('super-admin can save a per-user reminder override', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    $user = User::factory()->create();

    Livewire::test(Index::class)
        ->call('openUserSettings', $user->id)
        ->set('userSettingsReminderEnabled', true)
        ->set('userSettingsReminderDelayMinutes', 480)
        ->call('saveUserSettings')
        ->assertHasNoErrors()
        ->assertSet('showUserSettingsModal', false);

    $this->assertDatabaseHas('work_session_user_settings', [
        'user_id' => $user->id,
        'reminder_enabled' => true,
        'reminder_delay_minutes' => 480,
    ]);
});

test('super-admin can disable reminder for a specific user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    $user = User::factory()->create();

    Livewire::test(Index::class)
        ->call('openUserSettings', $user->id)
        ->set('userSettingsReminderEnabled', false)
        ->set('userSettingsReminderDelayMinutes', 5)
        ->call('saveUserSettings')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('work_session_user_settings', [
        'user_id' => $user->id,
        'reminder_enabled' => false,
        'reminder_delay_minutes' => null,
    ]);
});

test('non-super-admin cannot open user reminder settings', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('manage-users');
    $this->actingAs($admin);

    $user = User::factory()->create();

    Livewire::test(Index::class)
        ->call('openUserSettings', $user->id)
        ->assertForbidden();
});
