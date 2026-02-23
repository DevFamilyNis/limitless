<?php

use App\Domain\Clients\Actions\UpsertClientAction;
use App\Domain\Clients\DTO\UpsertClientData;
use App\Models\ClientType;
use App\Models\User;

test('upsert client action creates company client with primary contact', function () {
    $user = User::factory()->create();

    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');

    $client = app(UpsertClientAction::class)->execute(
        UpsertClientData::fromArray([
            'user_id' => $user->id,
            'client_type_id' => $companyTypeId,
            'display_name' => 'ACME',
            'email' => 'office@acme.test',
            'phone' => '+38160123456',
            'address' => 'Main street',
            'note' => 'Company note',
            'pib' => '123456789',
            'mb' => '12345678',
            'bank_account' => '160-123456-78',
            'contacts' => [
                [
                    'id' => null,
                    'full_name' => 'Primary Person',
                    'email' => 'primary@acme.test',
                    'phone' => '',
                    'position' => 'Manager',
                    'is_primary' => false,
                    'note' => '',
                ],
            ],
        ])
    );

    expect($client->company)->not()->toBeNull();
    expect($client->contacts)->toHaveCount(1);
    expect((bool) $client->contacts->first()->is_primary)->toBeTrue();
});

test('upsert client action switches from company to person and clears company relations', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');

    $client = app(UpsertClientAction::class)->execute(
        UpsertClientData::fromArray([
            'user_id' => $user->id,
            'client_type_id' => $companyTypeId,
            'display_name' => 'Switch me',
            'contacts' => [
                [
                    'id' => null,
                    'full_name' => 'Company Contact',
                    'email' => '',
                    'phone' => '',
                    'position' => '',
                    'is_primary' => true,
                    'note' => '',
                ],
            ],
        ])
    );

    $updated = app(UpsertClientAction::class)->execute(
        UpsertClientData::fromArray([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'client_type_id' => $personTypeId,
            'display_name' => 'Switch me person',
            'first_name' => 'Pera',
            'last_name' => 'Peric',
            'contacts' => [],
        ])
    );

    $updated->refresh();

    expect($updated->person)->not()->toBeNull();
    expect($updated->company)->toBeNull();
    expect($updated->contacts)->toHaveCount(0);
});
