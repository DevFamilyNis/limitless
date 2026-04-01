<?php

use App\Livewire\Leads\Form;
use App\Livewire\Leads\Index;
use App\Livewire\Leads\Show;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Livewire\Livewire;

test('leads page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('leads.index'))
        ->assertOk();
});

test('create lead page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('leads.create'))
        ->assertOk()
        ->assertSee('Novi lead');
});

test('lead show page is displayed for another user in shared workspace', function () {
    $owner = User::factory()->create();
    $anotherUser = User::factory()->create();

    $lead = Lead::query()->create([
        'lead_status_id' => LeadStatus::query()->where('key', 'new')->value('id'),
        'company_name' => 'Shared Lead Company',
        'email' => 'shared@example.com',
        'phone' => '+38160123456',
    ]);

    $this->actingAs($owner);

    $this->actingAs($anotherUser)
        ->get(route('leads.show', $lead))
        ->assertOk()
        ->assertSee('Shared Lead Company')
        ->assertDontSee('Tip događaja')
        ->assertDontSee('Ishod')
        ->assertSee('Telefon')
        ->assertSee('Email');
});

test('user can create lead', function () {
    $user = User::factory()->create();
    $interestedStatusId = LeadStatus::query()->where('key', 'interested')->value('id');

    Livewire::actingAs($user)->test(Form::class)
        ->set('companyName', 'Acme DOO')
        ->set('email', 'office@acme.test')
        ->set('phone', '+38160111222')
        ->set('leadStatusId', (string) $interestedStatusId)
        ->call('save')
        ->assertRedirect(route('leads.index', absolute: false));

    $this->assertDatabaseHas('leads', [
        'company_name' => 'Acme DOO',
        'email' => 'office@acme.test',
        'phone' => '+38160111222',
        'lead_status_id' => $interestedStatusId,
    ]);
});

test('user can search leads', function () {
    $user = User::factory()->create();
    $statusId = LeadStatus::query()->where('key', 'new')->value('id');

    Lead::query()->create([
        'lead_status_id' => $statusId,
        'company_name' => 'Alfa Systems',
        'email' => 'alfa@example.com',
        'phone' => '+38160111111',
    ]);

    Lead::query()->create([
        'lead_status_id' => $statusId,
        'company_name' => 'Beta Systems',
        'email' => 'beta@example.com',
        'phone' => '+38160222222',
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->set('search', 'Alfa')
        ->assertSee('Alfa Systems')
        ->assertDontSee('Beta Systems');
});

test('user can update lead', function () {
    $user = User::factory()->create();
    $newStatusId = LeadStatus::query()->where('key', 'new')->value('id');
    $respondedStatusId = LeadStatus::query()->where('key', 'responded')->value('id');

    $lead = Lead::query()->create([
        'lead_status_id' => $newStatusId,
        'company_name' => 'Old Name DOO',
        'email' => 'old@example.com',
        'phone' => '+38160111222',
    ]);

    Livewire::actingAs($user)->test(Form::class, ['lead' => $lead])
        ->set('companyName', 'New Name DOO')
        ->set('email', 'new@example.com')
        ->set('leadStatusId', (string) $respondedStatusId)
        ->call('save')
        ->assertRedirect(route('leads.index', absolute: false));

    $this->assertDatabaseHas('leads', [
        'id' => $lead->id,
        'company_name' => 'New Name DOO',
        'email' => 'new@example.com',
        'lead_status_id' => $respondedStatusId,
    ]);
});

test('user can delete lead', function () {
    $user = User::factory()->create();

    $lead = Lead::query()->create([
        'lead_status_id' => LeadStatus::query()->where('key', 'new')->value('id'),
        'company_name' => 'Delete Me DOO',
        'email' => 'delete@example.com',
        'phone' => '+38160333444',
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('deleteLead', $lead->id);

    $this->assertDatabaseMissing('leads', [
        'id' => $lead->id,
    ]);
});

test('user can add lead comment and update lead tracking fields', function () {
    $user = User::factory()->create();
    $newStatusId = LeadStatus::query()->where('key', 'new')->value('id');
    $respondedStatusId = LeadStatus::query()->where('key', 'responded')->value('id');

    $lead = Lead::query()->create([
        'lead_status_id' => $newStatusId,
        'company_name' => 'Timeline Lead',
        'email' => 'timeline@example.com',
        'phone' => '+38160444555',
    ]);

    Livewire::actingAs($user)->test(Show::class, ['lead' => $lead])
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
    ]);

    expect($lead->fresh()?->last_contacted_at?->format('Y-m-d H:i'))->toBe('2026-04-01 10:00');
    expect($lead->fresh()?->last_response_at?->format('Y-m-d H:i'))->toBe('2026-04-01 10:15');
});
