<?php

declare(strict_types=1);

namespace App\Domain\Issues\DTO;

final class DeleteIssueCommentData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $issueId,
        public readonly int $commentId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['user_id'], (int) $data['issue_id'], (int) $data['comment_id']);
    }
}
