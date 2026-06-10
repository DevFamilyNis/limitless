<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\Actions;

use App\Domain\WorkSessions\DTO\UpsertWorkSessionUserSettingData;
use App\Models\WorkSessionUserSetting;

final class UpsertWorkSessionUserSettingAction
{
    public function execute(UpsertWorkSessionUserSettingData $dto): WorkSessionUserSetting
    {
        return WorkSessionUserSetting::query()->updateOrCreate(
            ['user_id' => $dto->userId],
            [
                'reminder_enabled' => $dto->reminderEnabled,
                'reminder_delay_minutes' => $dto->reminderEnabled ? $dto->reminderDelayMinutes : null,
            ],
        );
    }
}
