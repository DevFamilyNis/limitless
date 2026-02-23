<?php

declare(strict_types=1);

namespace App\Domain\TaxYears\DTO;

final class EnsureCurrentTaxYearData
{
    public function __construct(public readonly int $userId) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['user_id']);
    }
}
