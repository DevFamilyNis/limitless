<?php

declare(strict_types=1);

namespace App\Domain\Leads\Actions;

use App\Domain\Leads\DTO\UpsertLeadData;
use App\Models\Lead;
use App\Models\LeadStatus;

final class UpsertLeadAction
{
    public function execute(UpsertLeadData $dto): Lead
    {
        $lead = $dto->leadId
            ? Lead::query()->findOrFail($dto->leadId)
            : new Lead;

        $statusKey = LeadStatus::query()->whereKey($dto->leadStatusId)->value('key');

        $lead->fill([
            'lead_campaign_id' => $dto->leadCampaignId,
            'lead_status_id' => $dto->leadStatusId,
            'company_name' => $dto->companyName,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'converted_at' => $statusKey === 'converted' ? ($lead->converted_at ?? now()) : null,
        ]);

        $lead->save();

        return $lead;
    }
}
