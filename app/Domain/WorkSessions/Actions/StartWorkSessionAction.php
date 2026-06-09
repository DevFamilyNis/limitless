<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\Actions;

use App\Domain\WorkSessions\DTO\StartWorkSessionData;
use App\Domain\WorkSessions\Exceptions\WorkSessionAlreadyStartedException;
use App\Enums\AppSettingKey;
use App\Models\AppSetting;
use App\Models\WorkSession;

final class StartWorkSessionAction
{
    public function execute(StartWorkSessionData $dto): WorkSession
    {
        $exists = WorkSession::query()
            ->where('user_id', $dto->userId)
            ->whereDate('work_date', $dto->workDate)
            ->exists();

        if ($exists) {
            throw new WorkSessionAlreadyStartedException;
        }

        $startedAt = now();
        $delayMinutes = (int) AppSetting::getValue(AppSettingKey::WorkSessionReminderDelayMinutes, 120);

        return WorkSession::create([
            'user_id' => $dto->userId,
            'work_date' => $dto->workDate->toDateString(),
            'started_at' => $startedAt,
            'reminder_due_at' => $startedAt->copy()->addMinutes($delayMinutes),
        ]);
    }
}
