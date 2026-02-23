<?php

declare(strict_types=1);

namespace App\Domain\Settings\Issues\DTO;

final class UpsertIssuePriorityData
{
    public function __construct(
        public readonly ?int $priorityId,
        public readonly string $key,
        public readonly string $name,
        public readonly int $sortOrder,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            priorityId: isset($data['priority_id']) ? (int) $data['priority_id'] : null,
            key: strtolower(trim((string) $data['key'])),
            name: trim((string) $data['name']),
            sortOrder: (int) $data['sort_order'],
        );
    }
}
