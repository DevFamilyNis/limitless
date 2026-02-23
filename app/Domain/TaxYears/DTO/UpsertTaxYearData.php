<?php

declare(strict_types=1);

namespace App\Domain\TaxYears\DTO;

final class UpsertTaxYearData
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $taxYearId,
        public readonly int $year,
        public readonly float $firstThresholdAmount,
        public readonly float $secondThresholdAmount,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            taxYearId: isset($data['tax_year_id']) ? (int) $data['tax_year_id'] : null,
            year: (int) $data['year'],
            firstThresholdAmount: (float) $data['first_threshold_amount'],
            secondThresholdAmount: (float) $data['second_threshold_amount'],
        );
    }
}
