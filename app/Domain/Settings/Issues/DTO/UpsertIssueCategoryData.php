<?php

declare(strict_types=1);

namespace App\Domain\Settings\Issues\DTO;

final class UpsertIssueCategoryData
{
    public function __construct(
        public readonly ?int $categoryId,
        public readonly string $name,
        public readonly bool $isActive,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            name: trim((string) $data['name']),
            isActive: (bool) $data['is_active'],
        );
    }
}
