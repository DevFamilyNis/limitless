<?php

declare(strict_types=1);

namespace App\Domain\Issues\Actions;

use App\Domain\Issues\DTO\DeleteIssueAttachmentData;
use App\Models\Issue;

final class DeleteIssueAttachmentAction
{
    public function execute(DeleteIssueAttachmentData $dto): void
    {
        $issue = Issue::query()
            ->findOrFail($dto->issueId);

        $media = $issue->media()->findOrFail($dto->mediaId);
        $media->delete();
    }
}
