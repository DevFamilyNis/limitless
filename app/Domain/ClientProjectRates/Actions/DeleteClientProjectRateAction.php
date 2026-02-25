<?php

declare(strict_types=1);

namespace App\Domain\ClientProjectRates\Actions;

use App\Domain\ClientProjectRates\DTO\DeleteClientProjectRateData;
use App\Models\ClientProjectRate;

final class DeleteClientProjectRateAction
{
    public function execute(DeleteClientProjectRateData $dto): void
    {
        $rate = ClientProjectRate::query()
            ->findOrFail($dto->rateId);

        $rate->delete();
    }
}
