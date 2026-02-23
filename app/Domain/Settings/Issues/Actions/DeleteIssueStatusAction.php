<?php

declare(strict_types=1);

namespace App\Domain\Settings\Issues\Actions;

use App\Domain\Settings\Issues\DTO\DeleteIssueStatusData;
use App\Models\IssueStatus;

final class DeleteIssueStatusAction
{
    public function execute(DeleteIssueStatusData $dto): void
    {
        IssueStatus::query()->findOrFail($dto->statusId)->delete();
    }
}
