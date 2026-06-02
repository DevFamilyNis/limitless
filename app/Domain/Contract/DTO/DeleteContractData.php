<?php

declare(strict_types=1);

namespace App\Domain\Contract\DTO;

final class DeleteContractData
{
    public function __construct(
        public readonly int $contractId,
        public readonly int $userId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contractId: (int) $data['contract_id'],
            userId: (int) $data['user_id'],
        );
    }
}
