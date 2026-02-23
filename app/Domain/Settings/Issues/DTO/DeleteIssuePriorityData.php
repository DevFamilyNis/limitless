<?php

declare(strict_types=1);

namespace App\Domain\Settings\Issues\DTO;

final class DeleteIssuePriorityData
{
    public function __construct(public readonly int $priorityId) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['priority_id']);
    }
}
