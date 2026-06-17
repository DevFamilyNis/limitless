<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\Actions;

use App\Domain\WorkSessions\DTO\ResumeWorkSessionData;
use App\Domain\WorkSessions\Exceptions\WorkSessionNotStartedException;
use App\Models\WorkSession;

final class ResumeWorkSessionAction
{
    public function execute(ResumeWorkSessionData $dto): WorkSession
    {
        $session = WorkSession::query()
            ->where('user_id', $dto->userId)
            ->whereDate('work_date', today())
            ->first();

        if ($session === null) {
            throw new WorkSessionNotStartedException;
        }

        if (! $session->isPaused()) {
            return $session;
        }

        $session->paused_at = null;
        $session->save();

        return $session;
    }
}
