<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\Actions;

use App\Domain\WorkSessions\DTO\AcknowledgeWorkSessionReminderData;
use App\Domain\WorkSessions\Exceptions\WorkSessionNotStartedException;
use App\Models\WorkSession;

final class AcknowledgeWorkSessionReminderAction
{
    public function execute(AcknowledgeWorkSessionReminderData $dto): WorkSession
    {
        $session = WorkSession::query()
            ->where('user_id', $dto->userId)
            ->whereDate('work_date', today())
            ->first();

        if ($session === null) {
            throw new WorkSessionNotStartedException;
        }

        $session->reminder_acknowledged_at = now();
        $session->save();

        return $session;
    }
}
