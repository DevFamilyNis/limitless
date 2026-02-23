<?php

declare(strict_types=1);

namespace App\Domain\Clients\DTO;

final class DeleteClientData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $clientId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['user_id'], (int) $data['client_id']);
    }
}
