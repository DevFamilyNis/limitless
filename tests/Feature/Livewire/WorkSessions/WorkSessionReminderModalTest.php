<?php

use App\Livewire\WorkSessions\WorkSessionReminderModal;
use App\Models\User;
use App\Models\WorkSession;
use Livewire\Livewire;

test('show is true when reminder is due and not acknowledged', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(3),
        'reminder_due_at' => now()->subHour(),
    ]);

    Livewire::test(WorkSessionReminderModal::class)
        ->assertSet('show', true);
});

test('show is false when no session today', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(WorkSessionReminderModal::class)
        ->assertSet('show', false);
});

test('show is false when reminder is not yet due', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subMinutes(30),
        'reminder_due_at' => now()->addHours(2),
    ]);

    Livewire::test(WorkSessionReminderModal::class)
        ->assertSet('show', false);
});

test('show is false when reminder is already acknowledged', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(3),
        'reminder_due_at' => now()->subHour(),
        'reminder_acknowledged_at' => now()->subMinutes(30),
    ]);

    Livewire::test(WorkSessionReminderModal::class)
        ->assertSet('show', false);
});

test('show is false when session is already finished', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(3),
        'ended_at' => now()->subMinutes(30),
        'duration_minutes' => 150,
        'reminder_due_at' => now()->subHour(),
    ]);

    Livewire::test(WorkSessionReminderModal::class)
        ->assertSet('show', false);
});

test('acknowledge sets reminder_acknowledged_at and hides modal', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $session = WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(3),
        'reminder_due_at' => now()->subHour(),
    ]);

    Livewire::test(WorkSessionReminderModal::class)
        ->assertSet('show', true)
        ->call('acknowledge')
        ->assertSet('show', false);

    expect($session->fresh()->reminder_acknowledged_at)->not->toBeNull();
});
