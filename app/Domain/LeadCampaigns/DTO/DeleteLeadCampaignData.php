<?php

declare(strict_types=1);

namespace App\Domain\LeadCampaigns\DTO;

final class DeleteLeadCampaignData
{
    public function __construct(
        public readonly int $campaignId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            campaignId: (int) $data['campaign_id'],
        );
    }
}
