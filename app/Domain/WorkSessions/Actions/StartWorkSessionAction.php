<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\Actions;

use App\Domain\WorkSessions\DTO\StartWorkSessionData;
use App\Domain\WorkSessions\Exceptions\WorkSessionAlreadyStartedException;
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

        return WorkSession::create([
            'user_id' => $dto->userId,
            'work_date' => $dto->workDate->toDateString(),
            'started_at' => now(),
        ]);
    }
}
