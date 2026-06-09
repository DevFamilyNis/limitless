<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\Actions;

use App\Domain\WorkSessions\DTO\ForceFinishWorkSessionData;
use App\Models\WorkSession;

final class ForceFinishWorkSessionAction
{
    public function execute(ForceFinishWorkSessionData $dto): ?WorkSession
    {
        $session = WorkSession::query()->find($dto->workSessionId);

        if ($session === null || $session->isFinished()) {
            return $session;
        }

        $now = now();
        $session->ended_at = $now;
        $session->duration_minutes = (int) $session->started_at->diffInMinutes($now);
        $session->save();

        return $session;
    }
}
