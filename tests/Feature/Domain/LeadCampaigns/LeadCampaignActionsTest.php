<?php

declare(strict_types=1);

use App\Domain\LeadCampaigns\Actions\DeleteLeadCampaignAction;
use App\Domain\LeadCampaigns\Actions\UpsertLeadCampaignAction;
use App\Domain\LeadCampaigns\DTO\DeleteLeadCampaignData;
use App\Domain\LeadCampaigns\DTO\UpsertLeadCampaignData;
use App\Domain\LeadCampaigns\Exceptions\LeadCampaignHasLeadsException;
use App\Models\Lead;
use App\Models\LeadCampaign;

test('upsert action creates a new campaign', function () {
    $campaign = app(UpsertLeadCampaignAction::class)->execute(
        UpsertLeadCampaignData::fromArray([
            'name' => 'EmPay 3.0',
            'description' => 'Treća generacija.',
        ])
    );

    expect($campaign)->toBeInstanceOf(LeadCampaign::class);
    expect($campaign->exists)->toBeTrue();
    expect($campaign->name)->toBe('EmPay 3.0');
    expect($campaign->description)->toBe('Treća generacija.');

    $this->assertDatabaseHas('lead_campaigns', [
        'id' => $campaign->id,
        'name' => 'EmPay 3.0',
    ]);
});

test('upsert action updates an existing campaign', function () {
    $campaign = LeadCampaign::factory()->create(['name' => 'Old Name']);

    $updated = app(UpsertLeadCampaignAction::class)->execute(
        UpsertLeadCampaignData::fromArray([
            'campaign_id' => $campaign->id,
            'name' => 'New Name',
            'description' => null,
        ])
    );

    expect($updated->id)->toBe($campaign->id);
    expect($updated->name)->toBe('New Name');
    expect($updated->description)->toBeNull();
});

test('delete action removes campaign with no leads', function () {
    $campaign = LeadCampaign::factory()->create();

    app(DeleteLeadCampaignAction::class)->execute(
        DeleteLeadCampaignData::fromArray(['campaign_id' => $campaign->id])
    );

    $this->assertDatabaseMissing('lead_campaigns', ['id' => $campaign->id]);
});

test('delete action throws when campaign has leads', function () {
    $campaign = LeadCampaign::factory()->create();
    Lead::factory()->create(['lead_campaign_id' => $campaign->id]);

    expect(fn () => app(DeleteLeadCampaignAction::class)->execute(
        DeleteLeadCampaignData::fromArray(['campaign_id' => $campaign->id])
    ))->toThrow(LeadCampaignHasLeadsException::class);

    $this->assertDatabaseHas('lead_campaigns', ['id' => $campaign->id]);
});
