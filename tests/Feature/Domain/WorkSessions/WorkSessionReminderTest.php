<?php

use App\Domain\WorkSessions\Actions\AcknowledgeWorkSessionReminderAction;
use App\Domain\WorkSessions\Actions\StartWorkSessionAction;
use App\Domain\WorkSessions\DTO\AcknowledgeWorkSessionReminderData;
use App\Domain\WorkSessions\DTO\StartWorkSessionData;
use App\Domain\WorkSessions\Exceptions\WorkSessionNotStartedException;
use App\Enums\AppSettingKey;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\WorkSession;

test('StartWorkSessionAction sets reminder_due_at based on default delay', function () {
    $user = User::factory()->create();

    $session = app(StartWorkSessionAction::class)->execute(
        StartWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect($session->reminder_due_at)->not->toBeNull()
        ->and((int) $session->started_at->diffInMinutes($session->reminder_due_at))->toBe(120);
});

test('StartWorkSessionAction sets reminder_due_at using configured delay', function () {
    AppSetting::setValue(AppSettingKey::WorkSessionReminderDelayMinutes, 60);

    $user = User::factory()->create();

    $session = app(StartWorkSessionAction::class)->execute(
        StartWorkSessionData::fromArray(['user_id' => $user->id])
    );

    expect((int) $session->started_at->diffInMinutes($session->reminder_due_at))->toBe(60);
});

test('AcknowledgeWorkSessionReminderAction sets reminder_acknowledged_at', function () {
    $user = User::factory()->create();

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(3),
        'reminder_due_at' => now()->subHour(),
    ]);

    $result = app(AcknowledgeWorkSessionReminderAction::class)->execute(
        AcknowledgeWorkSessionReminderData::fromArray(['user_id' => $user->id])
    );

    expect($result->reminder_acknowledged_at)->not->toBeNull();
});

test('AcknowledgeWorkSessionReminderAction throws when no session today', function () {
    $user = User::factory()->create();

    expect(fn () => app(AcknowledgeWorkSessionReminderAction::class)->execute(
        AcknowledgeWorkSessionReminderData::fromArray(['user_id' => $user->id])
    ))->toThrow(WorkSessionNotStartedException::class);
});
