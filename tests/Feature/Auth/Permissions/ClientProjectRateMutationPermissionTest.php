<?php

declare(strict_types=1);

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Livewire\ClientProjectRates\Form as RateForm;
use App\Livewire\ClientProjectRates\Index as RateIndex;
use App\Models\BillingPeriod;
use App\Models\Client;
use App\Models\ClientProjectRate;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

// Client-project-rate routes use can:manage-clients (same permission as clients module).
// Views use Flux UI. Direct component instantiation avoids view rendering.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// ─── Helper ──────────────────────────────────────────────────────────────────

function makeRate(int $userId): ClientProjectRate
{
    $client = Client::factory()->create(['user_id' => $userId]);
    $project = Project::factory()->create(['user_id' => $userId]);
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    return ClientProjectRate::query()->create([
        'client_id' => $client->id,
        'project_id' => $project->id,
        'billing_period_id' => $monthlyId,
        'price_amount' => 20000,
        'currency' => 'RSD',
        'is_active' => true,
    ]);
}

// ─── CANNOT: user without manage-clients ─────────────────────────────────────

test('user without manage-clients cannot save client project rate', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = ClientProjectRate::query()->count();

    expect(fn () => (new RateForm)->save())
        ->toThrow(AuthorizationException::class);

    expect(ClientProjectRate::query()->count())->toBe($initialCount);
});

test('user without manage-clients cannot delete client project rate', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $rate = makeRate($user->id);

    expect(fn () => (new RateIndex)->deleteRate($rate->id))
        ->toThrow(AuthorizationException::class);

    expect(ClientProjectRate::find($rate->id))->not()->toBeNull();
});

test('user without manage-clients cannot toggle client project rate active state', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $rate = makeRate($user->id);
    $originalActive = $rate->is_active;

    expect(fn () => (new RateIndex)->toggleActive($rate->id))
        ->toThrow(AuthorizationException::class);

    expect(ClientProjectRate::find($rate->id)?->is_active)->toBe($originalActive);
});

// ─── CAN: user with manage-clients ───────────────────────────────────────────

test('user with manage-clients can save client project rate', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageClients->value);
    $this->actingAs($user);

    $client = Client::factory()->create(['user_id' => $user->id]);
    $project = Project::factory()->create(['user_id' => $user->id]);
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $component = new RateForm;
    $component->clientId = (string) $client->id;
    $component->projectId = (string) $project->id;
    $component->billingPeriodId = (string) $monthlyId;
    $component->priceAmount = '15000';
    $component->currency = 'RSD';

    try {
        $component->save();
    } catch (\Throwable) {
        // redirectRoute may throw outside the Livewire lifecycle — rate is already saved
    }

    expect(ClientProjectRate::query()
        ->where('client_id', $client->id)
        ->where('project_id', $project->id)
        ->exists()
    )->toBeTrue();
});

test('user with manage-clients can delete client project rate', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageClients->value);
    $this->actingAs($user);

    $rate = makeRate($user->id);

    (new RateIndex)->deleteRate($rate->id);

    expect(ClientProjectRate::find($rate->id))->toBeNull();
});

test('user with manage-clients can toggle client project rate active state', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageClients->value);
    $this->actingAs($user);

    $rate = makeRate($user->id);

    (new RateIndex)->toggleActive($rate->id);

    expect(ClientProjectRate::find($rate->id)?->is_active)->toBeFalse();
});

// ─── SUPER-ADMIN: Gate::before bypass ────────────────────────────────────────

test('super-admin can delete client project rate via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($superAdmin);

    $rate = makeRate($superAdmin->id);

    (new RateIndex)->deleteRate($rate->id);

    expect(ClientProjectRate::find($rate->id))->toBeNull();
});
