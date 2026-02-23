<?php

declare(strict_types=1);

namespace App\Domain\ClientProjectRates\Actions;

use App\Domain\ClientProjectRates\DTO\UpsertClientProjectRateData;
use App\Models\Client;
use App\Models\ClientProjectRate;
use App\Models\Project;

final class UpsertClientProjectRateAction
{
    public function execute(UpsertClientProjectRateData $dto): ClientProjectRate
    {
        $client = Client::query()
            ->where('user_id', $dto->userId)
            ->findOrFail($dto->clientId);

        Project::query()
            ->where('user_id', $dto->userId)
            ->findOrFail($dto->projectId);

        $rate = $dto->rateId
            ? ClientProjectRate::query()
                ->whereHas('client', fn ($query) => $query->where('user_id', $dto->userId))
                ->findOrFail($dto->rateId)
            : new ClientProjectRate;

        $rate->fill([
            'client_id' => $client->id,
            'project_id' => $dto->projectId,
            'billing_period_id' => $dto->billingPeriodId,
            'price_amount' => $dto->priceAmount,
            'currency' => $dto->currency,
            'is_active' => $rate->exists ? $rate->is_active : true,
        ]);

        $rate->save();

        return $rate;
    }
}
