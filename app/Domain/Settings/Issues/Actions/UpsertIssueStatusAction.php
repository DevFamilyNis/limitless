<?php

declare(strict_types=1);

namespace App\Domain\Settings\Issues\Actions;

use App\Domain\Settings\Issues\DTO\UpsertIssueStatusData;
use App\Models\IssueStatus;

final class UpsertIssueStatusAction
{
    public function execute(UpsertIssueStatusData $dto): IssueStatus
    {
        $status = $dto->statusId ? IssueStatus::query()->findOrFail($dto->statusId) : new IssueStatus;

        $status->fill([
            'key' => $dto->key,
            'name' => $dto->name,
            'sort_order' => $dto->sortOrder,
            'is_active' => $dto->isActive,
        ]);

        $status->save();

        return $status;
    }
}
