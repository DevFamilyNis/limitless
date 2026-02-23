<?php

declare(strict_types=1);

namespace App\Domain\Issues\Actions;

use App\Domain\Issues\DTO\MoveIssueData;
use App\Models\Issue;
use App\Models\IssueStatus;

final class MoveIssueAction
{
    public function execute(MoveIssueData $dto): Issue
    {
        $issue = Issue::query()
            ->whereHas('project', fn ($query) => $query->where('user_id', $dto->userId))
            ->findOrFail($dto->issueId);

        $toStatus = IssueStatus::query()->findOrFail($dto->toStatusId);

        $issue->status_id = $toStatus->id;
        $issue->completed_at = $toStatus->key === 'done' ? now() : null;
        $issue->save();

        return $issue;
    }
}
