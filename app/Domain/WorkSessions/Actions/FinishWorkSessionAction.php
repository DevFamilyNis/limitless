<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\Actions;

use App\Domain\WorkSessions\DTO\FinishWorkSessionData;
use App\Domain\WorkSessions\Exceptions\WorkSessionNotStartedException;
use App\Models\WorkSession;

final class FinishWorkSessionAction
{
    public function execute(FinishWorkSessionData $dto): WorkSession
    {
        $session = WorkSession::query()
            ->where('user_id', $dto->userId)
            ->whereDate('work_date', today())
            ->first();

        if ($session === null) {
            throw new WorkSessionNotStartedException;
        }

        if ($session->isFinished()) {
            return $session;
        }

        $now = now();
        $session->ended_at = $now;
        $session->duration_minutes = (int) $session->started_at->diffInMinutes($now);
        $session->save();

        return $session;
    }
}
