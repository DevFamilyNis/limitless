<?php

declare(strict_types=1);

namespace App\Domain\Settings\Issues\DTO;

final class DeleteIssueStatusData
{
    public function __construct(public readonly int $statusId) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['status_id']);
    }
}
