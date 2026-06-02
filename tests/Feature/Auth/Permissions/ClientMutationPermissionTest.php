<?php

declare(strict_types=1);

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Clients\Index as ClientIndex;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

// Client views use Flux UI. Direct component instantiation avoids view rendering
// while still exercising the full authorization + domain logic path.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// ─── Helpers ─────────────────────────────────────────────────────────────────

function makeClient(int $userId): Client
{
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');

    return Client::query()->create([
        'user_id' => $userId,
        'client_type_id' => $personTypeId,
        'display_name' => 'Test Klijent',
        'is_active' => true,
    ]);
}

// ─── CANNOT: user without manage-clients ─────────────────────────────────────

test('user without manage-clients cannot save client through form component', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = Client::query()->count();

    expect(fn () => (new ClientForm)->save())
        ->toThrow(AuthorizationException::class);

    expect(Client::query()->count())->toBe($initialCount);
});

test('user without manage-clients cannot delete client', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $client = makeClient($user->id);

    expect(fn () => (new ClientIndex)->deleteClient($client->id))
        ->toThrow(AuthorizationException::class);

    expect(Client::find($client->id))->not()->toBeNull();
});

test('user without manage-clients cannot toggle client active state', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $client = makeClient($user->id);
    $originalActive = $client->is_active;

    expect(fn () => (new ClientIndex)->toggleActive($client->id))
        ->toThrow(AuthorizationException::class);

    expect(Client::find($client->id)?->is_active)->toBe($originalActive);
});

// ─── CAN: user with manage-clients ───────────────────────────────────────────

test('user with manage-clients can save client', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageClients->value);
    $this->actingAs($user);

    $personTypeId = ClientType::query()->where('key', 'person')->value('id');

    $component = new ClientForm;
    $component->clientTypeId = (string) $personTypeId;
    $component->clientTypeKey = 'person';
    $component->displayName = 'Novi Klijent';
    $component->firstName = 'Novi';
    $component->lastName = 'Klijent';

    try {
        $component->save();
    } catch (\Livewire\Exceptions\CannotRedirectWithoutButtonOrLinkException|\Exception $e) {
        // redirectRoute may throw outside the Livewire lifecycle — client is already saved
    }

    expect(Client::query()->where('display_name', 'Novi Klijent')->exists())->toBeTrue();
});

test('user with manage-clients can delete client', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageClients->value);
    $this->actingAs($user);

    $client = makeClient($user->id);

    (new ClientIndex)->deleteClient($client->id);

    expect(Client::find($client->id))->toBeNull();
});

test('user with manage-clients can toggle client active state', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageClients->value);
    $this->actingAs($user);

    $client = makeClient($user->id);

    (new ClientIndex)->toggleActive($client->id);

    expect(Client::find($client->id)?->is_active)->toBeFalse();
});

// ─── SUPER-ADMIN: Gate::before bypass ────────────────────────────────────────

test('super-admin can delete client via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($superAdmin);

    $client = makeClient($superAdmin->id);

    (new ClientIndex)->deleteClient($client->id);

    expect(Client::find($client->id))->toBeNull();
});
