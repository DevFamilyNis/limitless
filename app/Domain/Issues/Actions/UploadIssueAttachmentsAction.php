<?php

declare(strict_types=1);

namespace App\Domain\Issues\Actions;

use App\Domain\Issues\DTO\UploadIssueAttachmentsData;
use App\Models\Issue;

final class UploadIssueAttachmentsAction
{
    public function execute(UploadIssueAttachmentsData $dto): void
    {
        $issue = Issue::query()
            ->whereHas('project', fn ($query) => $query->where('user_id', $dto->userId))
            ->findOrFail($dto->issueId);

        foreach ($dto->files as $file) {
            $issue->addMedia($file->getRealPath())
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('attachments');
        }
    }
}
