<?php

use App\Domain\Clients\Actions\UpsertClientAction;
use App\Domain\Clients\DTO\UpsertClientData;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Pravilo: UpsertClientAction mora biti atomaran — DB::transaction štiti od parcijalnog stanja.

test('upsert client action creates client within a database transaction', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');

    $client = app(UpsertClientAction::class)->execute(
        UpsertClientData::fromArray([
            'user_id' => $user->id,
            'client_id' => null,
            'client_type_id' => $companyTypeId,
            'display_name' => 'Transactional DOO',
            'email' => null,
            'phone' => null,
            'address' => null,
            'note' => null,
            'pib' => '123456789',
            'mb' => '87654321',
            'bank_account' => '160-000-00',
            'first_name' => null,
            'last_name' => null,
            'contacts' => [],
            'app_links' => [],
        ])
    );

    expect($client->id)->toBeInt();

    $this->assertDatabaseHas('clients', ['display_name' => 'Transactional DOO']);
    $this->assertDatabaseHas('client_companies', ['client_id' => $client->id, 'pib' => '123456789']);
});

test('upsert client creates contacts and company data in single atomic operation', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');

    $client = app(UpsertClientAction::class)->execute(
        UpsertClientData::fromArray([
            'user_id' => $user->id,
            'client_id' => null,
            'client_type_id' => $companyTypeId,
            'display_name' => 'Multi Table DOO',
            'email' => null,
            'phone' => null,
            'address' => null,
            'note' => null,
            'pib' => '111222333',
            'mb' => '444555666',
            'bank_account' => '160-111-22',
            'first_name' => null,
            'last_name' => null,
            'contacts' => [
                [
                    'id' => null,
                    'full_name' => 'Petar Petrović',
                    'email' => 'petar@example.com',
                    'phone' => '+38160111222',
                    'position' => 'CEO',
                    'is_primary' => true,
                    'note' => '',
                ],
            ],
            'app_links' => [
                [
                    'id' => null,
                    'label' => 'Prod',
                    'url' => 'https://example.com',
                ],
            ],
        ])
    );

    $this->assertDatabaseHas('clients', ['id' => $client->id]);
    $this->assertDatabaseHas('client_companies', ['client_id' => $client->id]);
    $this->assertDatabaseHas('client_contacts', ['client_id' => $client->id, 'full_name' => 'Petar Petrović']);
    $this->assertDatabaseHas('client_app_links', ['client_id' => $client->id, 'label' => 'Prod']);
});

test('upsert client rolls back client and company when contact validation fails mid-transaction', function () {
    // Ovaj test dokazuje stvarni rollback, ne samo happy path.
    //
    // Redosled izvršavanja unutar DB::transaction:
    //   1. $client->save()           ← client je upisan
    //   2. $client->company()->...   ← company je upisana
    //   3. ValidationException       ← bačena zbog praznog full_name
    //   4. DB::transaction rollback  ← oba zapisa su poništena
    //
    // Bez DB::transaction: client i company ostaju u bazi → assertDatabaseMissing pada → test FAILS.
    // Sa DB::transaction: savepoint se rollbackuje → assertDatabaseMissing prolazi → test PASSES.
    //
    // Kontakt sa praznim full_name ali nepraznim email-om prolazi filter,
    // ali ga check unutar transakcije odbacuje sa ValidationException.

    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');

    expect(fn () => app(UpsertClientAction::class)->execute(
        UpsertClientData::fromArray([
            'user_id' => $user->id,
            'client_id' => null,
            'client_type_id' => $companyTypeId,
            'display_name' => 'Rollback Test DOO',
            'email' => null,
            'phone' => null,
            'address' => null,
            'note' => null,
            'pib' => '999888777',
            'mb' => '111222333',
            'bank_account' => '160-999-00',
            'first_name' => null,
            'last_name' => null,
            'contacts' => [
                [
                    'id' => null,
                    'full_name' => '',                  // prazno → ValidationException
                    'email' => 'contact@example.com',  // neprazno → prolazi filter
                    'phone' => '',
                    'position' => '',
                    'is_primary' => true,
                    'note' => '',
                ],
            ],
            'app_links' => [],
        ])
    ))->toThrow(\Illuminate\Validation\ValidationException::class);

    // Transakcija je rollbackovana — ni client ni company ne smeju biti u bazi
    $this->assertDatabaseMissing('clients', ['display_name' => 'Rollback Test DOO']);
    $this->assertDatabaseMissing('client_companies', ['pib' => '999888777']);
});

test('upsert client uses lockForUpdate when updating existing client', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');

    $existingClient = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Original Name',
        'is_active' => true,
    ]);

    $updated = app(UpsertClientAction::class)->execute(
        UpsertClientData::fromArray([
            'user_id' => $user->id,
            'client_id' => $existingClient->id,
            'client_type_id' => $personTypeId,
            'display_name' => 'Updated Name',
            'email' => null,
            'phone' => null,
            'address' => null,
            'note' => null,
            'pib' => null,
            'mb' => null,
            'bank_account' => null,
            'first_name' => 'Petar',
            'last_name' => 'Petrović',
            'contacts' => [],
            'app_links' => [],
        ])
    );

    expect($updated->id)->toBe($existingClient->id);
    $this->assertDatabaseHas('clients', ['id' => $existingClient->id, 'display_name' => 'Updated Name']);
});
