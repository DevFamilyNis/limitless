<?php

declare(strict_types=1);

namespace App\Domain\TaxYears\Actions;

use App\Domain\TaxYears\DTO\DeleteTaxYearData;
use App\Models\TaxYear;

final class DeleteTaxYearAction
{
    public function execute(DeleteTaxYearData $dto): void
    {
        $taxYear = TaxYear::query()
            ->findOrFail($dto->taxYearId);

        $taxYear->delete();
    }
}
