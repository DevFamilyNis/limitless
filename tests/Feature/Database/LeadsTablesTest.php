<?php

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

test('leads tables have expected structure', function () {
    expect(Schema::hasTable('lead_statuses'))->toBeTrue();
    expect(Schema::hasTable('leads'))->toBeTrue();
    expect(Schema::hasTable('lead_comments'))->toBeTrue();

    expect(Schema::hasColumns('leads', [
        'id',
        'lead_status_id',
        'company_name',
        'email',
        'phone',
        'last_contacted_at',
        'last_contact_method',
        'last_response_at',
        'converted_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('lead_comments', [
        'id',
        'lead_id',
        'author_id',
        'lead_status_id',
        'event_type',
        'contact_method',
        'outcome',
        'body',
        'contacted_at',
        'responded_at',
        'next_follow_up_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('lead statuses table is seeded with default records', function () {
    expect(LeadStatus::query()->orderBy('id')->pluck('key')->all())->toBe([
        'new',
        'contacted',
        'responded',
        'not_available',
        'interested',
        'not_interested',
        'converted',
    ]);
});

test('lead comments are deleted when lead is deleted', function () {
    $author = User::factory()->create();
    $lead = Lead::query()->create([
        'lead_status_id' => LeadStatus::query()->where('key', 'new')->value('id'),
        'company_name' => 'Acme Lead',
        'email' => 'lead@example.com',
        'phone' => '+38160111222',
    ]);

    $lead->comments()->create([
        'author_id' => $author->id,
        'lead_status_id' => LeadStatus::query()->where('key', 'contacted')->value('id'),
        'event_type' => 'call',
        'contact_method' => 'phone',
        'outcome' => 'no_answer',
        'body' => 'Nije se javio.',
        'contacted_at' => now(),
        'responded_at' => null,
        'next_follow_up_at' => now()->addDay(),
    ]);

    $lead->delete();

    $this->assertDatabaseMissing('lead_comments', [
        'lead_id' => $lead->id,
    ]);
});
