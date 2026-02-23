<?php

declare(strict_types=1);

namespace App\Domain\Settings\Issues\Actions;

use App\Domain\Settings\Issues\DTO\DeleteIssuePriorityData;
use App\Models\IssuePriority;

final class DeleteIssuePriorityAction
{
    public function execute(DeleteIssuePriorityData $dto): void
    {
        IssuePriority::query()->findOrFail($dto->priorityId)->delete();
    }
}
