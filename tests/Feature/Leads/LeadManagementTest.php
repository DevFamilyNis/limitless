<?php

use App\Livewire\Leads\Form;
use App\Livewire\Leads\Index;
use App\Livewire\Leads\Show;
use App\Models\Lead;
use App\Models\LeadCampaign;
use App\Models\LeadStatus;
use App\Models\User;
use Livewire\Livewire;

test('leads campaign index page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('leads.index'))
        ->assertOk();
});

test('create lead page is displayed', function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();

    $user = User::factory()->create();
    $user->givePermissionTo('manage-leads');
    $campaign = LeadCampaign::factory()->create();

    $this->actingAs($user)
        ->get(route('leads.create', $campaign))
        ->assertOk()
        ->assertSee(__('messages.leads.form_new_title'));
});

test('lead show page is displayed for another user in shared workspace', function () {
    $owner = User::factory()->create();
    $anotherUser = User::factory()->create();

    $campaign = LeadCampaign::factory()->create();
    $lead = Lead::factory()->create([
        'lead_campaign_id' => $campaign->id,
        'company_name' => 'Shared Lead Company',
        'email' => 'shared@example.com',
        'phone' => '+38160123456',
    ]);

    $this->actingAs($owner);

    $this->actingAs($anotherUser)
        ->get(route('leads.show', [$campaign, $lead]))
        ->assertOk()
        ->assertSee('Shared Lead Company')
        ->assertDontSee('Tip događaja')
        ->assertDontSee('Ishod')
        ->assertSee('Telefon')
        ->assertSee('Email')
        ->assertSee(__('messages.leads.next_contact'));
});

test('user can create lead', function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();

    $user = User::factory()->create();
    $user->givePermissionTo('manage-leads');
    $campaign = LeadCampaign::factory()->create();
    $interestedStatusId = LeadStatus::query()->where('key', 'interested')->value('id');

    Livewire::actingAs($user)->test(Form::class, ['campaign' => $campaign])
        ->set('companyName', 'Acme DOO')
        ->set('email', 'office@acme.test')
        ->set('phone', '+38160111222')
        ->set('leadStatusId', (string) $interestedStatusId)
        ->set('leadCampaignId', (string) $campaign->id)
        ->call('save')
        ->assertRedirect(route('leads.campaign', $campaign, absolute: false));

    $this->assertDatabaseHas('leads', [
        'company_name' => 'Acme DOO',
        'email' => 'office@acme.test',
        'phone' => '+38160111222',
        'lead_status_id' => $interestedStatusId,
        'lead_campaign_id' => $campaign->id,
    ]);
});

test('user can search leads within a campaign', function () {
    $user = User::factory()->create();
    $campaign = LeadCampaign::factory()->create();

    Lead::factory()->create([
        'lead_campaign_id' => $campaign->id,
        'company_name' => 'Alfa Systems',
    ]);

    Lead::factory()->create([
        'lead_campaign_id' => $campaign->id,
        'company_name' => 'Beta Systems',
    ]);

    Livewire::actingAs($user)->test(Index::class, ['campaign' => $campaign])
        ->set('search', 'Alfa')
        ->assertSee('Alfa Systems')
        ->assertDontSee('Beta Systems');
});

test('leads pagination shows 10 leads per page', function () {
    $user = User::factory()->create();
    $campaign = LeadCampaign::factory()->create();

    foreach (range(1, 11) as $number) {
        Lead::factory()->create([
            'lead_campaign_id' => $campaign->id,
            'company_name' => sprintf('Pagination Lead %02d', $number),
        ]);
    }

    Livewire::actingAs($user)->test(Index::class, ['campaign' => $campaign])
        ->assertSee('Pagination Lead 11')
        ->assertSee('Pagination Lead 02')
        ->assertDontSee('Pagination Lead 01')
        ->call('gotoPage', 2)
        ->assertSee('Pagination Lead 01')
        ->assertDontSee('Pagination Lead 02');
});

test('user can update lead', function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();

    $user = User::factory()->create();
    $user->givePermissionTo('manage-leads');
    $campaign = LeadCampaign::factory()->create();
    $newStatusId = LeadStatus::query()->where('key', 'new')->value('id');
    $respondedStatusId = LeadStatus::query()->where('key', 'responded')->value('id');

    $lead = Lead::factory()->create([
        'lead_campaign_id' => $campaign->id,
        'lead_status_id' => $newStatusId,
        'company_name' => 'Old Name DOO',
        'email' => 'old@example.com',
    ]);

    Livewire::actingAs($user)->test(Form::class, ['campaign' => $campaign, 'lead' => $lead])
        ->set('companyName', 'New Name DOO')
        ->set('email', 'new@example.com')
        ->set('leadStatusId', (string) $respondedStatusId)
        ->call('save')
        ->assertRedirect(route('leads.campaign', $campaign, absolute: false));

    $this->assertDatabaseHas('leads', [
        'id' => $lead->id,
        'company_name' => 'New Name DOO',
        'email' => 'new@example.com',
        'lead_status_id' => $respondedStatusId,
    ]);
});

test('user can delete lead', function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();

    $user = User::factory()->create();
    $user->givePermissionTo('manage-leads');
    $campaign = LeadCampaign::factory()->create();

    $lead = Lead::factory()->create([
        'lead_campaign_id' => $campaign->id,
        'company_name' => 'Delete Me DOO',
    ]);

    Livewire::actingAs($user)->test(Index::class, ['campaign' => $campaign])
        ->call('deleteLead', $lead->id);

    $this->assertDatabaseMissing('leads', [
        'id' => $lead->id,
    ]);
});

test('user can add lead comment and update lead tracking fields', function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();

    $user = User::factory()->create();
    $user->givePermissionTo('manage-leads');
    $campaign = LeadCampaign::factory()->create();
    $newStatusId = LeadStatus::query()->where('key', 'new')->value('id');
    $respondedStatusId = LeadStatus::query()->where('key', 'responded')->value('id');

    $lead = Lead::factory()->create([
        'lead_campaign_id' => $campaign->id,
        'lead_status_id' => $newStatusId,
        'company_name' => 'Timeline Lead',
    ]);

    Livewire::actingAs($user)->test(Show::class, ['campaign' => $campaign, 'lead' => $lead])
        ->set('commentLeadStatusId', (string) $respondedStatusId)
        ->set('commentContactMethod', 'phone')
        ->set('commentBody', 'Javili su se i tražili ponudu.')
        ->set('commentContactedAt', '2026-04-01 10:00')
        ->set('commentRespondedAt', '2026-04-01 10:15')
        ->set('commentNextFollowUpAt', '2026-04-03 09:00')
        ->call('addComment')
        ->assertSee('Javili su se i tražili ponudu.');

    $this->assertDatabaseHas('lead_comments', [
        'lead_id' => $lead->id,
        'author_id' => $user->id,
        'lead_status_id' => $respondedStatusId,
        'event_type' => 'note',
        'contact_method' => 'phone',
        'body' => 'Javili su se i tražili ponudu.',
    ]);

    $this->assertDatabaseHas('leads', [
        'id' => $lead->id,
        'lead_status_id' => $respondedStatusId,
        'last_contact_method' => 'phone',
        'next_follow_up_at' => '2026-04-03 09:00:00',
    ]);

    expect($lead->fresh()?->last_contacted_at?->format('Y-m-d H:i'))->toBe('2026-04-01 10:00');
    expect($lead->fresh()?->last_response_at?->format('Y-m-d H:i'))->toBe('2026-04-01 10:15');
    expect($lead->fresh()?->next_follow_up_at?->format('Y-m-d H:i'))->toBe('2026-04-03 09:00');
});

test('lead uses first upcoming follow up across multiple comments', function () {
    $user = User::factory()->create();
    $campaign = LeadCampaign::factory()->create();
    $statusId = LeadStatus::query()->where('key', 'contacted')->value('id');

    $lead = Lead::factory()->create([
        'lead_campaign_id' => $campaign->id,
        'lead_status_id' => $statusId,
        'company_name' => 'Future Follow Up Lead',
    ]);

    $lead->comments()->create([
        'author_id' => $user->id,
        'lead_status_id' => $statusId,
        'event_type' => 'note',
        'contact_method' => 'phone',
        'outcome' => null,
        'body' => 'Prvi komentar.',
        'contacted_at' => now(),
        'responded_at' => null,
        'next_follow_up_at' => now()->addDays(10),
    ]);

    $lead->comments()->create([
        'author_id' => $user->id,
        'lead_status_id' => $statusId,
        'event_type' => 'note',
        'contact_method' => 'phone',
        'outcome' => null,
        'body' => 'Drugi komentar sa bližim datumom.',
        'contacted_at' => now(),
        'responded_at' => null,
        'next_follow_up_at' => now()->addDays(2),
    ]);

    $lead->refresh()->load('comments');

    expect($lead->current_next_follow_up_at?->format('Y-m-d'))->toBe(now()->addDays(2)->format('Y-m-d'));

    $this->actingAs($user)
        ->get(route('leads.show', [$campaign, $lead]))
        ->assertOk()
        ->assertSee($lead->current_next_follow_up_at?->format('d.m.Y'));
});
