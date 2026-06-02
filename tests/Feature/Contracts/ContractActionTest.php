<?php

use App\Domain\Contract\Actions\ChangeContractStatusAction;
use App\Domain\Contract\Actions\CreateContractAction;
use App\Domain\Contract\Actions\DeleteContractAction;
use App\Domain\Contract\Actions\UpdateContractAction;
use App\Domain\Contract\DTO\ChangeContractStatusData;
use App\Domain\Contract\DTO\CreateContractData;
use App\Domain\Contract\DTO\DeleteContractData;
use App\Domain\Contract\DTO\UpdateContractData;
use App\Domain\Contract\Enums\ContractStatus;
use App\Domain\Contract\Enums\ContractType;
use App\Domain\Contract\Exceptions\InvalidAnnexParentException;
use App\Domain\Contract\Exceptions\MultipleActiveContractException;
use App\Models\Client;
use App\Models\Contract;
use App\Models\User;

// ─── CreateContractAction ────────────────────────────────────────────────────

test('can create a Ugovor', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->id]);

    $contract = app(CreateContractAction::class)->execute(
        CreateContractData::fromArray([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'parent_id' => null,
            'type' => ContractType::Ugovor->value,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'note' => 'Test napomena',
            'pdf_file' => null,
        ])
    );

    expect($contract->type)->toBe(ContractType::Ugovor);
    expect($contract->status)->toBe(ContractStatus::Aktivan);
    expect($contract->user_id)->toBe($user->id);
    expect($contract->client_id)->toBe($client->id);
    expect($contract->parent_id)->toBeNull();

    $this->assertDatabaseHas('contracts', [
        'id' => $contract->id,
        'type' => 'Ugovor',
        'status' => 'Aktivan',
        'user_id' => $user->id,
        'client_id' => $client->id,
    ]);
});

test('can create an Aneks referencing a Ugovor', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->id]);
    $ugovor = Contract::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

    $aneks = app(CreateContractAction::class)->execute(
        CreateContractData::fromArray([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'parent_id' => $ugovor->id,
            'type' => ContractType::Aneks->value,
            'start_date' => '2026-06-01',
            'end_date' => null,
            'note' => null,
            'pdf_file' => null,
        ])
    );

    expect($aneks->type)->toBe(ContractType::Aneks);
    expect($aneks->parent_id)->toBe($ugovor->id);
});

test('throws MultipleActiveContractException when creating second active Ugovor for same client', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->id]);

    Contract::factory()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'type' => ContractType::Ugovor->value,
        'status' => ContractStatus::Aktivan->value,
    ]);

    app(CreateContractAction::class)->execute(
        CreateContractData::fromArray([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'parent_id' => null,
            'type' => ContractType::Ugovor->value,
            'start_date' => '2026-06-01',
            'end_date' => null,
            'note' => null,
            'pdf_file' => null,
        ])
    );
})->throws(MultipleActiveContractException::class);

test('allows second Ugovor for same client if first is Neaktivan', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->id]);

    Contract::factory()->neaktivan()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'type' => ContractType::Ugovor->value,
    ]);

    $new = app(CreateContractAction::class)->execute(
        CreateContractData::fromArray([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'parent_id' => null,
            'type' => ContractType::Ugovor->value,
            'start_date' => '2026-06-01',
            'end_date' => null,
            'note' => null,
            'pdf_file' => null,
        ])
    );

    expect($new->status)->toBe(ContractStatus::Aktivan);
});

test('throws InvalidAnnexParentException when Aneks references another Aneks', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->id]);
    $ugovor = Contract::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
    $aneks = Contract::factory()->aneks()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'parent_id' => $ugovor->id,
    ]);

    app(CreateContractAction::class)->execute(
        CreateContractData::fromArray([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'parent_id' => $aneks->id,
            'type' => ContractType::Aneks->value,
            'start_date' => '2026-06-01',
            'end_date' => null,
            'note' => null,
            'pdf_file' => null,
        ])
    );
})->throws(InvalidAnnexParentException::class);

test('active Ugovor uniqueness is scoped per user', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $clientA = Client::factory()->create(['user_id' => $userA->id]);
    $clientB = Client::factory()->create(['user_id' => $userB->id]);

    Contract::factory()->create([
        'user_id' => $userA->id,
        'client_id' => $clientA->id,
        'type' => ContractType::Ugovor->value,
        'status' => ContractStatus::Aktivan->value,
    ]);

    // different user — should succeed
    $contract = app(CreateContractAction::class)->execute(
        CreateContractData::fromArray([
            'user_id' => $userB->id,
            'client_id' => $clientB->id,
            'parent_id' => null,
            'type' => ContractType::Ugovor->value,
            'start_date' => '2026-01-01',
            'end_date' => null,
            'note' => null,
            'pdf_file' => null,
        ])
    );

    expect($contract)->toBeInstanceOf(Contract::class);
});

// ─── UpdateContractAction ────────────────────────────────────────────────────

test('can update contract dates and note', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->id]);
    $contract = Contract::factory()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'start_date' => '2026-01-01',
        'end_date' => null,
        'note' => null,
    ]);

    app(UpdateContractAction::class)->execute(
        UpdateContractData::fromArray([
            'contract_id' => $contract->id,
            'user_id' => $user->id,
            'start_date' => '2026-03-01',
            'end_date' => '2027-02-28',
            'note' => 'Izmenjena napomena',
            'pdf_file' => null,
        ])
    );

    $updated = $contract->fresh();
    expect($updated->start_date->format('Y-m-d'))->toBe('2026-03-01');
    expect($updated->end_date->format('Y-m-d'))->toBe('2027-02-28');
    expect($updated->note)->toBe('Izmenjena napomena');
});

test('update is scoped to owner user_id', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $owner->id]);
    $contract = Contract::factory()->create(['user_id' => $owner->id, 'client_id' => $client->id]);

    expect(fn () => app(UpdateContractAction::class)->execute(
        UpdateContractData::fromArray([
            'contract_id' => $contract->id,
            'user_id' => $other->id,
            'start_date' => '2026-03-01',
            'end_date' => null,
            'note' => null,
            'pdf_file' => null,
        ])
    ))->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});

// ─── ChangeContractStatusAction ─────────────────────────────────────────────

test('can deactivate a Ugovor and all its Aneksi cascade to Neaktivan', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->id]);
    $ugovor = Contract::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);

    $aneks1 = Contract::factory()->aneks()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'parent_id' => $ugovor->id,
        'status' => ContractStatus::Aktivan->value,
    ]);
    $aneks2 = Contract::factory()->aneks()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'parent_id' => $ugovor->id,
        'status' => ContractStatus::Aktivan->value,
    ]);

    app(ChangeContractStatusAction::class)->execute(
        ChangeContractStatusData::fromArray([
            'contract_id' => $ugovor->id,
            'user_id' => $user->id,
            'status' => ContractStatus::Neaktivan->value,
        ])
    );

    $this->assertDatabaseHas('contracts', ['id' => $ugovor->id, 'status' => 'Neaktivan']);
    $this->assertDatabaseHas('contracts', ['id' => $aneks1->id, 'status' => 'Neaktivan']);
    $this->assertDatabaseHas('contracts', ['id' => $aneks2->id, 'status' => 'Neaktivan']);
});

test('can manually change a single Aneks status to Neaktivan without affecting Ugovor', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->id]);
    $ugovor = Contract::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
    $aneks = Contract::factory()->aneks()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'parent_id' => $ugovor->id,
    ]);

    app(ChangeContractStatusAction::class)->execute(
        ChangeContractStatusData::fromArray([
            'contract_id' => $aneks->id,
            'user_id' => $user->id,
            'status' => ContractStatus::Neaktivan->value,
        ])
    );

    $this->assertDatabaseHas('contracts', ['id' => $aneks->id, 'status' => 'Neaktivan']);
    $this->assertDatabaseHas('contracts', ['id' => $ugovor->id, 'status' => 'Aktivan']);
});

test('throws MultipleActiveContractException when re-activating a Ugovor that conflicts', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->id]);

    $inactive = Contract::factory()->neaktivan()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'type' => ContractType::Ugovor->value,
    ]);

    // another active Ugovor exists for same client
    Contract::factory()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'type' => ContractType::Ugovor->value,
        'status' => ContractStatus::Aktivan->value,
    ]);

    app(ChangeContractStatusAction::class)->execute(
        ChangeContractStatusData::fromArray([
            'contract_id' => $inactive->id,
            'user_id' => $user->id,
            'status' => ContractStatus::Aktivan->value,
        ])
    );
})->throws(MultipleActiveContractException::class);

// ─── DeleteContractAction ────────────────────────────────────────────────────

test('can delete a Ugovor and its Aneksi are removed', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->id]);
    $ugovor = Contract::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
    $aneks = Contract::factory()->aneks()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'parent_id' => $ugovor->id,
    ]);

    app(DeleteContractAction::class)->execute(
        DeleteContractData::fromArray([
            'contract_id' => $ugovor->id,
            'user_id' => $user->id,
        ])
    );

    $this->assertDatabaseMissing('contracts', ['id' => $ugovor->id]);
    $this->assertDatabaseMissing('contracts', ['id' => $aneks->id]);
});

test('can delete a standalone Aneks without affecting Ugovor', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->id]);
    $ugovor = Contract::factory()->create(['user_id' => $user->id, 'client_id' => $client->id]);
    $aneks = Contract::factory()->aneks()->create([
        'user_id' => $user->id,
        'client_id' => $client->id,
        'parent_id' => $ugovor->id,
    ]);

    app(DeleteContractAction::class)->execute(
        DeleteContractData::fromArray([
            'contract_id' => $aneks->id,
            'user_id' => $user->id,
        ])
    );

    $this->assertDatabaseMissing('contracts', ['id' => $aneks->id]);
    $this->assertDatabaseHas('contracts', ['id' => $ugovor->id]);
});

test('delete is scoped to owner user_id', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $owner->id]);
    $contract = Contract::factory()->create(['user_id' => $owner->id, 'client_id' => $client->id]);

    expect(fn () => app(DeleteContractAction::class)->execute(
        DeleteContractData::fromArray([
            'contract_id' => $contract->id,
            'user_id' => $other->id,
        ])
    ))->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
