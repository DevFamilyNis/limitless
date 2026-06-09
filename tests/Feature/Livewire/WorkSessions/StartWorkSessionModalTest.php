<?php

use App\Livewire\WorkSessions\StartWorkSessionModal;
use App\Models\User;
use App\Models\WorkSession;
use Livewire\Livewire;

test('show is true when no session exists today', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(StartWorkSessionModal::class)
        ->assertSet('show', true);
});

test('show is false when session already exists today', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now(),
    ]);

    Livewire::test(StartWorkSessionModal::class)
        ->assertSet('show', false);
});

test('startSession creates a work session and sets show to false', function () {
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

test('show is false when session exists with ended_at set', function () {
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
