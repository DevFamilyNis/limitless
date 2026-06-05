<?php

declare(strict_types=1);

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Livewire\Leads\Form as LeadForm;
use App\Livewire\Leads\Index as LeadIndex;
use App\Livewire\Leads\Show as LeadShow;
use App\Models\Lead;
use App\Models\LeadCampaign;
use App\Models\LeadComment;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

// Lead views use Flux UI. Direct component instantiation avoids view rendering
// while still exercising the full authorization + domain logic path.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// ─── Helper ──────────────────────────────────────────────────────────────────

function makeLead(): Lead
{
    $campaign = LeadCampaign::factory()->create();

    return Lead::factory()->create([
        'lead_campaign_id' => $campaign->id,
        'lead_status_id' => LeadStatus::query()->where('key', 'new')->value('id'),
        'company_name' => 'Test Lead DOO',
        'email' => 'test@lead.test',
        'phone' => '+38160000001',
    ]);
}

// ─── CANNOT: user without manage-leads ───────────────────────────────────────

test('user without manage-leads cannot save lead through form component', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = Lead::query()->count();

    expect(fn () => (new LeadForm)->save())
        ->toThrow(AuthorizationException::class);

    expect(Lead::query()->count())->toBe($initialCount);
});

test('user without manage-leads cannot delete lead', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = makeLead();

    expect(fn () => (new LeadIndex)->deleteLead($lead->id))
        ->toThrow(AuthorizationException::class);

    expect(Lead::find($lead->id))->not()->toBeNull();
});

test('user without manage-leads cannot add lead comment', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $lead = makeLead();

    $component = new LeadShow;
    $component->lead = $lead->load(['status', 'comments.author', 'comments.status']);

    expect(fn () => $component->addComment())
        ->toThrow(AuthorizationException::class);

    expect(LeadComment::query()->where('lead_id', $lead->id)->count())->toBe(0);
});

// ─── CAN: user with manage-leads ─────────────────────────────────────────────

test('user with manage-leads can save lead', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageLeads->value);
    $this->actingAs($user);

    $campaign = LeadCampaign::factory()->create();
    $statusId = LeadStatus::query()->where('key', 'new')->value('id');

    $component = new LeadForm;
    $component->campaign = $campaign;
    $component->leadCampaignId = (string) $campaign->id;
    $component->companyName = 'Nova Firma DOO';
    $component->leadStatusId = (string) $statusId;

    try {
        $component->save();
    } catch (\Throwable) {
        // redirectRoute may throw outside the Livewire lifecycle — lead is already saved
    }

    expect(Lead::query()->where('company_name', 'Nova Firma DOO')->exists())->toBeTrue();
});

test('user with manage-leads can delete lead', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageLeads->value);
    $this->actingAs($user);

    $lead = makeLead();

    (new LeadIndex)->deleteLead($lead->id);

    expect(Lead::find($lead->id))->toBeNull();
});

test('user with manage-leads can add lead comment', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageLeads->value);
    $this->actingAs($user);

    $lead = makeLead();
    $statusId = LeadStatus::query()->where('key', 'new')->value('id');

    $component = new LeadShow;
    $component->lead = $lead->load(['status', 'comments.author', 'comments.status']);
    $component->commentLeadStatusId = (string) $statusId;
    $component->commentEventType = 'note';
    $component->commentContactMethod = 'phone';
    $component->commentBody = 'Test komentar za permission guard.';

    $component->addComment();

    expect(LeadComment::query()->where('lead_id', $lead->id)->exists())->toBeTrue();
});

// ─── SUPER-ADMIN: Gate::before bypass ────────────────────────────────────────

test('super-admin can delete lead via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($superAdmin);

    $lead = makeLead();

    (new LeadIndex)->deleteLead($lead->id);

    expect(Lead::find($lead->id))->toBeNull();
});
