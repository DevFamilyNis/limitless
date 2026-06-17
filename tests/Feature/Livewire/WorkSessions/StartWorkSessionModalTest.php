<?php

use App\Livewire\WorkSessions\StartWorkSessionModal;
use App\Models\User;
use App\Models\WorkSession;
use Livewire\Livewire;

test('show is true with mode start when no session exists today', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(StartWorkSessionModal::class)
        ->assertSet('show', true)
        ->assertSet('mode', 'start');
});

test('show is true with mode resume when session is active on another device', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now(),
    ]);

    Livewire::test(StartWorkSessionModal::class)
        ->assertSet('show', true)
        ->assertSet('mode', 'resume');
});

test('show is false when session is finished', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(8),
        'ended_at' => now(),
        'duration_minutes' => 480,
    ]);

    Livewire::test(StartWorkSessionModal::class)
        ->assertSet('show', false);
});

test('startSession creates a work session and closes modal', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(StartWorkSessionModal::class)
        ->assertSet('show', true)
        ->call('startSession')
        ->assertSet('show', false);

    expect(
        WorkSession::query()->where('user_id', $user->id)->whereDate('work_date', today())->exists()
    )->toBeTrue();
});

test('continueSession closes modal and sets session flag', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now(),
    ]);

    Livewire::test(StartWorkSessionModal::class)
        ->assertSet('mode', 'resume')
        ->call('continueSession')
        ->assertSet('show', false);

    expect(session('work_session_resumed_'.today()->toDateString()))->toBeTrue();
});

test('show stays false on remount after continueSession acknowledged', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now(),
    ]);

    session(['work_session_resumed_'.today()->toDateString() => true]);

    Livewire::test(StartWorkSessionModal::class)
        ->assertSet('show', false);
});

test('finishSession ends the active session and closes modal', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(4),
    ]);

    Livewire::test(StartWorkSessionModal::class)
        ->assertSet('mode', 'resume')
        ->call('finishSession')
        ->assertSet('show', false);

    $session = WorkSession::query()->where('user_id', $user->id)->whereDate('work_date', today())->first();
    expect($session->ended_at)->not->toBeNull();
    expect($session->duration_minutes)->toBeGreaterThan(0);
});
