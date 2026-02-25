<?php

declare(strict_types=1);

namespace App\Domain\Clients\Actions;

use App\Domain\Clients\DTO\ToggleClientActiveData;
use App\Models\Client;

final class ToggleClientActiveAction
{
    public function execute(ToggleClientActiveData $dto): Client
    {
        $client = Client::query()->findOrFail($dto->clientId);

        $client->update(['is_active' => ! $client->is_active]);

        return $client;
    }
}
