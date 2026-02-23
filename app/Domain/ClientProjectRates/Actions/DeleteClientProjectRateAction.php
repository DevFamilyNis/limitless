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
            ->whereHas('client', fn ($query) => $query->where('user_id', $dto->userId))
            ->findOrFail($dto->rateId);

        $rate->delete();
    }
}
