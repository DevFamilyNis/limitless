<?php

declare(strict_types=1);

namespace App\Domain\Categories\DTO;

final class DeleteCategoryData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $categoryId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            categoryId: (int) $data['category_id'],
        );
    }
}
