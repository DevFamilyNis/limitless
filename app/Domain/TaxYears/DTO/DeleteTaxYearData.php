<?php

declare(strict_types=1);

namespace App\Domain\TaxYears\DTO;

final class DeleteTaxYearData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $taxYearId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['user_id'], (int) $data['tax_year_id']);
    }
}
