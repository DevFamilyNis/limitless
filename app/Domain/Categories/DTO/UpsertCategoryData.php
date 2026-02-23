<?php

declare(strict_types=1);

namespace App\Domain\Categories\DTO;

final class UpsertCategoryData
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $categoryId,
        public readonly int $categoryTypeId,
        public readonly string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            categoryTypeId: (int) $data['category_type_id'],
            name: trim((string) $data['name']),
        );
    }
}
