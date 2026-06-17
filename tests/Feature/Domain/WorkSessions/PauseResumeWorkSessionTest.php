<?php

use App\Domain\WorkSessions\Actions\FinishWorkSessionAction;
use App\Domain\WorkSessions\Actions\PauseWorkSessionAction;
use App\Domain\WorkSessions\Actions\ResumeWorkSessionAction;
use App\Domain\WorkSessions\DTO\FinishWorkSessionData;
use App\Domain\WorkSessions\DTO\PauseWorkSessionData;
use App\Domain\WorkSessions\DTO\ResumeWorkSessionData;
use App\Domain\WorkSessions\Exceptions\WorkSessionNotStartedException;
use App\Models\User;
use App\Models\WorkSession;

test('PauseWorkSessionAction sets paused_at', function () {
    $user = User::factory()->create();

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
    ]);

    $session = app(PauseWorkSessionAction::class)->execute(
        PauseWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect($session->paused_at)->not->toBeNull()
        ->and($session->ended_at)->toBeNull();
});

test('PauseWorkSessionAction is idempotent when already paused', function () {
    $user = User::factory()->create();
    $pausedAt = now()->subMinutes(10);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
        'paused_at' => $pausedAt,
    ]);

    $session = app(PauseWorkSessionAction::class)->execute(
        PauseWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect($session->paused_at->toDateTimeString())->toBe($pausedAt->toDateTimeString());
});

test('PauseWorkSessionAction throws when no session today', function () {
    $user = User::factory()->create();

    expect(fn () => app(PauseWorkSessionAction::class)->execute(
        PauseWorkSessionData::fromArray(['user_id' => $user->id])
    ))->toThrow(WorkSessionNotStartedException::class);
});

test('ResumeWorkSessionAction clears paused_at', function () {
    $user = User::factory()->create();

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
        'paused_at' => now()->subMinutes(30),
    ]);

    $session = app(ResumeWorkSessionAction::class)->execute(
        ResumeWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect($session->paused_at)->toBeNull()
        ->and($session->ended_at)->toBeNull();
});

test('ResumeWorkSessionAction is idempotent when not paused', function () {
    $user = User::factory()->create();

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
    ]);

    $session = app(ResumeWorkSessionAction::class)->execute(
        ResumeWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect($session->paused_at)->toBeNull();
});

test('FinishWorkSessionAction uses paused_at as end time when session is paused', function () {
    $user = User::factory()->create();
    $pausedAt = now()->subHour();

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(3),
        'paused_at' => $pausedAt,
    ]);

    $session = app(FinishWorkSessionAction::class)->execute(
        FinishWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect($session->ended_at->toDateTimeString())->toBe($pausedAt->toDateTimeString())
        ->and($session->paused_at)->toBeNull()
        ->and($session->duration_minutes)->toBeGreaterThanOrEqual(120);
});
