<?php

declare(strict_types=1);

namespace App\Domain\Clients\Actions;

use App\Domain\Clients\DTO\UpsertClientData;
use App\Models\Client;
use App\Models\ClientType;
use Illuminate\Validation\ValidationException;

final class UpsertClientAction
{
    public function execute(UpsertClientData $dto): Client
    {
        $client = $dto->clientId
            ? Client::query()->findOrFail($dto->clientId)
            : new Client;

        $client->fill([
            'user_id' => $dto->userId,
            'client_type_id' => $dto->clientTypeId,
            'display_name' => $dto->displayName,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'address' => $dto->address,
            'note' => $dto->note,
            'is_active' => $client->exists ? $client->is_active : true,
        ]);

        $client->save();

        $clientTypeKey = ClientType::query()->whereKey($dto->clientTypeId)->value('key');

        if ($clientTypeKey === 'company') {
            $client->company()->updateOrCreate([], [
                'pib' => $dto->pib,
                'mb' => $dto->mb,
                'bank_account' => $dto->bankAccount,
            ]);

            $contacts = collect($dto->contacts)
                ->map(function (array $contact): array {
                    return [
                        'id' => $contact['id'] ?? null,
                        'full_name' => trim((string) ($contact['full_name'] ?? '')),
                        'email' => trim((string) ($contact['email'] ?? '')),
                        'phone' => trim((string) ($contact['phone'] ?? '')),
                        'position' => trim((string) ($contact['position'] ?? '')),
                        'is_primary' => (bool) ($contact['is_primary'] ?? false),
                        'note' => trim((string) ($contact['note'] ?? '')),
                    ];
                })
                ->filter(fn (array $contact): bool => $contact['full_name'] !== ''
                    || $contact['email'] !== ''
                    || $contact['phone'] !== ''
                    || $contact['position'] !== ''
                    || $contact['note'] !== '')
                ->values();

            if ($contacts->contains(fn (array $contact): bool => $contact['full_name'] === '')) {
                throw ValidationException::withMessages([
                    'contacts' => 'Kontakt mora imati ime i prezime.',
                ]);
            }

            if ($contacts->isNotEmpty() && ! $contacts->contains(fn (array $contact): bool => $contact['is_primary'])) {
                $contacts = $contacts->map(function (array $contact, int $index): array {
                    if ($index !== 0) {
                        return $contact;
                    }

                    $contact['is_primary'] = true;

                    return $contact;
                });
            }

            $primaryAssigned = false;
            $contacts = $contacts->map(function (array $contact) use (&$primaryAssigned): array {
                if (! $contact['is_primary'] || $primaryAssigned) {
                    $contact['is_primary'] = false;

                    return $contact;
                }

                $primaryAssigned = true;

                return $contact;
            });

            $existingContactIds = $client->contacts()->pluck('id')->all();
            $incomingContactIds = $contacts->pluck('id')->filter()->map(fn ($id): int => (int) $id)->all();
            $contactIdsForDeletion = array_diff($existingContactIds, $incomingContactIds);

            if ($contactIdsForDeletion !== []) {
                $client->contacts()->whereIn('id', $contactIdsForDeletion)->delete();
            }

            foreach ($contacts as $contact) {
                $contactId = $contact['id'] ? (int) $contact['id'] : null;
                unset($contact['id']);
                $client->contacts()->updateOrCreate(['id' => $contactId], $contact);
            }

            $client->person()->delete();
        } elseif ($clientTypeKey === 'person') {
            $client->person()->updateOrCreate([], [
                'first_name' => $dto->firstName,
                'last_name' => $dto->lastName,
            ]);
            $client->company()->delete();
            $client->contacts()->delete();
        } else {
            $client->company()->delete();
            $client->person()->delete();
            $client->contacts()->delete();
        }

        return $client;
    }
}
