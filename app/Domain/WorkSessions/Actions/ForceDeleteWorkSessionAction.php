<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\Actions;

use App\Domain\WorkSessions\DTO\ForceDeleteWorkSessionData;
use App\Models\WorkSession;

final class ForceDeleteWorkSessionAction
{
    public function execute(ForceDeleteWorkSessionData $dto): void
    {
        WorkSession::query()->find($dto->workSessionId)?->delete();
    }
}
