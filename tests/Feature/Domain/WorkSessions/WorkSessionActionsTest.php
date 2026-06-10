<?php

use App\Domain\WorkSessions\Actions\FinishWorkSessionAction;
use App\Domain\WorkSessions\Actions\StartWorkSessionAction;
use App\Domain\WorkSessions\Actions\UpsertWorkSessionUserSettingAction;
use App\Domain\WorkSessions\DTO\FinishWorkSessionData;
use App\Domain\WorkSessions\DTO\StartWorkSessionData;
use App\Domain\WorkSessions\DTO\UpsertWorkSessionUserSettingData;
use App\Domain\WorkSessions\Exceptions\WorkSessionAlreadyStartedException;
use App\Domain\WorkSessions\Exceptions\WorkSessionNotStartedException;
use App\Enums\AppSettingKey;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\WorkSession;
use App\Models\WorkSessionUserSetting;

test('StartWorkSessionAction creates a work session for today', function () {
    $user = User::factory()->create();

    $session = app(StartWorkSessionAction::class)->execute(
        StartWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect($session)->toBeInstanceOf(WorkSession::class)
        ->and($session->user_id)->toBe($user->id)
        ->and($session->work_date->toDateString())->toBe(today()->toDateString())
        ->and($session->started_at)->not->toBeNull()
        ->and($session->ended_at)->toBeNull()
        ->and($session->reminder_due_at)->not->toBeNull();

    expect(
        WorkSession::query()->where('user_id', $user->id)->whereDate('work_date', today())->exists()
    )->toBeTrue();
});

test('StartWorkSessionAction does not set reminder_due_at when reminder is disabled', function () {
    AppSetting::setValue(AppSettingKey::WorkSessionReminderEnabled, false);

    $user = User::factory()->create();

    $session = app(StartWorkSessionAction::class)->execute(
        StartWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect($session->reminder_due_at)->toBeNull();
});

test('StartWorkSessionAction uses per-user reminder override when global reminder is enabled', function () {
    AppSetting::setValue(AppSettingKey::WorkSessionReminderEnabled, true);
    AppSetting::setValue(AppSettingKey::WorkSessionReminderDelayMinutes, 120);

    $user = User::factory()->create();
    WorkSessionUserSetting::create([
        'user_id' => $user->id,
        'reminder_enabled' => true,
        'reminder_delay_minutes' => 480,
    ]);

    $session = app(StartWorkSessionAction::class)->execute(
        StartWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect($session->started_at->diffInMinutes($session->reminder_due_at))->toBe(480.0);
});

test('StartWorkSessionAction does not set reminder_due_at when per-user override disables it', function () {
    AppSetting::setValue(AppSettingKey::WorkSessionReminderEnabled, true);

    $user = User::factory()->create();
    WorkSessionUserSetting::create([
        'user_id' => $user->id,
        'reminder_enabled' => false,
        'reminder_delay_minutes' => null,
    ]);

    $session = app(StartWorkSessionAction::class)->execute(
        StartWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect($session->reminder_due_at)->toBeNull();
});

test('StartWorkSessionAction throws WorkSessionAlreadyStartedException if session exists today', function () {
    $user = User::factory()->create();

    app(StartWorkSessionAction::class)->execute(
        StartWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect(fn () => app(StartWorkSessionAction::class)->execute(
        StartWorkSessionData::fromArray(['user_id' => $user->id])
    ))->toThrow(WorkSessionAlreadyStartedException::class);
});

test('FinishWorkSessionAction closes session and computes duration_minutes', function () {
    $user = User::factory()->create();

    $session = WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subMinutes(90),
    ]);

    $finished = app(FinishWorkSessionAction::class)->execute(
        FinishWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect($finished->ended_at)->not->toBeNull()
        ->and($finished->duration_minutes)->toBeGreaterThanOrEqual(90);

    $this->assertDatabaseHas('work_sessions', [
        'id' => $session->id,
        'duration_minutes' => $finished->duration_minutes,
    ]);
});

test('FinishWorkSessionAction throws WorkSessionNotStartedException when no session today', function () {
    $user = User::factory()->create();

    expect(fn () => app(FinishWorkSessionAction::class)->execute(
        FinishWorkSessionData::fromArray(['user_id' => $user->id])
    ))->toThrow(WorkSessionNotStartedException::class);
});

test('FinishWorkSessionAction is idempotent when session is already finished', function () {
    $user = User::factory()->create();
    $endedAt = now()->subMinutes(10);

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subMinutes(100),
        'ended_at' => $endedAt,
        'duration_minutes' => 90,
    ]);

    $result = app(FinishWorkSessionAction::class)->execute(
        FinishWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect($result->duration_minutes)->toBe(90)
        ->and($result->ended_at->toDateTimeString())->toBe($endedAt->toDateTimeString());
});

test('UpsertWorkSessionUserSettingAction creates an override for a user', function () {
    $user = User::factory()->create();

    app(UpsertWorkSessionUserSettingAction::class)->execute(
        UpsertWorkSessionUserSettingData::fromArray([
            'user_id' => $user->id,
            'reminder_enabled' => true,
            'reminder_delay_minutes' => 30,
        ])
    );

    $this->assertDatabaseHas('work_session_user_settings', [
        'user_id' => $user->id,
        'reminder_enabled' => true,
        'reminder_delay_minutes' => 30,
    ]);
});

test('UpsertWorkSessionUserSettingAction updates an existing override', function () {
    $user = User::factory()->create();
    WorkSessionUserSetting::create([
        'user_id' => $user->id,
        'reminder_enabled' => true,
        'reminder_delay_minutes' => 30,
    ]);

    app(UpsertWorkSessionUserSettingAction::class)->execute(
        UpsertWorkSessionUserSettingData::fromArray([
            'user_id' => $user->id,
            'reminder_enabled' => true,
            'reminder_delay_minutes' => 480,
        ])
    );

    $this->assertDatabaseHas('work_session_user_settings', [
        'user_id' => $user->id,
        'reminder_enabled' => true,
        'reminder_delay_minutes' => 480,
    ]);
    $this->assertDatabaseCount('work_session_user_settings', 1);
});

test('UpsertWorkSessionUserSettingAction clears delay when reminder is disabled', function () {
    $user = User::factory()->create();

    app(UpsertWorkSessionUserSettingAction::class)->execute(
        UpsertWorkSessionUserSettingData::fromArray([
            'user_id' => $user->id,
            'reminder_enabled' => false,
            'reminder_delay_minutes' => 30,
        ])
    );

    $this->assertDatabaseHas('work_session_user_settings', [
        'user_id' => $user->id,
        'reminder_enabled' => false,
        'reminder_delay_minutes' => null,
    ]);
});
