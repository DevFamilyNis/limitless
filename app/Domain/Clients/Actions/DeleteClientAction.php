<?php

declare(strict_types=1);

namespace App\Domain\Clients\Actions;

use App\Domain\Clients\DTO\DeleteClientData;
use App\Models\Client;

final class DeleteClientAction
{
    public function execute(DeleteClientData $dto): bool
    {
        $client = Client::query()->where('user_id', $dto->userId)->findOrFail($dto->clientId);

        if (! $client->canBeDeleted()) {
            return false;
        }

        $client->delete();

        return true;
    }
}
