<?php

declare(strict_types=1);

namespace App\Domain\Issues\DTO;

final class AddIssueCommentData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $issueId,
        public readonly string $body,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['user_id'], (int) $data['issue_id'], trim((string) $data['body']));
    }
}
