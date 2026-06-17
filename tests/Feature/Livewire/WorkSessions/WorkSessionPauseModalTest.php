<?php

use App\Livewire\WorkSessions\WorkSessionPauseModal;
use App\Models\User;
use App\Models\WorkSession;
use Livewire\Livewire;

test('show is false when no paused session exists', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
    ]);

    Livewire::test(WorkSessionPauseModal::class)
        ->assertSet('show', false);
});

test('show is true when session is paused', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
        'paused_at' => now()->subMinutes(10),
    ]);

    Livewire::test(WorkSessionPauseModal::class)
        ->assertSet('show', true);
});

test('resume clears paused_at and closes modal', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
        'paused_at' => now()->subMinutes(10),
    ]);

    Livewire::test(WorkSessionPauseModal::class)
        ->assertSet('show', true)
        ->call('resume')
        ->assertSet('show', false)
        ->assertDispatched('work-session-resumed');

    $session = WorkSession::query()->where('user_id', $user->id)->whereDate('work_date', today())->first();
    expect($session->paused_at)->toBeNull();
});

test('show is false when no session exists today', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(WorkSessionPauseModal::class)
        ->assertSet('show', false);
});
