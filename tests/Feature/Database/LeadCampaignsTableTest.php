<?php

use App\Models\Lead;
use App\Models\LeadCampaign;
use Illuminate\Database\QueryException;

test('lead_campaign_id is required on leads table', function () {
    expect(fn () => Lead::query()->create([
        'lead_status_id' => \App\Models\LeadStatus::query()->where('key', 'new')->value('id'),
        'company_name' => 'No Campaign DOO',
    ]))->toThrow(QueryException::class);
});

test('campaign cannot be deleted when it has leads — FK restriction', function () {
    $campaign = LeadCampaign::factory()->create();
    Lead::factory()->create(['lead_campaign_id' => $campaign->id]);

    expect(fn () => $campaign->delete())->toThrow(QueryException::class);

    $this->assertDatabaseHas('lead_campaigns', ['id' => $campaign->id]);
});

test('lead belongs to campaign via relationship', function () {
    $campaign = LeadCampaign::factory()->create(['name' => 'Test Campaign']);
    $lead = Lead::factory()->create(['lead_campaign_id' => $campaign->id]);

    expect($lead->campaign)->toBeInstanceOf(LeadCampaign::class);
    expect($lead->campaign->name)->toBe('Test Campaign');
});

test('campaign has many leads via relationship', function () {
    $campaign = LeadCampaign::factory()->create();
    Lead::factory()->count(3)->create(['lead_campaign_id' => $campaign->id]);

    expect($campaign->leads()->count())->toBe(3);
});
