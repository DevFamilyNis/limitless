<?php

declare(strict_types=1);

namespace App\Domain\LeadCampaigns\Actions;

use App\Domain\LeadCampaigns\DTO\UpsertLeadCampaignData;
use App\Models\LeadCampaign;

final class UpsertLeadCampaignAction
{
    public function execute(UpsertLeadCampaignData $dto): LeadCampaign
    {
        $campaign = $dto->campaignId
            ? LeadCampaign::query()->findOrFail($dto->campaignId)
            : new LeadCampaign;

        $campaign->fill([
            'name' => $dto->name,
            'description' => $dto->description,
        ]);

        $campaign->save();

        return $campaign;
    }
}
