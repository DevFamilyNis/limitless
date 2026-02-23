<?php

declare(strict_types=1);

namespace App\Domain\Settings\Issues\Actions;

use App\Domain\Settings\Issues\DTO\UpsertIssuePriorityData;
use App\Models\IssuePriority;

final class UpsertIssuePriorityAction
{
    public function execute(UpsertIssuePriorityData $dto): IssuePriority
    {
        $priority = $dto->priorityId ? IssuePriority::query()->findOrFail($dto->priorityId) : new IssuePriority;

        $priority->fill([
            'key' => $dto->key,
            'name' => $dto->name,
            'sort_order' => $dto->sortOrder,
        ]);

        $priority->save();

        return $priority;
    }
}
