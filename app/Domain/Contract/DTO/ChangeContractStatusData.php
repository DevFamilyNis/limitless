<?php

declare(strict_types=1);

namespace App\Domain\Contract\DTO;

use App\Domain\Contract\Enums\ContractStatus;

final class ChangeContractStatusData
{
    public function __construct(
        public readonly int $contractId,
        public readonly int $userId,
        public readonly ContractStatus $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contractId: (int) $data['contract_id'],
            userId: (int) $data['user_id'],
            status: ContractStatus::from((string) $data['status']),
        );
    }
}
