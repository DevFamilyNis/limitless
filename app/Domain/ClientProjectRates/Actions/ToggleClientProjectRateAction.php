<?php

declare(strict_types=1);

namespace App\Domain\ClientProjectRates\Actions;

use App\Domain\ClientProjectRates\DTO\ToggleClientProjectRateData;
use App\Models\ClientProjectRate;

final class ToggleClientProjectRateAction
{
    public function execute(ToggleClientProjectRateData $dto): ClientProjectRate
    {
        $rate = ClientProjectRate::query()
            ->whereHas('client', fn ($query) => $query->where('user_id', $dto->userId))
            ->findOrFail($dto->rateId);

        $rate->update([
            'is_active' => ! $rate->is_active,
        ]);

        return $rate;
    }
}
