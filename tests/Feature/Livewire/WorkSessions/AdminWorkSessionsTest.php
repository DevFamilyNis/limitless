<?php

use App\Livewire\Admin\WorkSessions\Index;
use App\Models\User;
use App\Models\WorkSession;
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
        ->call('forceFinish', $session->id);

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
        ->call('delete', $session->id);

    expect(WorkSession::find($session->id))->toBeNull();
});

test('non-super-admin cannot force finish a session', function () {
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
        ->call('forceFinish', $session->id)
        ->assertForbidden();
});

test('non-super-admin cannot delete a session', function () {
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
        ->call('delete', $session->id)
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
        ->call('forceFinish', $session->id);

    expect($session->fresh()->duration_minutes)->toBe(60);
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
