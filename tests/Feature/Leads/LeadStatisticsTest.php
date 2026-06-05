<?php

use App\Domain\Leads\Queries\LeadStatisticsQuery;
use App\Models\Lead;
use App\Models\LeadCampaign;
use App\Models\LeadStatus;

test('lead statistics query returns totals and conversion rate scoped to campaign', function () {
    $campaign = LeadCampaign::factory()->create();
    $otherCampaign = LeadCampaign::factory()->create();

    $newStatusId = LeadStatus::query()->where('key', 'new')->value('id');
    $respondedStatusId = LeadStatus::query()->where('key', 'responded')->value('id');
    $convertedStatusId = LeadStatus::query()->where('key', 'converted')->value('id');

    Lead::factory()->create([
        'lead_campaign_id' => $campaign->id,
        'lead_status_id' => $newStatusId,
        'company_name' => 'Alpha',
    ]);

    Lead::factory()->create([
        'lead_campaign_id' => $campaign->id,
        'lead_status_id' => $respondedStatusId,
        'company_name' => 'Beta',
        'last_response_at' => now(),
    ]);

    Lead::factory()->create([
        'lead_campaign_id' => $campaign->id,
        'lead_status_id' => $convertedStatusId,
        'company_name' => 'Gamma',
        'converted_at' => now(),
    ]);

    Lead::factory()->create([
        'lead_campaign_id' => $campaign->id,
        'lead_status_id' => $respondedStatusId,
        'company_name' => 'Delta',
        'last_response_at' => now(),
    ]);

    // lead in another campaign — must not affect stats
    Lead::factory()->create([
        'lead_campaign_id' => $otherCampaign->id,
        'lead_status_id' => $convertedStatusId,
        'company_name' => 'Other Campaign Lead',
        'converted_at' => now(),
    ]);

    $statistics = app(LeadStatisticsQuery::class)->get($campaign->id);

    expect($statistics['total'])->toBe(4);
    expect($statistics['converted'])->toBe(1);
    expect($statistics['responded'])->toBe(2);
    expect($statistics['conversion_rate'])->toBe(25.0);
    expect($statistics['by_status']['responded'])->toBe(2);
    expect($statistics['by_status']['converted'])->toBe(1);
});
