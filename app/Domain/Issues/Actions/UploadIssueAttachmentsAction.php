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
            ->findOrFail($dto->issueId);

        foreach ($dto->files as $file) {
            $issue->addMedia($file->getRealPath())
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('attachments');
        }
    }
}
