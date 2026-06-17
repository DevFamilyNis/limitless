<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\Actions;

use App\Domain\WorkSessions\DTO\PauseWorkSessionData;
use App\Domain\WorkSessions\Exceptions\WorkSessionNotStartedException;
use App\Models\WorkSession;

final class PauseWorkSessionAction
{
    public function execute(PauseWorkSessionData $dto): WorkSession
    {
        $session = WorkSession::query()
            ->where('user_id', $dto->userId)
            ->whereDate('work_date', today())
            ->first();

        if ($session === null) {
            throw new WorkSessionNotStartedException;
        }

        if ($session->isFinished() || $session->isPaused()) {
            return $session;
        }

        $session->paused_at = now();
        $session->save();

        return $session;
    }
}
