<?php

declare(strict_types=1);

namespace App\Domain\Issues\DTO;

final class UploadIssueAttachmentsData
{
    /**
     * @param  array<int, mixed>  $files
     */
    public function __construct(
        public readonly int $userId,
        public readonly int $issueId,
        public readonly array $files,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            issueId: (int) $data['issue_id'],
            files: $data['files'] ?? [],
        );
    }
}
