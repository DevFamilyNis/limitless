<?php

declare(strict_types=1);

namespace App\Domain\LeadCampaigns\Actions;

use App\Domain\LeadCampaigns\DTO\DeleteLeadCampaignData;
use App\Domain\LeadCampaigns\Exceptions\LeadCampaignHasLeadsException;
use App\Models\LeadCampaign;

final class DeleteLeadCampaignAction
{
    public function execute(DeleteLeadCampaignData $dto): void
    {
        $campaign = LeadCampaign::query()->findOrFail($dto->campaignId);

        if ($campaign->leads()->exists()) {
            throw new LeadCampaignHasLeadsException;
        }

        $campaign->delete();
    }
}
