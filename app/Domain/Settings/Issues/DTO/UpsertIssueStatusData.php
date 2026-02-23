<?php

declare(strict_types=1);

namespace App\Domain\Settings\Issues\DTO;

final class UpsertIssueStatusData
{
    public function __construct(
        public readonly ?int $statusId,
        public readonly string $key,
        public readonly string $name,
        public readonly int $sortOrder,
        public readonly bool $isActive,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            statusId: isset($data['status_id']) ? (int) $data['status_id'] : null,
            key: strtolower(trim((string) $data['key'])),
            name: trim((string) $data['name']),
            sortOrder: (int) $data['sort_order'],
            isActive: (bool) $data['is_active'],
        );
    }
}
