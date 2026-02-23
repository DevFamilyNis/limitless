<?php

declare(strict_types=1);

namespace App\Domain\Issues\Actions;

use App\Domain\Issues\DTO\DeleteIssueCommentData;
use App\Models\IssueComment;

final class DeleteIssueCommentAction
{
    public function execute(DeleteIssueCommentData $dto): void
    {
        $comment = IssueComment::query()
            ->where('issue_id', $dto->issueId)
            ->findOrFail($dto->commentId);

        if ($comment->author_id !== $dto->userId) {
            abort(403);
        }

        $comment->delete();
    }
}
