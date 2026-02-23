<?php

declare(strict_types=1);

namespace App\Domain\Settings\Issues\DTO;

final class DeleteIssueCategoryData
{
    public function __construct(public readonly int $categoryId) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['category_id']);
    }
}
