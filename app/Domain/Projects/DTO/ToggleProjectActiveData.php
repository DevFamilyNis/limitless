<?php

declare(strict_types=1);

namespace App\Domain\Projects\DTO;

final class ToggleProjectActiveData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $projectId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['user_id'], (int) $data['project_id']);
    }
}
