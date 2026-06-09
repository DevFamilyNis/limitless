<?php

use App\Livewire\WorkSessions\FinishWorkSessionButton;
use App\Models\User;
use App\Models\WorkSession;
use Livewire\Livewire;

test('status is open when session exists without ended_at', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
    ]);

    Livewire::test(FinishWorkSessionButton::class)
        ->assertSet('status', 'open');
});

test('status is finished when session has ended_at set', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(8),
        'ended_at' => now()->subHour(),
        'duration_minutes' => 420,
    ]);

    Livewire::test(FinishWorkSessionButton::class)
        ->assertSet('status', 'finished');
});

test('status is null when no session exists today', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(FinishWorkSessionButton::class)
        ->assertSet('status', null);
});

test('finishSession closes the session and sets status to finished', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(4),
    ]);

    Livewire::test(FinishWorkSessionButton::class)
        ->assertSet('status', 'open')
        ->call('finishSession')
        ->assertSet('status', 'finished');

    $session = WorkSession::query()
        ->where('user_id', $user->id)
        ->whereDate('work_date', today())
        ->first();

    expect($session->ended_at)->not->toBeNull()
        ->and($session->duration_minutes)->toBeGreaterThan(0);
});

test('finishSession sets status to finished when called on already-finished session', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(8),
        'ended_at' => now()->subHour(),
        'duration_minutes' => 420,
    ]);

    Livewire::test(FinishWorkSessionButton::class)
        ->assertSet('status', 'finished')
        ->call('finishSession')
        ->assertSet('status', 'finished');

    $this->assertDatabaseHas('work_sessions', [
        'user_id' => $user->id,
        'duration_minutes' => 420,
    ]);
});
