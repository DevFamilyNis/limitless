<?php

declare(strict_types=1);

namespace App\Domain\TaxYears\Actions;

use App\Domain\TaxYears\DTO\UpsertTaxYearData;
use App\Models\TaxYear;

final class UpsertTaxYearAction
{
    public function execute(UpsertTaxYearData $dto): TaxYear
    {
        $taxYear = $dto->taxYearId
            ? TaxYear::query()->findOrFail($dto->taxYearId)
            : new TaxYear;

        $taxYear->fill([
            'user_id' => $dto->userId,
            'year' => $dto->year,
            'first_threshold_amount' => $dto->firstThresholdAmount,
            'second_threshold_amount' => $dto->secondThresholdAmount,
        ]);

        $taxYear->save();

        return $taxYear;
    }
}
