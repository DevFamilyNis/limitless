<?php

declare(strict_types=1);

namespace App\Domain\TaxYears\Actions;

use App\Domain\TaxYears\DTO\EnsureCurrentTaxYearData;
use App\Models\TaxYear;

final class EnsureCurrentTaxYearAction
{
    public function execute(EnsureCurrentTaxYearData $dto): TaxYear
    {
        return TaxYear::query()->firstOrCreate(
            [
                'year' => now()->year,
            ],
            [
                'user_id' => $dto->userId,
                'first_threshold_amount' => 6000000,
                'second_threshold_amount' => 8000000,
            ]
        );
    }
}
