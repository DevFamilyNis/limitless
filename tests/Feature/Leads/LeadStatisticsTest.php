<?php

use App\Domain\Leads\Queries\LeadStatisticsQuery;
use App\Models\Lead;
use App\Models\LeadStatus;

test('lead statistics query returns totals and conversion rate', function () {
    $newStatusId = LeadStatus::query()->where('key', 'new')->value('id');
    $respondedStatusId = LeadStatus::query()->where('key', 'responded')->value('id');
    $convertedStatusId = LeadStatus::query()->where('key', 'converted')->value('id');

    Lead::query()->create([
        'lead_status_id' => $newStatusId,
        'company_name' => 'Alpha',
        'email' => 'alpha@example.com',
        'phone' => '+38160111111',
    ]);

    Lead::query()->create([
        'lead_status_id' => $respondedStatusId,
        'company_name' => 'Beta',
        'email' => 'beta@example.com',
        'phone' => '+38160222222',
        'last_response_at' => now(),
    ]);

    Lead::query()->create([
        'lead_status_id' => $convertedStatusId,
        'company_name' => 'Gamma',
        'email' => 'gamma@example.com',
        'phone' => '+38160333333',
        'converted_at' => now(),
    ]);

    Lead::query()->create([
        'lead_status_id' => $respondedStatusId,
        'company_name' => 'Delta',
        'email' => 'delta@example.com',
        'phone' => '+38160444444',
        'last_response_at' => now(),
    ]);

    $statistics = app(LeadStatisticsQuery::class)->get();

    expect($statistics['total'])->toBe(4);
    expect($statistics['converted'])->toBe(1);
    expect($statistics['responded'])->toBe(2);
    expect($statistics['conversion_rate'])->toBe(25.0);
    expect($statistics['by_status']['responded'])->toBe(2);
    expect($statistics['by_status']['converted'])->toBe(1);
});
